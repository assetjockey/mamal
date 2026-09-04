<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Payment;
use App\Services\PaymentService;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    /**
     * The payment service instance.
     */
    private PaymentService $paymentService;

    /**
     * Create a new controller instance.
     */
    public function __construct(PaymentService $paymentService) {
        $this->paymentService = $paymentService;
    }

    /**
     * Handle the PayPal webhook.
     */
    public function paypal(Request $request): JsonResponse
    {
        $httpClient = new GuzzleClient();

        try {
            $payPalAuthRequest = $httpClient->request('POST', 'https://' . (config('settings.paypal_mode') == 'sandbox' ? 'api-m.sandbox' : 'api-m') . '.paypal.com/v1/oauth2/token', [
                'auth' => [config('settings.paypal_client_id'), config('settings.paypal_secret')],
                'form_params' => [
                    'grant_type' => 'client_credentials'
                ]
            ]);

            $payPalAuth = json_decode($payPalAuthRequest->getBody()->getContents());
        } catch (Exception $e) {
            logger()->error('PayPal: ' . $e->getMessage());

            return response()->json([
                'message' => 'PayPal: ' . $e->getMessage(),
                'status' => 400
            ], 400);
        }

        $payload = json_decode($request->getContent());

        try {
            $payPalWHSignatureRequest = $httpClient->request('POST', 'https://' . (config('settings.paypal_mode') == 'sandbox' ? 'api-m.sandbox' : 'api-m') . '.paypal.com/v1/notifications/verify-webhook-signature', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $payPalAuth->access_token,
                    'Content-Type' => 'application/json'
                ],
                'body' => json_encode([
                    'auth_algo' => $request->header('PAYPAL-AUTH-ALGO'),
                    'cert_url' => $request->header('PAYPAL-CERT-URL'),
                    'transmission_id' => $request->header('PAYPAL-TRANSMISSION-ID'),
                    'transmission_sig' => $request->header('PAYPAL-TRANSMISSION-SIG'),
                    'transmission_time' => $request->header('PAYPAL-TRANSMISSION-TIME'),
                    'webhook_id' => config('settings.paypal_webhook_id'),
                    'webhook_event' => $payload
                ])
            ]);

            $payPalWHSignature = json_decode($payPalWHSignatureRequest->getBody()->getContents());
        } catch (Exception $e) {
            logger()->error('PayPal: ' . $e->getMessage());

            return response()->json([
                'message' => 'PayPal: ' . $e->getMessage(),
                'status' => 400
            ], 400);
        }

        if ($payPalWHSignature->verification_status !== 'SUCCESS') {
            return response()->json([
                'message' => 'PayPal: ' . 'Invalid signature.',
                'status' => 400
            ], 400);
        }

        parse_str($payload->resource->custom_id ?? ($payload->resource->custom ?? ''), $metadata);
        $metadata = (object) $metadata;

        if (isset($metadata->user)) {
            $user = User::where('id', '=', $metadata->user)->first();

            if ($user) {
                if ($payload->event_type == 'BILLING.SUBSCRIPTION.CREATED') {
                    $user->cancelPlanSubscription();
                    $user->plan_id = $metadata->plan;
                    $user->plan_amount = $metadata->amount;
                    $user->plan_currency = $metadata->currency;
                    $user->plan_interval = $metadata->interval;
                    $user->plan_payment_processor = 'paypal';
                    $user->plan_subscription_id = $payload->resource->id;
                    $user->plan_subscription_status = $payload->resource->status;
                    $user->plan_subscription_information = null;
                    $user->plan_created_at = Carbon::now();
                    $user->plan_recurring_at = null;
                    $user->plan_ends_at = null;
                    $user->save();

                    if (isset($metadata->coupon) && $metadata->coupon) {
                        Coupon::withTrashed()->find($metadata->coupon)?->increment('redeems', 1);
                    }
                } elseif (stripos($payload->event_type, 'BILLING.SUBSCRIPTION.') !== false) {
                    if ($user->plan_payment_processor == 'paypal' && $user->plan_subscription_id == $payload->resource->id) {
                        if (isset($payload->resource->billing_info->next_billing_time)) {
                            $user->plan_recurring_at = Carbon::create($payload->resource->billing_info->next_billing_time);
                        }

                        if (isset($payload->resource->status)) {
                            $user->plan_subscription_status = $payload->resource->status;
                        }

                        if ($payload->event_type == 'BILLING.SUBSCRIPTION.CANCELLED') {
                            if (!empty($user->plan_recurring_at)) {
                                $user->plan_ends_at = $user->plan_recurring_at;
                                $user->plan_recurring_at = null;
                            }
                        }

                        $user->save();
                    }
                } elseif ($payload->event_type == 'PAYMENT.SALE.COMPLETED') {
                    if (!Payment::where([['processor', '=', 'paypal'], ['payment_id', '=', $payload->resource->id ?? null]])->exists()) {
                        $payment = $this->paymentService->store([
                            'user_id' => $user->id,
                            'plan_id' => $metadata->plan,
                            'payment_id' => $payload->resource->id,
                            'processor' => 'paypal',
                            'amount' => $metadata->amount,
                            'currency' => $metadata->currency,
                            'interval' => $metadata->interval,
                            'status' => 'completed',
                            'coupon' => $metadata->coupon ?? null,
                            'tax_rates' => $metadata->tax_rates ?? null,
                            'customer' => $user->billing_information,
                        ]);

                        $this->paymentService->sendInvoiceEmail($payment, $user);
                    }
                }
            }
        }

        return response()->json([
            'status' => 200
        ], 200);
    }

    /**
     * Handle the Stripe webhook.
     */
    public function stripe(Request $request): JsonResponse
    {
        $signatureHeader = $request->header('Stripe-Signature');

        if (!$signatureHeader) {
            return response()->json([
                'message' => 'Stripe: ' . 'Missing signature header.',
                'status' => 400
            ], 400);
        }

        $signatureSegments = [];
        foreach (explode(',', $signatureHeader) as $segment) {
            $keyValue = explode('=', $segment, 2);
            if (count($keyValue) == 2) {
                $signatureSegments[$keyValue[0]] = $keyValue[1];
            }
        }

        if (!isset($signatureSegments['t']) || !isset($signatureSegments['v1'])) {
            return response()->json([
                'message' => 'Stripe: ' . 'Missing signature segments.',
                'status' => 400
            ], 400);
        }

        $payload = $request->getContent();
        $signedPayload = $signatureSegments['t'] . '.' . $payload;
        $computedSignature = hash_hmac('sha256', $signedPayload, config('settings.stripe_wh_secret'));

        if (!hash_equals($computedSignature, $signatureSegments['v1'])) {
            return response()->json([
                'message' => 'Stripe: ' . 'Invalid signature.',
                'status' => 400
            ], 400);
        }

        $stripeEvent = json_decode($payload);
        $metadata = $stripeEvent->data->object->lines->data[0]->metadata ?? ($stripeEvent->data->object->metadata ?? null);

        if (isset($metadata->user)) {
            if ($stripeEvent->type !== 'customer.subscription.created' && stripos($stripeEvent->type, 'customer.subscription.') !== false) {
                // Provide enough time for the subscription created event to be handled
                sleep(3);
            }

            $user = User::where('id', '=', $metadata->user)->first();

            if ($user) {
                if ($stripeEvent->type == 'customer.subscription.created') {
                    $user->cancelPlanSubscription();
                    $user->plan_id = $metadata->plan;
                    $user->plan_amount = $metadata->amount;
                    $user->plan_currency = $metadata->currency;
                    $user->plan_interval = $metadata->interval;
                    $user->plan_payment_processor = 'stripe';
                    $user->plan_subscription_id = $stripeEvent->data->object->id;
                    $user->plan_subscription_status = $stripeEvent->data->object->status;
                    $user->plan_subscription_information = null;
                    $user->plan_created_at = Carbon::now();
                    $user->plan_recurring_at = $stripeEvent->data->object->current_period_end ? Carbon::createFromTimestamp($stripeEvent->data->object->current_period_end) : null;
                    $user->plan_ends_at = null;
                    $user->save();

                    if (isset($metadata->coupon) && $metadata->coupon) {
                        Coupon::withTrashed()->find($metadata->coupon)?->increment('redeems', 1);
                    }
                } elseif (stripos($stripeEvent->type, 'customer.subscription.') !== false) {
                    if ($user->plan_payment_processor == 'stripe' && $user->plan_subscription_id == $stripeEvent->data->object->id) {
                        if ($stripeEvent->data->object->current_period_end) {
                            $user->plan_recurring_at = Carbon::createFromTimestamp($stripeEvent->data->object->current_period_end);
                        }

                        if ($stripeEvent->data->object->status) {
                            $user->plan_subscription_status = $stripeEvent->data->object->status;
                        }

                        if ($stripeEvent->data->object->cancel_at_period_end) {
                            $user->plan_ends_at = Carbon::createFromTimestamp($stripeEvent->data->object->current_period_end);
                        } elseif ($stripeEvent->data->object->cancel_at) {
                            $user->plan_ends_at = Carbon::createFromTimestamp($stripeEvent->data->object->cancel_at);
                        } elseif ($stripeEvent->data->object->canceled_at) {
                            $user->plan_ends_at = Carbon::createFromTimestamp($stripeEvent->data->object->canceled_at);
                        } else {
                            $user->plan_ends_at = null;
                        }

                        if (!empty($user->plan_ends_at)) {
                            $user->plan_recurring_at = null;
                        }

                        $user->save();
                    }
                } elseif ($stripeEvent->type == 'invoice.paid') {
                    if (!Payment::where([['processor', '=', 'stripe'], ['payment_id', '=', $stripeEvent->data->object->id ?? null]])->exists()) {
                        $payment = $this->paymentService->store([
                            'user_id' => $user->id,
                            'plan_id' => $metadata->plan,
                            'payment_id' => $stripeEvent->data->object->id,
                            'processor' => 'stripe',
                            'amount' => $metadata->amount,
                            'currency' => $metadata->currency,
                            'interval' => $metadata->interval,
                            'status' => 'completed',
                            'coupon' => $metadata->coupon ?? null,
                            'tax_rates' => $metadata->tax_rates ?? null,
                            'customer' => $user->billing_information,
                        ]);

                        $this->paymentService->sendInvoiceEmail($payment, $user);
                    }
                }
            }
        }

        return response()->json([
            'status' => 200
        ], 200);
    }

    /**
     * Handle the Mollie webhook.
     */
    public function mollie(Request $request): JsonResponse
    {
        $molliePaymentId = $request->input('id');

        if (!$molliePaymentId) {
            return response()->json([
                'message' => 'Mollie: ' . 'Missing payment ID.',
                'status' => 400
            ], 400);
        }

        $httpClient = new GuzzleClient();

        try {
            $molliePaymentRequest = $httpClient->request('GET', 'https://api.mollie.com/v2/payments/' . $molliePaymentId, [
                'headers' => [
                    'Authorization' => 'Bearer ' . config('settings.mollie_key'),
                    'Content-Type' => 'application/json',
                ]
            ]);

            $molliePayment = json_decode($molliePaymentRequest->getBody());
        } catch (Exception $e) {
            logger()->error('Mollie: ' . $e->getMessage());

            return response()->json([
                'message' => 'Mollie: ' . $e->getMessage(),
                'status' => 400
            ], 400);
        }

        $metadata = $molliePayment->metadata ?? null;

        if (isset($metadata->user)) {
            $user = User::where('id', '=', $metadata->user)->first();

            if ($user) {
                if ($molliePayment->status == 'paid') {
                    if ($molliePayment->sequenceType == 'first') {
                        try {
                            $user->cancelPlanSubscription();

                            $subscriptionStartDate = $metadata->interval == 'month' ? Carbon::now()->addMonth()->format('Y-m-d') : Carbon::now()->addYear()->format('Y-m-d');

                            $mollieSubscriptionRequest = $httpClient->request('POST', 'https://api.mollie.com/v2/customers/' . $molliePayment->customerId . '/subscriptions', [
                                'headers' => [
                                    'Authorization' => 'Bearer ' . config('settings.mollie_key'),
                                    'Content-Type' => 'application/json',
                                ],
                                'json' => [
                                    'amount' => [
                                        'value' => $metadata->amount,
                                        'currency' => $metadata->currency
                                    ],
                                    'interval' => $metadata->interval == 'month' ? '1 month' : '12 months',
                                    'description' => $molliePayment->description . ' - ' . $molliePayment->id,
                                    'webhookUrl' => route('webhooks.mollie'),
                                    'metadata' => $metadata,
                                    'startDate' => $subscriptionStartDate
                                ]
                            ]);

                            $mollieSubscription = json_decode($mollieSubscriptionRequest->getBody());

                            $user->plan_id = $metadata->plan;
                            $user->plan_amount = $metadata->amount;
                            $user->plan_currency = $metadata->currency;
                            $user->plan_interval = $metadata->interval;
                            $user->plan_payment_processor = 'mollie';
                            $user->plan_subscription_id = $mollieSubscription->id;
                            $user->plan_subscription_status = $mollieSubscription->status;
                            $user->plan_subscription_information = ['customerId' => $mollieSubscription->customerId];
                            $user->plan_created_at = Carbon::now();
                            $user->plan_recurring_at = $subscriptionStartDate;
                            $user->plan_ends_at = null;
                            $user->save();

                            if (isset($metadata->coupon) && $metadata->coupon) {
                                Coupon::withTrashed()->find($metadata->coupon)?->increment('redeems', 1);
                            }
                        } catch (Exception $e) {
                            logger()->error('Mollie: ' . $e->getMessage());

                            return response()->json([
                                'message' => 'Mollie: ' . $e->getMessage(),
                                'status' => 400
                            ], 400);
                        }
                    }

                    if ($molliePayment->sequenceType == 'recurring') {
                        if ($user->plan_payment_processor == 'mollie' && $user->plan_subscription_id == $molliePayment->subscriptionId) {
                            try {
                                $mollieSubscriptionRequest = $httpClient->request('GET', 'https://api.mollie.com/v2/customers/' . $molliePayment->customerId . '/subscriptions/' . $molliePayment->subscriptionId, [
                                    'headers' => [
                                        'Authorization' => 'Bearer ' . config('settings.mollie_key'),
                                        'Content-Type' => 'application/json',
                                    ]
                                ]);

                                $mollieSubscription = json_decode($mollieSubscriptionRequest->getBody());

                                if ($mollieSubscription->nextPaymentDate) {
                                    $user->plan_recurring_at = Carbon::createFromFormat('Y-m-d', $mollieSubscription->nextPaymentDate);
                                }

                                if ($mollieSubscription->status) {
                                    $user->plan_subscription_status = $mollieSubscription->status;
                                }

                                if ($mollieSubscription->nextPaymentDate) {
                                    $user->plan_recurring_at = Carbon::createFromFormat('Y-m-d', $mollieSubscription->nextPaymentDate);
                                }

                                if ($mollieSubscription->canceledAt) {
                                    $user->plan_ends_at = Carbon::createFromFormat('Y-m-d', $mollieSubscription->canceledAt);
                                } else if (!$mollieSubscription->nextPaymentDate) {
                                    $user->plan_ends_at = $user->plan_recurring_at;
                                }

                                if (!empty($user->plan_ends_at)) {
                                    $user->plan_recurring_at = null;
                                }

                                $user->save();
                            } catch (Exception $e) {
                                logger()->error('Mollie: ' . $e->getMessage());

                                return response()->json([
                                    'message' => 'Mollie: ' . $e->getMessage(),
                                    'status' => 400
                                ], 400);
                            }
                        }
                    }

                    if (!Payment::where([['processor', '=', 'mollie'], ['payment_id', '=', $molliePayment->id ?? null]])->exists()) {
                        $payment = $this->paymentService->store([
                            'user_id' => $user->id,
                            'plan_id' => $metadata->plan,
                            'payment_id' => $molliePayment->id,
                            'processor' => 'mollie',
                            'amount' => $metadata->amount,
                            'currency' => $metadata->currency,
                            'interval' => $metadata->interval,
                            'status' => 'completed',
                            'coupon' => $metadata->coupon ?? null,
                            'tax_rates' => $metadata->tax_rates ?? null,
                            'customer' => $user->billing_information,
                        ]);

                        $this->paymentService->sendInvoiceEmail($payment, $user);
                    }
                }
            }
        }

        return response()->json([
            'status' => 200
        ], 200);
    }

    /**
     * Handle the Paddle webhook.
     */
    public function paddle(Request $request): JsonResponse
    {
        $signatureHeader = $request->header('paddle-signature');

        if (!$signatureHeader) {
            return response()->json([
                'message' => 'Paddle: ' . 'Missing signature header.',
                'status' => 400
            ], 400);
        }

        $signatureSegments = [];
        foreach (explode(';', $signatureHeader) as $segment) {
            $keyValue = explode('=', $segment, 2);
            if (count($keyValue) == 2) {
                $signatureSegments[$keyValue[0]] = $keyValue[1];
            }
        }

        if (!isset($signatureSegments['ts']) || !isset($signatureSegments['h1'])) {
            return response()->json([
                'message' => 'Paddle: ' . 'Missing signature segments.',
                'status' => 400
            ], 400);
        }

        $payload = $request->getContent();
        $signedPayload = $signatureSegments['ts'] . ':' . $payload;
        $computedSignature = hash_hmac('sha256', $signedPayload, config('settings.paddle_wh_secret'));

        if (!hash_equals($computedSignature, $signatureSegments['h1'])) {
            return response()->json([
                'message' => 'Paddle: ' . 'Invalid signature.',
                'status' => 400
            ], 400);
        }

        $paddleEvent = json_decode($payload);
        $metadata = $paddleEvent->data->items[0]->price->custom_data ?? null;

        if (isset($metadata->user)) {
            if ($paddleEvent->event_type !== 'subscription.created' && stripos($paddleEvent->event_type, 'subscription.') !== false) {
                // Provide enough time for the subscription created event to be handled
                sleep(3);
            }

            $user = User::where('id', '=', $metadata->user)->first();

            if ($user) {
                if ($paddleEvent->event_type == 'subscription.created') {
                    $user->cancelPlanSubscription();
                    $user->plan_id = $metadata->plan;
                    $user->plan_amount = $metadata->amount;
                    $user->plan_currency = $metadata->currency;
                    $user->plan_interval = $metadata->interval;
                    $user->plan_payment_processor = 'paddle';
                    $user->plan_subscription_id = $paddleEvent->data->id;
                    $user->plan_subscription_status = $paddleEvent->data->status;
                    $user->plan_subscription_information = null;
                    $user->plan_created_at = Carbon::now();
                    $user->plan_recurring_at = $paddleEvent->data->next_billed_at ? Carbon::parse($paddleEvent->data->next_billed_at) : null;
                    $user->plan_ends_at = null;
                    $user->save();

                    if (isset($metadata->coupon) && $metadata->coupon) {
                        Coupon::withTrashed()->find($metadata->coupon)?->increment('redeems', 1);
                    }
                } elseif (stripos($paddleEvent->event_type, 'subscription.') !== false) {
                    if ($user->plan_payment_processor == 'paddle' && $user->plan_subscription_id == $paddleEvent->data->id) {
                        if ($paddleEvent->data->next_billed_at) {
                            $user->plan_recurring_at = Carbon::parse($paddleEvent->data->next_billed_at);
                        }

                        if ($paddleEvent->data->status) {
                            $user->plan_subscription_status = $paddleEvent->data->status;
                        }

                        if (isset($paddleEvent->data->scheduled_change->action) && $paddleEvent->data->scheduled_change->action == 'cancel') {
                            $user->plan_ends_at = Carbon::parse($paddleEvent->data->scheduled_change->effective_at);
                        } elseif ($paddleEvent->data->canceled_at) {
                            $user->plan_ends_at = Carbon::parse($paddleEvent->data->canceled_at);
                        } else {
                            $user->plan_ends_at = null;
                        }

                        if (!empty($user->plan_ends_at)) {
                            $user->plan_recurring_at = null;
                        }

                        $user->save();
                    }
                } elseif ($paddleEvent->event_type == 'transaction.paid') {
                    if (!Payment::where([['processor', '=', 'paddle'], ['payment_id', '=', $paddleEvent->data->id ?? null]])->exists()) {
                        $payment = $this->paymentService->store([
                            'user_id' => $user->id,
                            'plan_id' => $metadata->plan,
                            'payment_id' => $paddleEvent->data->id,
                            'processor' => 'paddle',
                            'amount' => $metadata->amount,
                            'currency' => $metadata->currency,
                            'interval' => $metadata->interval,
                            'status' => 'completed',
                            'coupon' => $metadata->coupon ?? null,
                            'tax_rates' => $metadata->tax_rates ?? null,
                            'customer' => $user->billing_information,
                        ]);

                        $this->paymentService->sendInvoiceEmail($payment, $user);
                    }
                }
            }
        }

        return response()->json([
            'status' => 200
        ], 200);
    }

    /**
     * Handle the Razorpay webhook.
     */
    public function razorpay(Request $request): JsonResponse
    {
        $signatureHeader = $request->header('x-razorpay-signature');

        if (!$signatureHeader) {
            return response()->json([
                'message' => 'Razorpay: ' . 'Missing signature header.',
                'status' => 400
            ], 400);
        }

        $computedSignature = hash_hmac('sha256', $request->getContent(), config('settings.razorpay_wh_secret'));

        if (!hash_equals($computedSignature, $signatureHeader)) {
            return response()->json([
                'message' => 'Razorpay: ' . 'Invalid signature.',
                'status' => 400
            ], 400);
        }

        $payload = json_decode($request->getContent());
        $metadata = $payload->payload->subscription->entity->notes ?? null;

        if (isset($metadata->user)) {
            $user = User::where('id', '=', $metadata->user)->first();

            if ($user) {
                if ($payload->event == 'subscription.authenticated') {
                    $user->cancelPlanSubscription();
                    $user->plan_id = $metadata->plan;
                    $user->plan_amount = $metadata->amount;
                    $user->plan_currency = $metadata->currency;
                    $user->plan_interval = $metadata->interval;
                    $user->plan_payment_processor = 'razorpay';
                    $user->plan_subscription_id = $payload->payload->subscription->entity->id;
                    $user->plan_subscription_status = $payload->payload->subscription->entity->status;
                    $user->plan_subscription_information = null;
                    $user->plan_created_at = Carbon::now();
                    $user->plan_recurring_at = $payload->payload->subscription->entity->charge_at ? Carbon::createFromTimestamp($payload->payload->subscription->entity->charge_at) : null;
                    $user->plan_ends_at = null;
                    $user->save();

                    if (isset($metadata->coupon) && $metadata->coupon) {
                        Coupon::withTrashed()->find($metadata->coupon)?->increment('redeems', 1);
                    }
                } elseif (stripos($payload->event, 'subscription.') !== false) {
                    if ($user->plan_payment_processor == 'razorpay' && $user->plan_subscription_id == $payload->payload->subscription->entity->id) {
                        if ($payload->payload->subscription->entity->charge_at) {
                            $user->plan_recurring_at = Carbon::createFromTimestamp($payload->payload->subscription->entity->charge_at);
                        }

                        if ($payload->payload->subscription->entity->status) {
                            $user->plan_subscription_status = $payload->payload->subscription->entity->status;
                        }

                        if ($payload->payload->subscription->entity->ended_at) {
                            if (!empty($user->plan_recurring_at)) {
                                $user->plan_ends_at = $user->plan_recurring_at;
                                $user->plan_recurring_at = null;
                            }
                        } else {
                            $user->plan_ends_at = null;
                        }

                        $user->save();
                    }
                }

                if ($payload->event == 'subscription.charged') {
                    if (!Payment::where([['processor', '=', 'razorpay'], ['payment_id', '=', $payload->payload->payment->entity->id]])->exists()) {
                        $payment = $this->paymentService->store([
                            'user_id' => $user->id,
                            'plan_id' => $metadata->plan,
                            'payment_id' => $payload->payload->payment->entity->id,
                            'processor' => 'razorpay',
                            'amount' => $metadata->amount,
                            'currency' => $metadata->currency,
                            'interval' => $metadata->interval,
                            'status' => 'completed',
                            'coupon' => $metadata->coupon ?? null,
                            'tax_rates' => $metadata->tax_rates ?? null,
                            'customer' => $user->billing_information,
                        ]);

                        $this->paymentService->sendInvoiceEmail($payment, $user);
                    }
                }
            }
        }

        return response()->json([
            'status' => 200
        ], 200);
    }

    /**
     * Handle the Paystack webhook.
     */
    public function paystack(Request $request): JsonResponse
    {
        $signatureHeader = $request->header('x-paystack-signature');

        if (!$signatureHeader) {
            return response()->json([
                'message' => 'Paystack: ' . 'Missing signature header.',
                'status' => 400
            ], 400);
        }

        $computedSignature = hash_hmac('sha512', $request->getContent(), config('settings.paystack_secret'));

        if (!hash_equals($computedSignature, $signatureHeader)) {
            return response()->json([
                'message' => 'Paystack: ' . 'Invalid signature.',
                'status' => 400
            ], 400);
        }

        $payload = json_decode($request->getContent());

        parse_str(str_replace('¤cy=', '&currency=', $payload->data->plan->description ?? ''), $metadata);
        $metadata = (object) $metadata;

        if (isset($metadata->user)) {
            $user = User::where('id', '=', $metadata->user)->first();

            if ($user) {
                if ($payload->event == 'subscription.create') {
                    $user->cancelPlanSubscription();
                    $user->plan_id = $metadata->plan;
                    $user->plan_amount = $metadata->amount;
                    $user->plan_currency = $metadata->currency;
                    $user->plan_interval = $metadata->interval;
                    $user->plan_payment_processor = 'paystack';
                    $user->plan_subscription_id = $payload->data->subscription_code;
                    $user->plan_subscription_status = $payload->data->status;
                    $user->plan_subscription_information = null;
                    $user->plan_created_at = Carbon::now();
                    $user->plan_recurring_at = $payload->data->next_payment_date ? Carbon::createFromTimeString($payload->data->next_payment_date) : null;
                    $user->plan_ends_at = null;
                    $user->save();

                    if (isset($metadata->coupon) && $metadata->coupon) {
                        Coupon::withTrashed()->find($metadata->coupon)?->increment('redeems', 1);
                    }
                } elseif (stripos($payload->event, 'subscription.') !== false) {
                    if ($user->plan_payment_processor == 'paystack' && $user->plan_subscription_id == $payload->data->subscription_code) {
                        if ($payload->data->next_payment_date) {
                            $user->plan_recurring_at = Carbon::createFromTimeString($payload->data->next_payment_date);
                            $user->plan_ends_at = null;
                        } else {
                            if (!empty($user->plan_recurring_at)) {
                                $user->plan_ends_at = $user->plan_recurring_at;
                                $user->plan_recurring_at = null;
                            }
                        }

                        if ($payload->data->status) {
                            $user->plan_subscription_status = $payload->data->status;
                        }

                        $user->save();
                    }
                }

                if ($payload->event == 'charge.success') {

                    if (!Payment::where([['processor', '=', 'paystack'], ['payment_id', '=', $payload->data->reference]])->exists()) {
                        $payment = $this->paymentService->store([
                            'user_id' => $user->id,
                            'plan_id' => $metadata->plan,
                            'payment_id' => $payload->data->reference,
                            'processor' => 'paystack',
                            'amount' => $metadata->amount,
                            'currency' => $metadata->currency,
                            'interval' => $metadata->interval,
                            'status' => 'completed',
                            'coupon' => $metadata->coupon ?? null,
                            'tax_rates' => $metadata->tax_rates ?? null,
                            'customer' => $user->billing_information,
                        ]);

                        $this->paymentService->sendInvoiceEmail($payment, $user);
                    }
                }
            }
        }

        return response()->json([
            'status' => 200
        ], 200);
    }

    /**
     * Handle the Coinbase webhook.
     */
    public function coinbase(Request $request): JsonResponse
    {
        $signatureHeader = $request->server('HTTP_X_CC_WEBHOOK_SIGNATURE');

        if (!$signatureHeader) {
            return response()->json([
                'message' => 'Coinbase: ' . 'Missing signature header.',
                'status' => 400
            ], 400);
        }

        $computedSignature = hash_hmac('sha256', $request->getContent(), config('settings.coinbase_wh_secret'));

        if (!hash_equals($computedSignature, $signatureHeader)) {
            return response()->json([
                'message' => 'Coinbase: ' . 'Invalid signature.',
                'status' => 400
            ], 400);
        }

        $payload = json_decode($request->getContent());
        $metadata = $payload->event->data->metadata ?? null;

        if (isset($metadata->user)) {
            $user = User::where('id', '=', $metadata->user)->first();

            if ($user) {
                if ($payload->event->type == 'charge:confirmed' || $payload->event->type == 'charge:resolved') {
                    if (!Payment::where([['processor', '=', 'coinbase'], ['payment_id', '=', $payload->event->data->code]])->exists()) {
                        $now = Carbon::now();
                        $user->cancelPlanSubscription();
                        $user->plan_id = $metadata->plan;
                        $user->plan_amount = $metadata->amount;
                        $user->plan_currency = $metadata->currency;
                        $user->plan_interval = $metadata->interval;
                        $user->plan_payment_processor = 'coinbase';
                        $user->plan_subscription_id = $payload->event->data->code;
                        $user->plan_subscription_status = null;
                        $user->plan_subscription_information = null;
                        $user->plan_created_at = $now;
                        $user->plan_recurring_at = null;
                        $user->plan_ends_at = $metadata->interval == 'month' ? (clone $now)->addMonth() : (clone $now)->addYear();
                        $user->save();

                        if (isset($metadata->coupon) && $metadata->coupon) {
                            Coupon::withTrashed()->find($metadata->coupon)?->increment('redeems', 1);
                        }

                        $payment = $this->paymentService->store([
                            'user_id' => $user->id,
                            'plan_id' => $metadata->plan,
                            'payment_id' => $payload->event->data->code,
                            'processor' => 'coinbase',
                            'amount' => $metadata->amount,
                            'currency' => $metadata->currency,
                            'interval' => $metadata->interval,
                            'status' => 'completed',
                            'coupon' => $metadata->coupon ?? null,
                            'tax_rates' => $metadata->tax_rates ?? null,
                            'customer' => $user->billing_information,
                        ]);

                        $this->paymentService->sendInvoiceEmail($payment, $user);
                    }
                }
            }
        }

        return response()->json([
            'status' => 200
        ], 200);
    }

    /**
     * Handle the Crypto.com webhook.
     */
    public function cryptocom(Request $request): JsonResponse
    {
        $signatureHeader = $request->header('pay-signature');

        if (!$signatureHeader) {
            return response()->json([
                'message' => 'Crypto.com: ' . 'Missing signature header.',
                'status' => 400
            ], 400);
        }

        $signatureSegments = [];
        foreach (explode(',', $signatureHeader) as $segment) {
            $keyValue = explode('=', $segment, 2);
            if (count($keyValue) == 2) {
                $signatureSegments[$keyValue[0]] = $keyValue[1];
            }
        }

        if (!isset($signatureSegments['t']) || !isset($signatureSegments['v1'])) {
            return response()->json([
                'message' => 'Crypto.com: ' . 'Missing signature segments.',
                'status' => 400
            ], 400);
        }

        $payload = $request->getContent();
        $signedPayload = $signatureSegments['t'] . '.' . $payload;
        $computedSignature = hash_hmac('sha256', $signedPayload, config('settings.cryptocom_wh_secret'));

        if (!hash_equals($computedSignature, $signatureSegments['v1'])) {
            return response()->json([
                'message' => 'Crypto.com: ' . 'Invalid signature.',
                'status' => 400
            ], 400);
        }

        $cryptocomEvent = json_decode($payload);
        $metadata = $cryptocomEvent->data->object->metadata ?? null;

        if (isset($metadata->user)) {
            $user = User::where('id', '=', $metadata->user)->first();

            if ($user) {
                if ($cryptocomEvent->object_type == 'event' && $cryptocomEvent->type == 'payment.captured') {
                    if (!Payment::where([['processor', '=', 'cryptocom'], ['payment_id', '=', $cryptocomEvent->data->object->id]])->exists()) {
                        $now = Carbon::now();
                        $user->cancelPlanSubscription();
                        $user->plan_id = $metadata->plan;
                        $user->plan_amount = $metadata->amount;
                        $user->plan_currency = $metadata->currency;
                        $user->plan_interval = $metadata->interval;
                        $user->plan_payment_processor = 'cryptocom';
                        $user->plan_subscription_id = $cryptocomEvent->data->object->id;
                        $user->plan_subscription_status = null;
                        $user->plan_subscription_information = null;
                        $user->plan_created_at = $now;
                        $user->plan_recurring_at = null;
                        $user->plan_ends_at = $metadata->interval == 'month' ? (clone $now)->addMonth() : (clone $now)->addYear();
                        $user->save();

                        if (isset($metadata->coupon) && $metadata->coupon) {
                            Coupon::withTrashed()->find($metadata->coupon)?->increment('redeems', 1);
                        }

                        $payment = $this->paymentService->store([
                            'user_id' => $user->id,
                            'plan_id' => $metadata->plan,
                            'payment_id' => $cryptocomEvent->data->object->id,
                            'processor' => 'cryptocom',
                            'amount' => $metadata->amount,
                            'currency' => $metadata->currency,
                            'interval' => $metadata->interval,
                            'status' => 'completed',
                            'coupon' => $metadata->coupon ?? null,
                            'tax_rates' => $metadata->tax_rates ?? null,
                            'customer' => $user->billing_information,
                        ]);

                        $this->paymentService->sendInvoiceEmail($payment, $user);
                    }
                }
            }
        }

        return response()->json([
            'status' => 200
        ], 200);
    }
}