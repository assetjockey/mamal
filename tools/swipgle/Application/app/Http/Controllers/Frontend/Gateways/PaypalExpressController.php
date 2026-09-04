<?php

namespace App\Http\Controllers\Frontend\Gateways;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Frontend\User\CheckoutController;
use App\Models\Transaction;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PayPal\Api\Payer;
use Vironeer\PayPal\Core\PayPalHttpClient;
use Vironeer\PayPal\Core\ProductionEnvironment;
use Vironeer\PayPal\Core\SandboxEnvironment;
use Vironeer\PayPal\Orders\OrdersCaptureRequest;
use Vironeer\PayPal\Orders\OrdersCreateRequest;

class PaypalExpressController extends Controller
{
    private $paymentGateway;
    private $environment;

    public function __construct()
    {
        $this->paymentGateway = paymentGateway('paypal_express');
        if ($this->paymentGateway->test_mode == 1) {
            $this->environment = new SandboxEnvironment(
                $this->paymentGateway->credentials->client_id,
                $this->paymentGateway->credentials->client_secret
            );
        } else {
            $this->environment = new ProductionEnvironment(
                $this->paymentGateway->credentials->client_id,
                $this->paymentGateway->credentials->client_secret
            );
        }
    }

    public function process($trx)
    {
        if ($trx->status != 0) {
            $data['error'] = true;
            $data['msg'] = lang('Invalid or expired transaction', 'checkout');
            return json_encode($data);
        }

        if ($trx->plan->interval == 0) {
            $planInterval = '(Monthly)';
        } elseif ($trx->plan->interval == 1) {
            $planInterval = '(Yearly)';
        } elseif ($trx->plan->interval == 2) {
            $planInterval = '(Lifetime)';
        }

        $paymentName = "Payment for subscription " . $trx->plan->name . " Plan " . $planInterval;
        $gatewayFees = round(($trx->total_price * paymentGateway('paypal_express')->fees) / 100, 2);
        $priceIncludeFees = round($trx->plan_price + $gatewayFees, 2);

        $client = new PayPalHttpClient($this->environment);
        $request = new OrdersCreateRequest();
        $request->prefer('return=representation');

        $request->body = [
            "intent" => "CAPTURE",
            "purchase_units" => [[
                "reference_id" => $trx->transaction_id,
                "description" => $paymentName,
                "amount" => [
                    "value" => (string) $priceIncludeFees,
                    "currency_code" => currencyCode(),
                    "breakdown" => [
                        "item_total" => [
                            "value" => (string) round($trx->plan_price, 2),
                            "currency_code" => currencyCode(),
                        ],
                        "handling" => [
                            "value" => (string) $gatewayFees,
                            "currency_code" => currencyCode(),
                        ],
                    ],
                ],
            ]],
            "application_context" => [
                "return_url" => route('ipn.paypal_express'),
                "cancel_url" => route('user.subscription'),
                "shipping_preference" => "NO_SHIPPING",
            ],
        ];

        try {
            $response = $client->execute($request);
            $data['error'] = false;
            $data['redirectUrl'] = $response->result->links[1]->href;
            return json_encode($data);
        } catch (\Exception $e) {
            $data['error'] = true;
            $data['msg'] = $e->getMessage();
            return json_encode($data);
        }

    }

    public function ipn(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => ['required'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                throw new Exception($error);
            }
        }

        try {
            $ordersCaptureRequest = new OrdersCaptureRequest($request->token);
            $ordersCaptureRequest->prefer('return=representation');
            $client = new PayPalHttpClient($this->environment);
            $response = $client->execute($ordersCaptureRequest);

            if (@$response->result->status != 'COMPLETED') {
                throw new Exception(lang('Payment failed', 'checkout'));
            }

            $id = $response->result->purchase_units[0]->reference_id;

            $trx = Transaction::where([['user_id', userAuthInfo()->id], ['transaction_id', $id], ['status', 1]])->first();
            if (is_null($trx)) {
                throw new Exception(lang('Invalid or expired transaction', 'checkout'));
            }

            $payment_id = $response->result->id;
            $payer_id = $response->result->payer->payer_id;
            $payer_email = $response->result->payer->email_address;

            $handling_fee = $response->result->purchase_units[0]->payments->captures[0]->seller_receivable_breakdown->paypal_fee->value;
            $total = ($trx->total_price + $handling_fee);

            $updateTrx = $trx->update([
                'fees_price' => $handling_fee,
                'total_price' => $total,
                'payment_gateway_id' => $this->paymentGateway->id,
                'payment_id' => $payment_id,
                'payer_id' => $payer_id,
                'payer_email' => $payer_email,
                'status' => 2,
            ]);

            if ($updateTrx) {
                CheckoutController::updateSubscription($trx);
                toastr()->success(lang('Payment made successfully', 'checkout'));
                return redirect()->route('user.subscription');
            }
        } catch (\Exception $e) {
            toastr()->error($e->getMessage());
            return redirect()->route('home');
        }
    }
}