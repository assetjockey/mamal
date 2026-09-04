<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Gateway;

class GatewaysSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $platforms = [
            ['id' => 1, 'name' => 'paypal', 'logo' => 'assets/img/payments/paypal.png', 'status' => false],
            ['id' => 2, 'name' => 'stripe', 'logo' => 'assets/img/payments/stripe.png', 'status' => false],
            ['id' => 3, 'name' => 'banktransfer', 'logo' => 'assets/img/payments/bank-transfer.png', 'status' => false],
            ['id' => 4, 'name' => 'paystack', 'logo' => 'assets/img/payments/paystack.png', 'status' => false],
            ['id' => 5, 'name' => 'razorpay', 'logo' => 'assets/img/payments/razorpay.png', 'status' => false],
            ['id' => 6, 'name' => 'braintree', 'logo' => 'assets/img/payments/braintree.svg', 'status' => false],
            ['id' => 7, 'name' => 'mollie', 'logo' => 'assets/img/payments/mollie.svg', 'status' => false],
            ['id' => 8, 'name' => 'coinbase', 'logo' => 'assets/img/payments/coinbase.svg', 'status' => false],
            ['id' => 9, 'name' => 'midtrans', 'logo' => 'assets/img/payments/midtrans.png', 'status' => false],
            ['id' => 10, 'name' => 'flutterwave', 'logo' => 'assets/img/payments/flutterwave.png', 'status' => false],
            ['id' => 11, 'name' => 'yookassa', 'logo' => 'assets/img/payments/yookassa.svg', 'status' => false],
            ['id' => 12, 'name' => 'paddle', 'logo' => 'assets/img/payments/paddle.jpg', 'status' => false],
            ['id' => 13, 'name' => 'mercadopago', 'logo' => 'assets/img/payments/mercadopago.svg', 'status' => false],
            ['id' => 14, 'name' => 'twocheckout', 'logo' => 'assets/img/payments/twocheckout.svg', 'status' => false],
            ['id' => 15, 'name' => 'iyzico', 'logo' => 'assets/img/payments/iyzico.svg', 'status' => false],
            ['id' => 16, 'name' => 'robokassa', 'logo' => 'assets/img/payments/robokassa.svg', 'status' => false],
            ['id' => 17, 'name' => 'paytm', 'logo' => 'assets/img/payments/paytm.svg', 'status' => false],
            ['id' => 18, 'name' => 'alipay', 'logo' => 'assets/img/payments/paytm.svg', 'status' => false],
            ['id' => 19, 'name' => 'wepay', 'logo' => 'assets/img/payments/wepay.svg', 'status' => false],
            ['id' => 20, 'name' => 'coinremitter', 'logo' => 'assets/img/payments/coinremitter.avif', 'status' => false],
            ['id' => 21, 'name' => 'wallet', 'logo' => 'assets/img/payments/wallet.avif', 'status' => false],
            ['id' => 22, 'name' => 'awdpay', 'logo' => 'assets/img/payments/awdpay.png', 'status' => false],
        ];

        foreach ($platforms as $platform) {
            Gateway::updateOrCreate(['id' => $platform['id']], $platform);
        }
    }
}
