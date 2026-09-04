<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProcessCheckoutRequest;
use App\Models\Coupon;
use App\Models\Plan;
use App\Models\TaxRate;
use App\Services\PaymentService;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CheckoutController
{
    /**
     * The default plan interval.
     *
     * @var string
     */
    private $defaultInterval = 'month';

    /**
     * Plan intervals.
     *
     * @var string[]
     */
    private $intervals = ['month', 'year'];

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
     * Display the checkout form.
     */
    public function index(Request $request, $id): View|RedirectResponse
    {
        $request->session()->forget(['plan_redirect']);

        // If no interval is set
        if (!in_array($request->input('interval'), $this->intervals)) {
            // Redirect to a default interval
            return redirect()->route('checkout.index', ['id' => $id, 'interval' => $this->defaultInterval]);
        }

        $plan = Plan::where('id', '=', $id)
            ->where('amount_month', '>', 0)
            ->where('amount_year', '>', 0)
            ->firstOrFail();

        // If the user is already subscribed to the plan
        if ($request->user()->active_plan->id == $plan->id) {
            return redirect()->route('pricing');
        }

        $coupon = null;

        // If the plan has coupons assigned
        if ($plan->coupons) {
            // If a coupon was set
            if ($request->old('coupon')) {
                $coupon = Coupon::where('code', '=', $request->old('coupon'))->first() ?? null;

                // If the coupon isn't available on this plan
                if ($coupon && !in_array($coupon->id, $plan->coupons)) {
                    $coupon = null;
                }

                // If the coupon quantity is smaller or equal with the number of redeems, and it is not unlimited
                if ($coupon && $coupon->quantity <= $coupon->redeems && $coupon->quantity != -1) {
                    $coupon = null;
                }
            }
        }

        // Get the tax rates
        $taxRates = TaxRate::whereIn('id', $plan->tax_rates ?? [])->ofRegion(old('country') ?? ($request->user()->billing_information->country ?? null))->orderBy('type')->get();

        // Sum the inclusive tax rates
        $inclTaxRatesPercentage = $taxRates->where('type', '=', 0)->sum('percentage');

        // Sum the exclusive tax rates
        $exclTaxRatesPercentage = $taxRates->where('type', '=', 1)->sum('percentage');

        return view('checkout.index', ['plan' => $plan, 'user' => $request->user(), 'taxRates' => $taxRates, 'coupon' => $coupon, 'inclTaxRatesPercentage' => $inclTaxRatesPercentage, 'exclTaxRatesPercentage' => $exclTaxRatesPercentage]);
    }

    /**
     * Process the payment request.
     */
    public function process(ProcessCheckoutRequest $request, $id): View|RedirectResponse
    {
        $plan = Plan::where('id', '=', $id)
            ->where('amount_month', '>', 0)
            ->where('amount_year', '>', 0)
            ->firstOrFail();

        // If the user is already subscribed to the plan
        if ($request->user()->active_plan->id == $plan->id) {
            return redirect()->route('pricing');
        }

        // If the user wants to skip trial
        if ($request->input('skip_trial')) {
            $request->user()->plan_trial_ends_at = Carbon::now();
            $request->user()->save();
            return redirect()->back()->withInput();
        }

        // If the user's country has changed, or a coupon was applied
        if ($request->has('country') && !$request->has('submit') || $request->has('coupon') && !$request->has('coupon_set')) {
            return redirect()->back()->withInput();
        }

        // Update the user's billing information
        $request->user()->billing_information = [
            'city' => $request->input('city'),
            'country' => $request->input('country'),
            'postal_code' => $request->input('postal_code'),
            'state' => $request->input('state'),
            'address' => $request->input('address'),
            'name' => $request->input('name'),
            'phone' => $request->input('phone')
        ];

        $request->user()->save();

        // Get the coupon
        $coupon = $plan->coupons && $request->input('coupon') ? Coupon::where('code', '=', $request->input('coupon'))->firstOrFail() : null;

        // Get the tax rates
        $taxRates = TaxRate::whereIn('id', $plan->tax_rates ?? [])->ofRegion($request->user()->billing_information->country ?? null)->orderBy('type')->get();

        // Sum the inclusive tax rates
        $inclTaxRatesPercentage = $taxRates->where('type', '=', 0)->sum('percentage');

        // Sum the exclusive tax rates
        $exclTaxRatesPercentage = $taxRates->where('type', '=', 1)->sum('percentage');

        // Get the total amount to be charged
        $amount = formatMoney(checkoutTotal(($request->input('interval') == 'year' ? $plan->amount_year : $plan->amount_month), $coupon->percentage ?? 0, $exclTaxRatesPercentage, $inclTaxRatesPercentage), $plan->currency, false, false);

        $taxRates = TaxRate::whereIn('id', $plan->tax_rates ?? [])->ofRegion($request->user()->billing_information->country ?? null)->orderBy('type')->get();

        // If a trial is available
        if ($plan->trial_days > 0 && !$request->user()->plan_trial_ends_at) {
            return $this->tryPlan($request, $plan);
        }

        // If a redeemable coupon was used
        if ($coupon && $coupon->isRedeemable() == 1) {
            return $this->redeemPlan($request, $plan, $coupon);
        }

        return match ($request->input('payment_processor')) {
            'paypal' => $this->initPayPal($request, $plan, $coupon, $taxRates, $amount),
            'stripe' => $this->initStripe($request, $plan, $coupon, $taxRates, $amount),
            'mollie' => $this->initMollie($request, $plan, $coupon, $taxRates, $amount),
            'paddle' => $this->initPaddle($request, $plan, $coupon, $taxRates, $amount),
            'razorpay' => $this->initRazorpay($request, $plan, $coupon, $taxRates, $amount),
            'paystack' => $this->initPaystack($request, $plan, $coupon, $taxRates, $amount),
            'xendit' => $this->initXendit($request, $plan, $coupon, $taxRates, $amount),
            'mercadopago' => $this->initMercadoPago($request, $plan, $coupon, $taxRates, $amount),
            'yoomoney' => $this->initYooMoney($request, $plan, $coupon, $taxRates, $amount),
            'nowpayments' => $this->initNowPayments($request, $plan, $coupon, $taxRates, $amount),
            'cryptocom' => $this->initCryptocom($request, $plan, $coupon, $taxRates, $amount),
            'bank' => $this->initBank($request, $plan, $coupon, $taxRates, $amount),
            default => back()
        };
    }

    /**
     * Redeem a plan.
     */
    private function redeemPlan(Request $request, Plan $plan, Coupon $coupon): RedirectResponse
    {
        $request->user()->cancelPlanSubscription();

        $now = Carbon::now();

        $request->user()->plan_id = $plan->id;
        $request->user()->plan_amount = null;
        $request->user()->plan_currency = null;
        $request->user()->plan_interval = null;
        $request->user()->plan_payment_processor = null;
        $request->user()->plan_subscription_id = null;
        $request->user()->plan_subscription_status = null;
        $request->user()->plan_subscription_information = null;
        $request->user()->plan_created_at = $now;
        $request->user()->plan_recurring_at = null;
        $request->user()->plan_ends_at = $coupon->days < 0 ? null : (clone $now)->addDays($coupon->days);
        $request->user()->save();

        $coupon->increment('redeems', 1);

        return redirect()->route('checkout.complete');
    }

    /**
     * Try a plan.
     */
    private function tryPlan(Request $request, Plan $plan): RedirectResponse
    {
        $request->user()->cancelPlanSubscription();

        $now = Carbon::now();

        $request->user()->plan_id = $plan->id;
        $request->user()->plan_amount = null;
        $request->user()->plan_currency = null;
        $request->user()->plan_interval = null;
        $request->user()->plan_payment_processor = null;
        $request->user()->plan_subscription_id = null;
        $request->user()->plan_subscription_status = null;
        $request->user()->plan_subscription_information = null;
        $request->user()->plan_created_at = $now;
        $request->user()->plan_recurring_at = null;
        $request->user()->plan_trial_ends_at = (clone $now)->addDays($plan->trial_days);
        $request->user()->plan_ends_at = (clone $now)->addDays($plan->trial_days);
        $request->user()->save();

        return redirect()->route('checkout.complete');
    }

    /**
     * Initialize the PayPal payment.
     */
    private function initPayPal(Request $request, Plan $plan, ?Coupon $coupon, ?Collection $taxRates, string $amount): RedirectResponse
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
            return back()->with('error', $e->getResponse()->getBody()->getContents());
        }

        try {
            $payPalProductRequest = $httpClient->request('GET', 'https://' . (config('settings.paypal_mode') == 'sandbox' ? 'api-m.sandbox' : 'api-m') . '.paypal.com/v1/catalogs/products/' . Str::slug(config('settings.title')), [
                'headers' => [
                    'Authorization' => 'Bearer ' . $payPalAuth->access_token,
                    'Content-Type' => 'application/json'
                ]
            ]);

            $payPalProduct = json_decode($payPalProductRequest->getBody()->getContents());
        } catch (Exception) {
            try {
                $payPalProductRequest = $httpClient->request('POST', 'https://' . (config('settings.paypal_mode') == 'sandbox' ? 'api-m.sandbox' : 'api-m') . '.paypal.com/v1/catalogs/products', [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $payPalAuth->access_token,
                        'Content-Type' => 'application/json'
                    ],
                    'body' => json_encode([
                        'id' => Str::slug(config('settings.title')),
                        'name' => config('settings.title'),
                        'type' => 'SERVICE'
                    ])
                ]);

                $payPalProduct = json_decode($payPalProductRequest->getBody()->getContents());
            } catch (Exception $e) {
                return back()->with('error', $e->getResponse()->getBody()->getContents());
            }
        }

        $payPalAmount = $amount;

        try {
            $payPalPlanRequest = $httpClient->request('POST', 'https://' . (config('settings.paypal_mode') == 'sandbox' ? 'api-m.sandbox' : 'api-m') . '.paypal.com/v1/billing/plans', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $payPalAuth->access_token,
                    'Content-Type' => 'application/json'
                ],
                'body' => json_encode([
                    'product_id' => $payPalProduct->id,
                    'name' => $plan->name,
                    'status' => 'ACTIVE',
                    'billing_cycles' => [
                        [
                            'frequency' => [
                                'interval_unit' => strtoupper($request->input('interval')),
                                'interval_count' => 1,
                            ],
                            'tenure_type' => 'REGULAR',
                            'sequence' => 1,
                            'total_cycles' => 0,
                            'pricing_scheme' => [
                                'fixed_price' => [
                                    'value' => $payPalAmount,
                                    'currency_code' => $plan->currency,
                                ],
                            ]
                        ]
                    ],
                    'payment_preferences' => [
                        'auto_bill_outstanding' => true,
                        'payment_failure_threshold' => 0,
                    ],
                ])
            ]);

            $payPalPlan = json_decode($payPalPlanRequest->getBody()->getContents());
        } catch (Exception $e) {
            return back()->with('error', $e->getResponse()->getBody()->getContents());
        }

        try {
            $payPalSubscriptionRequest = $httpClient->request('POST', 'https://' . (config('settings.paypal_mode') == 'sandbox' ? 'api-m.sandbox' : 'api-m') . '.paypal.com/v1/billing/subscriptions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $payPalAuth->access_token,
                    'Content-Type' => 'application/json'
                ],
                'body' => json_encode([
                    'plan_id' => $payPalPlan->id,
                    'application_context' => [
                        'brand_name' => config('settings.title'),
                        'locale' => 'en-US',
                        'shipping_preference' => 'SET_PROVIDED_ADDRESS',
                        'user_action' => 'SUBSCRIBE_NOW',
                        'payment_method' => [
                            'payer_selected' => 'PAYPAL',
                            'payee_preferred' => 'IMMEDIATE_PAYMENT_REQUIRED',
                        ],
                        'return_url' => route('checkout.complete'),
                        'cancel_url' => route('checkout.cancelled')
                    ],
                    'custom_id' => http_build_query([
                        'user' => $request->user()->id,
                        'plan' => $plan->id,
                        'plan_amount' => $request->input('interval') == 'year' ? $plan->amount_year : $plan->amount_month,
                        'amount' => $amount,
                        'currency' => $plan->currency,
                        'interval' => $request->input('interval'),
                        'coupon' => $coupon->id ?? null,
                        'tax_rates' => $taxRates->pluck('id')->implode('_'),
                    ])
                ])
            ]);

            $payPalSubscription = json_decode($payPalSubscriptionRequest->getBody()->getContents());
        } catch (Exception $e) {
            return back()->with('error', $e->getResponse()->getBody()->getContents());
        }

        return redirect($payPalSubscription->links[0]->href);
    }

    /**
     * Initialize the Stripe payment.
     */
    private function initStripe(Request $request, Plan $plan, ?Coupon $coupon, ?Collection $taxRates, string $amount): View|RedirectResponse
    {
        $httpClient = new GuzzleClient();

        $stripeAmount = in_array($plan->currency, config('currencies.zero_decimals')) ? $amount : ($amount * 100);

        try {
            $formParams = [
                'mode' => 'subscription',
                'success_url' => route('checkout.complete'),
                'cancel_url' => route('checkout.cancelled'),
                'line_items' => [
                    [
                        'price_data' => [
                            'currency' => $plan->currency,
                            'product_data' => [
                                'name' => $plan->name,
                            ],
                            'unit_amount' => $stripeAmount,
                            'recurring' => [
                                'interval' => $request->input('interval'),
                            ],
                        ],
                        'quantity' => 1,
                    ],
                ],
                'subscription_data' => [
                    'metadata' => [
                        'user' => $request->user()->id,
                        'plan' => $plan->id,
                        'plan_amount' => $request->input('interval') == 'year' ? $plan->amount_year : $plan->amount_month,
                        'amount' => $amount,
                        'currency' => $plan->currency,
                        'interval' => $request->input('interval'),
                        'coupon' => $coupon->id ?? null,
                        'tax_rates' => $taxRates->pluck('id')->implode('_'),
                    ],
                ],
            ];

            $formParams['payment_method_types'][] = 'card';
            if (config('settings.stripe_ideal')) {
                $formParams['payment_method_types'][] = 'ideal';
            }
            if (config('settings.stripe_klarna')) {
                $formParams['payment_method_types'][] = 'klarna';
            }
            if (config('settings.stripe_sepa_direct_debit')) {
                $formParams['payment_method_types'][] = 'sepa_debit';
            }

            if (config('settings.stripe_tax') && !config('settings.invoicing')) {
                $formParams['automatic_tax'] = [
                    'enabled' => 'true',
                ];
            }

            $stripeSessionRequest = $httpClient->request('POST', 'https://api.stripe.com/v1/checkout/sessions', [
                'auth' => [config('settings.stripe_secret'), ''],
                'form_params' => $formParams,
            ]);

            $stripeSession = json_decode($stripeSessionRequest->getBody()->getContents());
        } catch (Exception $e) {
            return back()->with('error', $e->getResponse()->getBody()->getContents());
        }

        return redirect($stripeSession->url);
    }

    /**
     * Initialize the Mollie payment.
     */
    private function initMollie(Request $request, Plan $plan, ?Coupon $coupon, ?Collection $taxRates, string $amount): RedirectResponse
    {
        $httpClient = new GuzzleClient();

        try {
            $mollieCustomerRequest = $httpClient->request('POST', 'https://api.mollie.com/v2/customers', [
                'headers' => [
                    'Authorization' => 'Bearer ' . config('settings.mollie_key'),
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                ]
            ]);

            $mollieCustomer = json_decode($mollieCustomerRequest->getBody(), true);
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        try {
            $molliePaymentRequest = $httpClient->request('POST', 'https://api.mollie.com/v2/customers/' . $mollieCustomer['id'] . '/payments', [
                'headers' => [
                    'Authorization' => 'Bearer ' . config('settings.mollie_key'),
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'amount' => [
                        'value' => $amount,
                        'currency' => $plan->currency,
                    ],
                    'description' => $plan->name,
                    'redirectUrl' => route('checkout.complete'),
                    'webhookUrl' => route('webhooks.mollie'),
                    'metadata' => [
                        'user' => $request->user()->id,
                        'plan' => $plan->id,
                        'plan_amount' => $request->input('interval') == 'year' ? $plan->amount_year : $plan->amount_month,
                        'amount' => $amount,
                        'currency' => $plan->currency,
                        'interval' => $request->input('interval'),
                        'coupon' => $coupon->id ?? null,
                        'tax_rates' => $taxRates->pluck('id')->implode('_')
                    ],
                    'sequenceType' => 'first',
                ]
            ]);

            $molliePayment = json_decode($molliePaymentRequest->getBody(), true);
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect($molliePayment['_links']['checkout']['href']);
    }

    /**
     * Initialize the Razorpay payment.
     */
    private function initPaddle(Request $request, Plan $plan, ?Coupon $coupon, ?Collection $taxRates, string $amount): RedirectResponse|View
    {
        $httpClient = new GuzzleClient();

        try {
            $paddleProductRequest = $httpClient->request('POST', 'https://' . (config('settings.paddle_mode') == 'sandbox' ? 'sandbox-api' : 'api') . '.paddle.com/products', [
                'headers' => [
                    'Authorization' => 'Bearer ' . config('settings.paddle_api_key'),
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => [
                    'name' => $plan->name,
                    'description' => $plan->description,
                    'tax_category' => 'standard',
                ]
            ]);

            $paddleProduct = json_decode($paddleProductRequest->getBody()->getContents(), true)['data'];
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        $paddleAmount = in_array($plan->currency, config('currencies.zero_decimals')) ? $amount : ($amount * 100);

        try {
            $paddlePriceRequest = $httpClient->request('POST', 'https://' . (config('settings.paddle_mode') == 'sandbox' ? 'sandbox-api' : 'api') . '.paddle.com/prices', [
                'headers' => [
                    'Authorization' => 'Bearer ' . config('settings.paddle_api_key'),
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => [
                    'product_id' => $paddleProduct['id'],
                    'description' => $plan->name,
                    'unit_price' => [
                        'amount' => (string) $paddleAmount,
                        'currency_code' => $plan->currency
                    ],
                    'billing_cycle' => [
                        'interval' => $request->input('interval'),
                        'frequency' => 1
                    ],
                    'quantity' => [
                        'minimum' => 1,
                        'maximum' => 1
                    ],
                    'custom_data' => [
                        'user' => $request->user()->id,
                        'plan' => $plan->id,
                        'plan_amount' => $request->input('interval') == 'year' ? $plan->amount_year : $plan->amount_month,
                        'amount' => $amount,
                        'currency' => $plan->currency,
                        'interval' => $request->input('interval'),
                        'coupon' => $coupon->id ?? null,
                        'tax_rates' => $taxRates->pluck('id')->implode('_')
                    ]
                ]
            ]);

            $paddlePrice = json_decode($paddlePriceRequest->getBody()->getContents(), true)['data'];
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return view('checkout.processors.paddle', ['price' => $paddlePrice]);
    }

    /**
     * Initialize the Razorpay payment.
     */
    private function initRazorpay(Request $request, Plan $plan, ?Coupon $coupon, ?Collection $taxRates, string $amount): RedirectResponse
    {
        $httpClient = new GuzzleClient();

        $razorpayAmount = in_array($plan->currency, config('currencies.zero_decimals')) ? $amount : ($amount * 100);

        try {
            $razorpayPlanRequest = $httpClient->request('POST', 'https://api.razorpay.com/v1/plans', [
                'auth' => [config('settings.razorpay_key'), config('settings.razorpay_secret')],
                'json' => [
                    'period' => $request->input('interval') == 'month' ? 'monthly' : 'yearly',
                    'interval' => 1,
                    'item' => [
                        'name' => $plan->name,
                        'amount' => $razorpayAmount,
                        'currency' => $plan->currency,
                    ]
                ]
            ]);

            $razorpayPlan = json_decode($razorpayPlanRequest->getBody()->getContents());
        } catch (Exception $e) {
            return back()->with('error', $e->getCode() . ' - ' . $e->getMessage());
        }

        try {
            $razorpaySubscriptionRequest = $httpClient->request('POST', 'https://api.razorpay.com/v1/subscriptions', [
                'auth' => [config('settings.razorpay_key'), config('settings.razorpay_secret')],
                'json' => [
                    'plan_id' => $razorpayPlan->id,
                    'total_count' => $request->input('interval') == 'month' ? 36 : 3,
                    'notes' => [
                        'user' => $request->user()->id,
                        'plan' => $plan->id,
                        'plan_amount' => $request->input('interval') == 'year' ? $plan->amount_year : $plan->amount_month,
                        'amount' => $amount,
                        'currency' => $plan->currency,
                        'interval' => $request->input('interval'),
                        'coupon' => $coupon->id ?? null,
                        'tax_rates' => $taxRates->pluck('id')->implode('_')
                    ]
                ]
            ]);

            $razorpaySubscription = json_decode($razorpaySubscriptionRequest->getBody()->getContents());
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect($razorpaySubscription->short_url);
    }

    /**
     * Initialize the Paystack payment.
     */
    private function initPaystack(Request $request, Plan $plan, ?Coupon $coupon, ?Collection $taxRates, string $amount): RedirectResponse
    {
        $httpClient = new GuzzleClient();

        $paystackAmount = in_array($plan->currency, config('currencies.zero_decimals')) ? $amount : ($amount * 100);

        try {
            $paystackPlanRequest = $httpClient->request('POST', 'https://api.paystack.co/plan', [
                'headers' => [
                    'Authorization' => 'Bearer ' . config('settings.paystack_secret'),
                    'Content-Type' => 'application/json',
                    'Cache-Control' => 'no-cache'
                ],
                'body' => json_encode([
                    'name' => $plan->name,
                    'interval' => 'monthly',
                    'amount' => $paystackAmount,
                    'currency' => $plan->currency,
                    'description' => http_build_query([
                        'user' => $request->user()->id,
                        'plan' => $plan->id,
                        'plan_amount' => $request->input('interval') == 'year' ? $plan->amount_year : $plan->amount_month,
                        'amount' => $amount,
                        'currency' => $plan->currency,
                        'interval' => $request->input('interval'),
                        'coupon' => $coupon->id ?? null,
                        'tax_rates' => $taxRates->pluck('id')->implode('_'),
                    ])
                ])
            ]);

            $paystackPlan = json_decode($paystackPlanRequest->getBody()->getContents());
        } catch (Exception $e) {
            return back()->with('error',  $e->getMessage());
        }

        try {
            $paystackSubscriptionRequest = $httpClient->request('POST', 'https://api.paystack.co/transaction/initialize', [
                'headers' => [
                    'Authorization' => 'Bearer ' . config('settings.paystack_secret'),
                    'Content-Type' => 'application/json',
                    'Cache-Control' => 'no-cache'
                ],
                'body' => json_encode([
                    'email' => $request->user()->email,
                    'amount' => $paystackAmount,
                    'currency' => $plan->currency,
                    'callback_url' => route('checkout.complete'),
                    'plan' => $paystackPlan->data->plan_code
                ])
            ]);

            $paystackSubscription = json_decode($paystackSubscriptionRequest->getBody()->getContents());
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect($paystackSubscription->data->authorization_url);
    }

    /**
     * Initialize the Xendit payment.
     */
    private function initXendit(Request $request, Plan $plan, ?Coupon $coupon, ?Collection $taxRates, string $amount): RedirectResponse
    {
        $httpClient = new GuzzleClient();

        try {
            $xenditCustomerRequest = $httpClient->request('POST', 'https://api.xendit.co/customers', [
                'auth' => [config('settings.xendit_secret_key'), ''],
                'headers' => [
                    'api-version' => '2020-10-31',
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'reference_id' => Str::uuid(),
                    'type' => 'INDIVIDUAL',
                    'individual_detail' => [
                        'given_names' => $request->user()->name,
                    ],
                    'email' => $request->user()->email,
                ]
            ]);

            $xenditCustomer = json_decode($xenditCustomerRequest->getBody()->getContents());
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        try {
            $xenditSessionRequest = $httpClient->request('POST', 'https://api.xendit.co/sessions', [
                'auth' => [config('settings.xendit_secret_key'), ''],
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'reference_id' => Str::uuid(),
                    'customer_id' => $xenditCustomer->id,
                    'session_type' => 'PAY',
                    'allow_save_payment_method' => 'FORCED',
                    'mode' => 'PAYMENT_LINK',
                    'amount' => (float) $amount,
                    'currency' => $plan->currency,
                    'country' => $request->user()->billing_information->country,
                    'channel_properties' => [
                        'cards' => [
                            'card_on_file_type' => 'RECURRING',
                            'recurring_configuration' => [
                                'recurring_expiry' => Carbon::now()->addYears(10)->format('Y-m-d'),
                                'recurring_frequency' => $request->input('interval') == 'year' ? 365 : 30,
                            ],
                        ],
                    ],
                    'metadata' => [
                        'user' => (string) $request->user()->id,
                        'plan' => (string) $plan->id,
                        'plan_amount' => (string) ($request->input('interval') == 'year' ? $plan->amount_year : $plan->amount_month),
                        'amount' => $amount,
                        'currency' => $plan->currency,
                        'interval' => $request->input('interval'),
                        'coupon' => (string) ($coupon->id ?? ''),
                        'tax_rates' => $taxRates->pluck('id')->implode('_')
                    ],
                    'description' => $plan->name,
                    'success_return_url' => route('checkout.complete'),
                    'cancel_return_url' => route('checkout.cancelled'),
                ]
            ]);

            $xenditSession = json_decode($xenditSessionRequest->getBody()->getContents());
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect($xenditSession->payment_link_url);
    }

    /**
     * Initialize the Mercado Pago payment.
     */
    private function initMercadoPago(Request $request, Plan $plan, ?Coupon $coupon, ?Collection $taxRates, string $amount): RedirectResponse
    {
        $httpClient = new GuzzleClient();

        try {
            $mercadoPagoPayload = [
                'items' => [
                    [
                        'title' => $plan->name,
                        'quantity' => 1,
                        'currency_id' => $plan->currency,
                        'unit_price' => (float) $amount,
                    ]
                ],
                'back_urls' => [
                    'success' => route('checkout.complete'),
                    'pending' => route('checkout.pending'),
                    'failure' => route('checkout.cancelled'),
                ],
                'auto_return' => 'approved',
                'external_reference' => http_build_query([
                    'user' => $request->user()->id,
                    'plan' => $plan->id,
                    'plan_amount' => $request->input('interval') == 'year' ? $plan->amount_year : $plan->amount_month,
                    'amount' => $amount,
                    'currency' => $plan->currency,
                    'interval' => $request->input('interval'),
                    'coupon' => $coupon->id ?? null,
                    'tax_rates' => $taxRates->pluck('id')->implode('_'),
                ]),
                'notification_url' => route('webhooks.mercadopago'),
            ];

            $mercadoPagoRequest = $httpClient->request('POST', 'https://api.mercadopago.com/checkout/preferences', [
                'headers' => [
                    'Authorization' => 'Bearer ' . config('settings.mercadopago_access_token'),
                    'Content-Type' => 'application/json',
                ],
                'json' => $mercadoPagoPayload,
            ]);

            $mercadoPagoResponse = json_decode($mercadoPagoRequest->getBody()->getContents(), true);
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect($mercadoPagoResponse['init_point']);
    }

    /**
     * Initialize the YooMoney payment.
     */
    private function initYooMoney(Request $request, Plan $plan, ?Coupon $coupon, ?Collection $taxRates, string $amount): RedirectResponse
    {
        $httpClient = new GuzzleClient();

        $yooMoneyAmount = number_format($amount, 2, '.', '');

        $formParams = [
            'amount' => [
                'value' => $yooMoneyAmount,
                'currency' => $plan->currency,
            ],
            'confirmation' => [
                'type' => 'redirect',
                'return_url' => route('checkout.complete')
            ],
            'capture' => true,
            'description' => $plan->name,
            'metadata' => [
                'user' => $request->user()->id,
                'plan' => $plan->id,
                'plan_amount' => $request->input('interval') == 'year' ? $plan->amount_year : $plan->amount_month,
                'amount' => $amount,
                'currency' => $plan->currency,
                'interval' => $request->input('interval'),
                'coupon' => $coupon->id ?? null,
                'tax_rates' => $taxRates->pluck('id')->implode('_')
            ]
        ];

        if (config('settings.yoomoney_receipts')) {
            $formParams['receipt'] = [
                'customer' => [
                    'email' => $request->user()->email,
                ],
                'items' => [
                    [
                        'description' => $plan->name,
                        'quantity' => '1.00',
                        'amount' => [
                            'value' => $yooMoneyAmount,
                            'currency' => $plan->currency,
                        ],
                        'vat_code' => config('settings.yoomoney_vat_code'),
                        'payment_mode' => 'full_prepayment',
                        'payment_subject' => 'service',
                    ]
                ]
            ];
        }

        try {
            $yooMoneyRequest = $httpClient->request('POST', 'https://api.yookassa.ru/v3/payments', [
                'auth' => [config('settings.yoomoney_shop_id'), config('settings.yoomoney_secret_key')],
                'headers' => [
                    'Idempotence-Key' => (string) Str::uuid(),
                    'Content-Type' => 'application/json'
                ],
                'json' => $formParams
            ]);

            $yooMoney = json_decode($yooMoneyRequest->getBody()->getContents());
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect($yooMoney->confirmation->confirmation_url);
    }

    /**
     * Initialize the NOWPayments payment.
     */
    private function initNowPayments(Request $request, Plan $plan, ?Coupon $coupon, ?Collection $taxRates, string $amount): RedirectResponse
    {
        $httpClient = new GuzzleClient();

        try {
            $nowPaymentsCheckoutRequest = $httpClient->request('POST', 'https://api.nowpayments.io/v1/invoice', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'x-api-key' => config('settings.nowpayments_key'),
                ],
                'body' => json_encode([
                    'price_amount' => $amount,
                    'price_currency' => strtolower($plan->currency),
                    'order_id' => (string) Str::uuid(),
                    'order_description' => http_build_query([
                        'user' => $request->user()->id,
                        'plan' => $plan->id,
                        'plan_amount' => $request->input('interval') == 'year' ? $plan->amount_year : $plan->amount_month,
                        'amount' => $amount,
                        'currency' => $plan->currency,
                        'interval' => $request->input('interval'),
                        'coupon' => $coupon->id ?? null,
                        'tax_rates' => $taxRates->pluck('id')->implode('_'),
                    ]),
                    'ipn_callback_url' => route('webhooks.nowpayments'),
                    'success_url' => route('checkout.complete'),
                    'cancel_url' => route('checkout.cancelled'),
                ]),
            ]);

            $nowPayments = json_decode($nowPaymentsCheckoutRequest->getBody()->getContents());
        } catch (Exception $e) {
            return back()->with('error', $e->getResponse()->getBody()->getContents());
        }

        return redirect($nowPayments->invoice_url);
    }

    /**
     * Initialize the Crypto.com payment.
     */
    private function initCryptocom(Request $request, Plan $plan, ?Coupon $coupon, ?Collection $taxRates, string $amount): RedirectResponse
    {
        $httpClient = new GuzzleClient();

        $cryptocomAmount = in_array($plan->currency, config('currencies.zero_decimals')) ? $amount : ($amount * 100);

        try {
            $cryptocomCheckoutRequest = $httpClient->request('POST', 'https://pay.crypto.com/api/payments', [
                'auth' => [config('settings.cryptocom_secret'), ''],
                'form_params' => [
                    'description' => $plan->name,
                    'amount' => $cryptocomAmount,
                    'currency' => $plan->currency,
                    'metadata' => [
                        'user' => $request->user()->id,
                        'plan' => $plan->id,
                        'plan_amount' => $request->input('interval') == 'year' ? $plan->amount_year : $plan->amount_month,
                        'amount' => $amount,
                        'currency' => $plan->currency,
                        'interval' => $request->input('interval'),
                        'coupon' => $coupon->id ?? null,
                        'tax_rates' => $taxRates->pluck('id')->implode('_')
                    ],
                    'return_url' => route('checkout.complete'),
                    'cancel_url' => route('checkout.cancelled')
                ]
            ]);

            $cryptocom = json_decode($cryptocomCheckoutRequest->getBody()->getContents());
        } catch (Exception $e) {
            return back()->with('error', $e->getResponse()->getBody()->getContents());
        }

        return redirect($cryptocom->payment_url);
    }

    /**
     * Initialize the Bank payment.
     */
    private function initBank(Request $request, Plan $plan, ?Coupon $coupon, ?Collection $taxRates, string $amount): RedirectResponse
    {
        $this->paymentService->store([
            'user_id' => $request->user()->id,
            'plan_id' => $plan->id,
            'payment_id' => $request->payment_id,
            'processor' => 'bank',
            'amount' => $amount,
            'currency' => $plan->currency,
            'interval' => $request->input('interval'),
            'status' => 'pending',
            'coupon' => $coupon->id ?? null,
            'tax_rates' => $taxRates->pluck('id')->implode('_'),
            'customer' => $request->user()->billing_information,
        ]);

        return redirect()->route('checkout.pending');
    }

    /**
     * Show the payment complete page.
     */
    public function complete(): View
    {
        return view('checkout.complete');
    }

    /**
     * Show the payment pending page.
     */
    public function pending(): View
    {
        return view('checkout.pending');
    }

    /**
     * Show the payment cancelled page.
     */
    public function cancelled(): View
    {
        return view('checkout.cancelled');
    }
}
