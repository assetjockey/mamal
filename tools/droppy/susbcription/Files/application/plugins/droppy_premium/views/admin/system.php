<?php
//Get the base dir.
$basedir = APPPATH . 'plugins/droppy_premium/';

?>
<div class="card">
    <div class="card-header">
        <div class="col">
            <h4 class="card-title">System setup</h4>
        </div>
    </div>
    <div class="card-body">
        <div class="form-panel" id="settings" style="overflow:hidden;">
            <p>More info about setting up the IPN, Webhook and cron can be found in the documentation and the <a href="https://support.proxibolt.com">Support page</a></p>
            <h3>Paypal IPN Path</h3>
            <p>Set the following IPN url in your paypal settings: <pre><?php echo $this->config->item('site_url') . 'page/ipn'; ?></pre></p>
            <p>Please take a look to the your documentation for more info.</p>
            <hr>
            <h3>Stripe webhook path</h3>
            <p><pre><?php echo $this->config->item('site_url') . 'page/stripe'; ?></pre></p>
            <p>NOTE: The premium plugin will automatically set this URL in your stripe account.</p>
        </div>
    </div>
</div>