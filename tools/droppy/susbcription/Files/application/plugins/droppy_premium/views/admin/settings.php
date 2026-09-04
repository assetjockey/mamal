<form class="form-horizontal style-form" method="post" action="<?php echo $this->config->item('site_url') ?>page/premium">
    <?php
    $clsPlans = new PremiumPlans();
    $plans = $clsPlans->getAll();
    if ((!empty($premium_settings['username_api']) || !empty($premium_settings['stripe_key'])) && ($plans === false || count($plans) == 0)) : ?>
        <div class="alert alert-danger" style="margin: 10px 0 20px 0;">
            <h2>No subscription plan created!</h2>
            It seems you haven't created a plan yet. Without a plan users aren't able to subscribe to your service, make sure the create a plan <a href="<?php echo $this->config->item('site_url') ?>admin/pluginpage/premium?p=plans">over here</a>
            <br>
        </div>
    <?php endif; ?>
    <?php
    $max_upload = min((int)ini_get('post_max_size'), (int)ini_get('upload_max_filesize'));

    if($max_upload < 60):
    ?>
        <div class="alert alert-danger" style="margin: 10px 0 20px 0;">
            <h2>Your max. upload size is low!</h2>
            Your current PHP settings may cause problems with users uploading custom backgrounds. By default the upload of custom backgrounds is set to max. <b>20 MB</b> but your current PHP settings only allow you to upload up to <b><?php echo $max_upload; ?> MB.</b><br>
            Please consider increasing your maximum upload size, more info can be found <a href="https://proxibolt.zendesk.com/hc/en-us/articles/10490131425938" target="_blank">over here</a>
            <br>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <div class="col">
                <h4 class="card-title">Payment gateway</h4>
            </div>
        </div>
        <div class="card-body">
            <input type="hidden" name="action" value="settings_general">
            <input type="hidden" name="goback" value="<?php echo current_url() ?>">

            <div class="mb-3">
                <label class="form-label">Payment gateway</label>
                <div class="col-sm-10">
                    <select class="form-control" name="payment_gateway">
                        <option value="paypal" <?php if($premium_settings['payment_gateway'] == 'paypal') { echo 'selected'; } ?>>Paypal</option>
                        <option value="stripe" <?php if($premium_settings['payment_gateway'] == 'stripe') { echo 'selected'; } ?>>Stripe</option>
                    </select>
                    <p><i>Select which payment gateway you would like to use</i></p>
                </div>
            </div>
            <div class="gateway-settings" id="paypal">
                <div class="mb-3">
                    <label class="form-label">Username (API Paypal)</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" name="username_api" placeholder="Password (API Paypal)" value="<?php echo $premium_settings['username_api']; ?>">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password (API Paypal)</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" name="password_api" placeholder="Password (API Paypal)" value="<?php echo $premium_settings['password_api']; ?>">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Signature (API PAYPAL)</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" name="signature_api" placeholder="Signature (Paypal API)" value="<?php echo $premium_settings['signature_api']; ?>">
                    </div>
                </div>
            </div>
            <div class="gateway-settings" id="stripe">
                <div class="mb-3">
                    <label class="form-label">Stripe API key</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" name="stripe_key" placeholder="Stripe API key" value="<?php echo $premium_settings['stripe_key']; ?>">
                    </div>
                </div>
            </div>

            <br>
            <button type="submit" class="btn btn-primary" ><i class="fa fa-floppy-o"></i>&nbsp;Save</button>
        </div>
    </div>

    <br>

    <div class="card">
        <div class="card-header">
            <div class="col">
                <h4 class="card-title">Product page</h4>
            </div>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label">Show VAT field<?php echo ($this->config->item('theme') == 'modern' ? '' : '<br><span style="font-size: 10px; color: red;">(Option disabled, current theme not supported</span>') ?></label>
                <div class="col-sm-10">
                    <select class="form-control" name="enable_vat" <?php echo ($this->config->item('theme') == 'modern' ? '' : 'disabled="disabled"') ?>>
                        <option value="true" <?php if($premium_settings['enable_vat'] == 'true') { echo 'selected'; } ?>>Show</option>
                        <option value="false" <?php if($premium_settings['enable_vat'] == 'false') { echo 'selected'; } ?>>Hide</option>
                    </select>
                    <p><i>Select if you want the VAT field to be shown.</i></p>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Show address fields<?php echo ($this->config->item('theme') == 'modern' ? '' : '<br><span style="font-size: 10px; color: red;">(Option disabled, current theme not supported</span>') ?></label>
                <div class="col-sm-10">
                    <select class="form-control" name="enable_address" <?php echo ($this->config->item('theme') == 'modern' ? '' : 'disabled="disabled"') ?>>
                        <option value="true" <?php if($premium_settings['enable_address'] == 'true') { echo 'selected'; } ?>>Show</option>
                        <option value="false" <?php if($premium_settings['enable_address'] == 'false') { echo 'selected'; } ?>>Hide</option>
                    </select>
                    <p><i>Select if you want to show the address fields in the subscription form</i></p>
                </div>
            </div>

            <br>
            <button type="submit" class="btn btn-primary" ><i class="fa fa-floppy-o"></i>&nbsp;Save</button>
        </div>
    </div>

    <br>

    <div class="card">
        <div class="card-header">
            <div class="col">
                <h4 class="card-title">Product details</h4>
            </div>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label">Enable sub-users<?php echo ($this->config->item('theme') == 'modern' ? '' : '<br><span style="font-size: 10px; color: red;">(Option disabled, current theme not supported</span>') ?></label>
                <div class="col-sm-10">
                    <select class="form-control" name="enable_multi_user" <?php echo ($this->config->item('theme') == 'modern' ? '' : 'disabled="disabled"') ?>>
                        <option value="true" <?php if($premium_settings['enable_multi_user'] == 'true') { echo 'selected'; } ?>>Yes</option>
                        <option value="false" <?php if($premium_settings['enable_multi_user'] == 'false') { echo 'selected'; } ?>>No</option>
                    </select>
                    <p><i>This options gives your subscription users the option to add additional logins to their subscription</i></p>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Paypal checkout logo</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" name="logo_paypal" placeholder="Your URL to your logo" value="<?php echo $premium_settings['logo_url']; ?>">
                    <p><i>Logo of your company/website that will be shown on the checkout page of paypal.</i></p>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Currency code</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" name="currency" placeholder="Currency code" value="<?php echo $premium_settings['currency']; ?>">
                    <p><i>Currrency code list <a href="https://docs.1010data.com/1010dataReferenceManual/DataTypesAndFormats/currencyUnitCodes.html">here</a></i></p>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Maximum fails</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" name="max_fails" placeholder="Max failed payments" value="<?php echo $premium_settings['max_fails']; ?>">
                    <p><i>Number of scheduled payments that can fail before the profile is automatically suspended. Set 0 to disable this feature (Subscription will continue even when payments are failed).</i></p>
                </div>
            </div>

            <br>
            <button type="submit" class="btn btn-primary" ><i class="fa fa-floppy-o"></i>&nbsp;Save</button>
        </div>
    </div>

    <br>

    <div class="card">
        <div class="card-header">
            <div class="col">
                <h4 class="card-title">Email messages</h4>
            </div>
        </div>
        <div class="card-body">
            <p>Email shortcodes can be found in the documentation</p>
            <div class="mb-3">
                <label class="form-label">Cancel subscription now subject</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" name="sub_cancel_n_subject" placeholder="Subject" value="<?php echo $premium_settings['sub_cancel_n_subject']; ?>">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Cancel subscription now text</label>
                <div class="col-sm-10">
                    <textarea name="sub_cancel_n_email" class="form-control" style="height: 150px;"><?php echo $premium_settings['sub_cancel_n_email']; ?></textarea>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Cancel subscription end subject</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" name="sub_cancel_e_subject" placeholder="Subject" value="<?php echo $premium_settings['sub_cancel_e_subject']; ?>">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Cancel subscription end email</label>
                <div class="col-sm-10">
                    <textarea name="sub_cancel_e_email" class="form-control" style="height: 150px;"><?php echo $premium_settings['sub_cancel_e_email']; ?></textarea>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">New subscription subject</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" name="new_sub_subject" placeholder="Subject" value="<?php echo $premium_settings['new_sub_subject']; ?>">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">New subscription email</label>
                <div class="col-sm-10">
                    <textarea name="new_sub_email" class="form-control" style="height: 150px;"><?php echo $premium_settings['new_sub_email']; ?></textarea>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Suspended sub subject</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" name="sus_email_sub" placeholder="Subject" value="<?php echo $premium_settings['sus_email_sub']; ?>">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Suspended sub email</label>
                <div class="col-sm-10">
                    <textarea name="sus_email" class="form-control" style="height: 150px;"><?php echo $premium_settings['sus_email']; ?></textarea>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Payment failed subject</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" name="payment_failed_sub" placeholder="Subject" value="<?php echo $premium_settings['payment_failed_sub']; ?>">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Payment failed email</label>
                <div class="col-sm-10">
                    <textarea name="payment_failed_email" class="form-control" style="height: 150px;"><?php echo $premium_settings['payment_failed_email']; ?></textarea>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Password forgot subject</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" name="forgot_pass_sub" placeholder="Subject" value="<?php echo $premium_settings['forgot_pass_subject']; ?>">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Password forgot email</label>
                <div class="col-sm-10">
                    <textarea name="forgot_pass_email" class="form-control" style="height: 150px;"><?php echo $premium_settings['forgot_pass_email']; ?></textarea>
                </div>
            </div>

            <br>
            <button type="submit" class="btn btn-primary" ><i class="fa fa-floppy-o"></i>&nbsp;Save</button>
        </div>
    </div>
</form>

<script>
    $( document ).ready(function() {
        $('.gateway-settings').hide();
        $('.gateway-settings#'+$('select[name="payment_gateway"]').val()).show();

        $('select[name="payment_gateway"]').on('change', function() {
            $('.gateway-settings').hide();
            $('.gateway-settings#'+$('select[name="payment_gateway"]').val()).show();
        });
    });
</script>