<?php

require_once dirname(__FILE__) . '/../../../../autoloader.php';

$clsSettings = new PremiumSettings();

$premium_settings = $clsSettings->getSettings();

?>

<script>
    "use strict"
    // Validating and submitting form data
    $(document).ready( function() {
        $('body').on('click', '#submitLogin', function() {
            var email   = $('#email').val();
            var pass    = $('#pwd').val();

            if(email == '' || email == null || pass == '' || pass == null)
            {
                document.getElementById('errors').innerHTML = '<div class="notification is-danger"><?php echo lang('premium_fill_fields'); ?></div>';
            }
            else
            {
                // Data that will be post to the php file to check if the email already exists in the database
                var dataString = 'action=login&email='+email+'&password='+pass;
                // Ajax post
                $.ajax({
                    type: "POST",
                    url: "<?php echo $this->config->item('site_url') ?>page/premium",
                    data: dataString,
                    success: function(return_data) {
                        console.log(return_data);
                        //When the email does not exists
                        if(return_data == 1)
                        {
                            window.location = '?goto=custom_account';
                        }
                        if(return_data == 2)
                        {
                            document.getElementById('errors').innerHTML = '<div class="notification is-danger"><?php echo lang('premium_account_suspended'); ?></div>';
                        }
                        if(return_data == 0)
                        {
                            document.getElementById('errors').innerHTML = '<div class="notification is-danger"><?php echo lang('invalid_login'); ?></div>';
                        }
                    }
                });
            }
        });

        $('body .loginDiv input').keypress(function(ev) {
            if (ev.keyCode === 13) {
                // Cancel the default action, if needed
                ev.preventDefault();
                // Trigger the button element with a click
                $('#submitLogin').click();
            }
        });

        $('body .resetDiv input').keypress(function(ev) {
            if (ev.keyCode === 13) {
                // Cancel the default action, if needed
                ev.preventDefault();
                // Trigger the button element with a click
                $('#submitReset').click();
            }
        });

        $('body .forgotDiv input').keypress(function(ev) {
            if (ev.keyCode === 13) {
                // Cancel the default action, if needed
                ev.preventDefault();
                // Trigger the button element with a click
                $('#submitForgot').click();
            }
        });

        $('body').on('click', '#submitForgot', function(ev) {
            ev.preventDefault();

            var email 	= $('#email_forgot').val();

            if(email == '' || email == null)
            {
                console.log('Email not entered');
                document.getElementById('errors').innerHTML = '<div class="notification is-danger"><?php echo lang('premium_fill_fields'); ?></div>';
            }
            else
            {
                console.log('Sending data..');

                // Data that will be post to the php file to check if the email already exists in the database
                var dataString = 'action=forgot&email='+email;
                // Ajax post
                $.ajax({
                    type: "POST",
                    url: "<?php echo $this->config->item('site_url') ?>page/premium",
                    data: dataString,
                    success: function(return_data) {
                        console.log('Got data back: ' + return_data);

                        //When the email does not exists
                        if(return_data == 1)
                        {
                            document.getElementById('email_forgot').value = '';
                            document.getElementById('errors').innerHTML = '<div class="notification is-success"><?php echo lang('premium_email_sent'); ?></div>';
                        }
                        if(return_data == 0)
                        {
                            document.getElementById('errors').innerHTML = '<div class="notification is-danger"><?php echo lang('premium_email_not_exists'); ?></div>';
                        }
                    }
                });
            }
        });
        $('body').on('click', '#submitReset', function(ev) {
            ev.preventDefault();

            var pass1 		= $('input[name="password1"]').val();
            var pass2 		= $('input[name="password2"]').val();
            var reset_code 	= $('#reset_code').val();

            if(pass1 == '' || pass1 == null || pass2 == '' || pass2 == null)
            {
                document.getElementById('errors').innerHTML = '<div class="notification is-danger"><?php echo lang('premium_fill_fields'); ?></div>';
            }
            else
            {
                if(pass1 == pass2) {
                    var dataString = 'action=reset_pass&pass1='+pass1+'&pass2='+pass2+'&reset='+reset_code;
                    $.ajax({
                        type: "POST",
                        url: "<?php echo $this->config->item('site_url') ?>page/premium",
                        data: dataString,
                        success: function(return_data) {
                            //When the email does not exists
                            if(return_data == 1)
                            {
                                window.location.href = "?goto=custom_account&msg=<?php echo lang('premium_pass_changed'); ?>";
                            }
                            if(return_data == 0)
                            {
                                document.getElementById('errors').innerHTML = '<div class="notification is-danger"><?php echo lang('premium_something_wrong'); ?></div>';
                            }
                        }
                    });
                }
                else
                {
                    document.getElementById('errors').innerHTML = '<div class="notification is-danger"><?php echo lang('premium_pass_same'); ?></div>';
                }
            }
        });
        $('body').on('click', '#submitDetailsChange', function(ev) {
            ev.preventDefault();

            var email 	= document.getElementById('email').value;
            var pass 	= document.getElementById('pwd').value;
            var name 	= document.getElementById('name').value;
            var subid	= document.getElementById('sub_id').value;

            if(email == '' || email == null || name == '' || name == null || subid == '' || subid == null)
            {
                document.getElementById('errorsOther').innerHTML = '<p style="color: red;"><?php echo lang('premium_fill_fields'); ?></p>';
            }
            else
            {
                // Data that will be post to the php file to check if the email already exists in the database
                var dataString = $(this).parents('form').serialize();
                // Ajax post
                $.ajax({
                    type: "POST",
                    url: "<?php echo $this->config->item('site_url') ?>page/premium",
                    data: dataString,
                    success: function(return_data) {
                        // When the email does not exists
                        if(return_data == 1)
                        {
                            document.getElementById('pwd').value = '';
                            document.getElementById('email-from').value = email;
                            document.getElementById('errorsOther').innerHTML = '<div class="notification is-success"><?php echo lang('premium_info_changed'); ?></div><br>';
                        }
                        if(return_data == 2)
                        {
                            document.getElementById('errorsOther').innerHTML = '<div class="notification is-danger"><?php echo lang('premium_email_exists'); ?></div><br>';
                        }
                        if(return_data == 3)
                        {
                            document.getElementById('errorsOther').innerHTML = '<div class="notification is-danger"><?php echo lang('premium_fill_fields'); ?></div><br>';
                        }
                    }
                });
            }
        });
        $('body').on('click', '#openForgot', function(ev) {
            ev.preventDefault();
            document.getElementById('loginDiv').style.display = 'none';
            document.getElementById('forgotDiv').style.display = 'block';
        });
        $('body').on('click', '#openLogin', function(ev) {
            ev.preventDefault();
            document.getElementById('loginDiv').style.display = 'block';
            document.getElementById('forgotDiv').style.display = 'none';
        });
        $('body').on('click', '.tab#tab-account .tabs a', function(ev) {
            var target = $(this).data('target');

            if(target == 'uploads') {
                updateUploadsList();
            }

            $('.tab#tab-account .tabs li').removeClass('is-active');
            $(this).parents('li').addClass('is-active');
            $('.tab#tab-account .account-tab').removeClass('active');
            $('.tab#tab-account .account-tab#'+target).addClass('active');
        });

        <?php echo (isset($_GET['tab']) ? '$("#tab-account .tabs a[data-target=\''.$_GET['tab'].'\'").click();' : '') ?>
    });

    function updateUploadsList() {
        setTimeout(function() {
            $.ajax({
                method: "GET",
                url: "<?php echo $this->config->item('site_url') ?>page/premium?action=uploads"
            })
            .done(function (html) {
                $('.account-tab#uploads').html(html);
            });
        }, 500);
    }
</script>
<?php
if(isset($_GET['error'])) :
    ?>
    <div id="errors"><div class="notification is-danger"><?php echo $_GET['error'] ?></div></div>
    <?php
elseif(isset($_GET['msg'])) :
    ?>
    <div id="errors"><div class="notification is-success"><?php echo $_GET['msg'] ?></div></div>
<?php
else:
    ?>
    <div id="errors"></div>
    <?php
endif;
if(isset($_SESSION['droppy_premium']) || (isset($_SESSION['droppy_premium_suspend']) && $_SESSION['droppy_premium_suspend'])) :
    if(isset($_SESSION['droppy_premium_suspend'])) {
        $pm_id = $_SESSION['droppy_premium_suspend'];
    }
    if(isset($_SESSION['droppy_premium'])) {
        $pm_id = $_SESSION['droppy_premium'];
    }

    $clsUser = new PremiumUser();
    $clsSubs = new PremiumSubs();

    $user = $clsUser->getByID($pm_id);

    if($user !== false) {
        $row = $clsSubs->getBySubID($user['sub_id']);

        if($row !== false) {
            $sub_id 		= $row['sub_id'];
            $name 			= $row['name'];
            $company 		= $row['company'];
            $payment 		= $row['payment'];
            $paypal_id 		= $row['paypal_id'];
            $sub_status		= $row['status'];
            $next_payment 	= $row['next_date'];
            $email          = $row['email'];
        }
    }

    ?>
    <?php
    if($sub_status == 'suspended') :
        ?>
        <p><?php echo lang('premium_hi') . ' ' . $name; ?></p>
        <hr>
        <h4><?php echo lang('premium_your_sub_details'); ?>:</h4>
        <table class="table" style="width: 100%;">
            <tr>
                <td><strong><?php echo lang('premium_account_status'); ?>:</strong></td>
                <td id="table_value"><?php echo lang($sub_status); ?></td>
            </tr>
            <tr>
                <td><strong><?php echo lang('premium_cancel_sub'); ?>:</strong></td>
                <td id="table_value"><a class="btn btn-default btn-xs" href="<?php echo $this->config->item('site_url') ?>page/premium?action=cancel&id=<?php echo $sub_id; ?>"><?php echo lang('premium_cancel'); ?></a></td>
            </tr>
        </table>
        <p style="font-size: 12px;"><?php echo lang('premium_cancel_sus_text'); ?></p>
        <?php
    elseif($sub_status == 'canceled' || $sub_status == 'validating') :
        ?>
        <h4><?php echo lang('premium_no_sub_found'); ?></h4>
        <p><?php echo lang('premium_no_subscription'); ?></p><br>
        <a class="button is-info is-fullwidth" href="#" onclick="Tabs.open('tab-gopremium');return 0;"><?php echo lang('premium_create_new_sub'); ?></a>
        <?php
    elseif($sub_status == 'active' || $sub_status == 'canceled_end') :
        ?>
        <div class="tabs core is-medium is-fullwidth is-boxed is-light">
            <ul>
                <li class="is-active"><a data-target="uploads"><?php echo lang('premium_tab_my_uploads') ?></a></li>
                <?php if(empty($user['parent_id'])): ?><li><a data-target="backgrounds"><?php echo lang('premium_tab_my_backgrounds') ?></a></li><?php endif; ?>
                <?php if($premium_settings['enable_multi_user'] == 'true' && empty($user['parent_id'])): ?><li><a data-target="users"><?php echo lang('premium_sub_users') ?></a></li><?php endif; ?>
                <li><a data-target="account"><?php echo lang('premium_tab_my_account') ?></a></li>
            </ul>
        </div>
        <div class="account-tab active" id="uploads">
            <?php require_once dirname(__FILE__) . '/uploads.php'; ?>
        </div>
        <?php if(empty($user['parent_id'])): ?>
        <div class="account-tab" id="backgrounds">
            <?php require_once dirname(__FILE__) . '/backgrounds.php'; ?>
        </div>
        <?php endif; ?>
        <?php if($premium_settings['enable_multi_user'] == 'true'): ?>
            <div class="account-tab" id="users">
                <?php require_once dirname(__FILE__) . '/users.php'; ?>
            </div>
        <?php endif; ?>
        <div class="account-tab" id="account">
            <?php if(empty($user['parent_id'])): ?>
            <p><?php echo lang('premium_hi') . ' ' . $name; ?></p>
            <br>
            <h4><?php echo lang('premium_your_sub_details'); ?>:</h4>
            <table class="table" style="width: 100%;">
                <tr>
                    <td><strong><?php echo lang('premium_account_status'); ?>:</strong></td>
                    <td id="table_value"><?php echo lang($sub_status); ?></td>
                </tr>
                <tr>
                    <td><strong><?php echo lang('premium_payment_type'); ?>:</strong></td>
                    <td id="table_value"><?php echo $payment; ?></td>
                </tr>
                <tr>
                    <td><strong>Paypal ID:</strong></td>
                    <td id="table_value"><?php echo $paypal_id; ?></td>
                </tr>
                <tr>
                    <td><strong><?php echo lang('premium_next_pay_date'); ?>:</strong></td>
                    <td id="table_value"><?php echo (!empty($next_payment) ? date("Y-m-d", $next_payment) : 'Unkown'); ?></td>
                </tr>
                <?php
                if($sub_status == 'canceled_end') :
                    ?>
                    <tr>
                        <td><strong><?php echo lang('premium_cancel_sub'); ?>:</strong></td>
                        <td id="table_value"><?php echo lang('premium_canceled_on_date') . ' ' . (!empty($next_payment) ? date("Y-m-d", $next_payment) : 'Unkown'); ?></td>
                    </tr>
                    <?php
                else:
                    ?>
                    <tr>
                        <td><strong><?php echo lang('premium_cancel_sub'); ?>:</strong></td>
                        <td id="table_value"><a class="btn btn-default btn-xs" href="<?php echo $this->config->item('site_url') ?>page/premium?action=cancel&type=end&id=<?php echo $sub_id; ?>"><?php echo lang('premium_cancel'); ?></a></td>
                    </tr>
                    <?php
                endif;
                ?>
            </table>
            <br>
            <h2><?php echo lang('premium_other_details'); ?>:</h2>
            <div id="errorsOther"></div>
            <form role="form">
                <input type="hidden" name="action" value="change_details">
                <input type="hidden" id="sub_id" name="sub_id" value="<?php echo $sub_id; ?>">

                <div class="field">
                    <label class="label"><?php echo lang('email') ?></label>
                    <div class="control">
                        <input class="input" type="email" name="email" id="email" placeholder="<?php echo lang('enter_own_email') ?>" value="<?php echo $email; ?>">
                    </div>
                </div>
                <div class="field">
                    <label class="label"><?php echo lang('password') ?></label>
                    <div class="control">
                        <input class="input" type="password" id="pwd" name="password" placeholder="<?php echo lang('password') ?>" autocomplete="no" autofill="no">
                    </div>
                </div>
                <div class="field">
                    <label class="label"><?php echo lang('premium_fullname') ?></label>
                    <div class="control">
                        <input class="input" type="text" id="name" name="name" placeholder="<?php echo lang('premium_fullname') ?>" value="<?php echo $name; ?>">
                    </div>
                </div>
                <div class="field">
                    <label class="label"><?php echo lang('premium_company') ?></label>
                    <div class="control">
                        <input class="input" type="text" id="company" name="company" placeholder="<?php echo lang('premium_company') ?>" value="<?php echo $company; ?>">
                    </div>
                </div>

                <?php if($premium_settings['enable_vat'] == 'true'): ?>
                <div class="field">
                    <label class="label"><?php echo lang('premium_vat') ?></label>
                    <div class="control">
                        <input class="input" type="text" id="vat" name="vat_number" placeholder="<?php echo lang('premium_vat') ?>" value="<?php echo ($row['vat_number'] ?? ''); ?>">
                    </div>
                </div>
                <?php endif; ?>

                <?php if($premium_settings['enable_address'] == 'true'): ?>
                    <div class="field">
                        <label class="label"><?php echo lang('premium_address_street') ?></label>
                        <div class="control">
                            <input class="input" type="text" id="address_street" name="address_street" placeholder="<?php echo lang('premium_address_street') ?>" value="<?php echo ($row['address_street'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="field">
                        <label class="label"><?php echo lang('premium_address_zip') ?></label>
                        <div class="control">
                            <input class="input" type="text" id="address_zip" name="address_zip" placeholder="<?php echo lang('premium_address_zip') ?>" value="<?php echo ($row['address_zip'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="field">
                        <label class="label"><?php echo lang('premium_address_city') ?></label>
                        <div class="control">
                            <input class="input" type="text" id="address_city" name="address_city" placeholder="<?php echo lang('premium_address_city') ?>" value="<?php echo ($row['address_city'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="field">
                        <label class="label"><?php echo lang('premium_address_country') ?></label>
                        <div class="control">
                            <select class="input" id="address_country" name="address_country" placeholder="<?php echo lang('premium_address_country'); ?>"> <option value="Afganistan">Afghanistan</option> <option value="Albania">Albania</option> <option value="Algeria">Algeria</option> <option value="American Samoa">American Samoa</option> <option value="Andorra">Andorra</option> <option value="Angola">Angola</option> <option value="Anguilla">Anguilla</option> <option value="Antigua & Barbuda">Antigua & Barbuda</option> <option value="Argentina">Argentina</option> <option value="Armenia">Armenia</option> <option value="Aruba">Aruba</option> <option value="Australia">Australia</option> <option value="Austria">Austria</option> <option value="Azerbaijan">Azerbaijan</option> <option value="Bahamas">Bahamas</option> <option value="Bahrain">Bahrain</option> <option value="Bangladesh">Bangladesh</option> <option value="Barbados">Barbados</option> <option value="Belarus">Belarus</option> <option value="Belgium">Belgium</option> <option value="Belize">Belize</option> <option value="Benin">Benin</option> <option value="Bermuda">Bermuda</option> <option value="Bhutan">Bhutan</option> <option value="Bolivia">Bolivia</option> <option value="Bonaire">Bonaire</option> <option value="Bosnia & Herzegovina">Bosnia & Herzegovina</option> <option value="Botswana">Botswana</option> <option value="Brazil">Brazil</option> <option value="British Indian Ocean Ter">British Indian Ocean Ter</option> <option value="Brunei">Brunei</option> <option value="Bulgaria">Bulgaria</option> <option value="Burkina Faso">Burkina Faso</option> <option value="Burundi">Burundi</option> <option value="Cambodia">Cambodia</option> <option value="Cameroon">Cameroon</option> <option value="Canada">Canada</option> <option value="Canary Islands">Canary Islands</option> <option value="Cape Verde">Cape Verde</option> <option value="Cayman Islands">Cayman Islands</option> <option value="Central African Republic">Central African Republic</option> <option value="Chad">Chad</option> <option value="Channel Islands">Channel Islands</option> <option value="Chile">Chile</option> <option value="China">China</option> <option value="Christmas Island">Christmas Island</option> <option value="Cocos Island">Cocos Island</option> <option value="Colombia">Colombia</option> <option value="Comoros">Comoros</option> <option value="Congo">Congo</option> <option value="Cook Islands">Cook Islands</option> <option value="Costa Rica">Costa Rica</option> <option value="Cote DIvoire">Cote DIvoire</option> <option value="Croatia">Croatia</option> <option value="Cuba">Cuba</option> <option value="Curaco">Curacao</option> <option value="Cyprus">Cyprus</option> <option value="Czech Republic">Czech Republic</option> <option value="Denmark">Denmark</option> <option value="Djibouti">Djibouti</option> <option value="Dominica">Dominica</option> <option value="Dominican Republic">Dominican Republic</option> <option value="East Timor">East Timor</option> <option value="Ecuador">Ecuador</option> <option value="Egypt">Egypt</option> <option value="El Salvador">El Salvador</option> <option value="Equatorial Guinea">Equatorial Guinea</option> <option value="Eritrea">Eritrea</option> <option value="Estonia">Estonia</option> <option value="Ethiopia">Ethiopia</option> <option value="Falkland Islands">Falkland Islands</option> <option value="Faroe Islands">Faroe Islands</option> <option value="Fiji">Fiji</option> <option value="Finland">Finland</option> <option value="France">France</option> <option value="French Guiana">French Guiana</option> <option value="French Polynesia">French Polynesia</option> <option value="French Southern Ter">French Southern Ter</option> <option value="Gabon">Gabon</option> <option value="Gambia">Gambia</option> <option value="Georgia">Georgia</option> <option value="Germany">Germany</option> <option value="Ghana">Ghana</option> <option value="Gibraltar">Gibraltar</option> <option value="Great Britain">Great Britain</option> <option value="Greece">Greece</option> <option value="Greenland">Greenland</option> <option value="Grenada">Grenada</option> <option value="Guadeloupe">Guadeloupe</option> <option value="Guam">Guam</option> <option value="Guatemala">Guatemala</option> <option value="Guinea">Guinea</option> <option value="Guyana">Guyana</option> <option value="Haiti">Haiti</option> <option value="Hawaii">Hawaii</option> <option value="Honduras">Honduras</option> <option value="Hong Kong">Hong Kong</option> <option value="Hungary">Hungary</option> <option value="Iceland">Iceland</option> <option value="Indonesia">Indonesia</option> <option value="India">India</option> <option value="Iran">Iran</option> <option value="Iraq">Iraq</option> <option value="Ireland">Ireland</option> <option value="Isle of Man">Isle of Man</option> <option value="Israel">Israel</option> <option value="Italy">Italy</option> <option value="Jamaica">Jamaica</option> <option value="Japan">Japan</option> <option value="Jordan">Jordan</option> <option value="Kazakhstan">Kazakhstan</option> <option value="Kenya">Kenya</option> <option value="Kiribati">Kiribati</option> <option value="Korea North">Korea North</option> <option value="Korea Sout">Korea South</option> <option value="Kuwait">Kuwait</option> <option value="Kyrgyzstan">Kyrgyzstan</option> <option value="Laos">Laos</option> <option value="Latvia">Latvia</option> <option value="Lebanon">Lebanon</option> <option value="Lesotho">Lesotho</option> <option value="Liberia">Liberia</option> <option value="Libya">Libya</option> <option value="Liechtenstein">Liechtenstein</option> <option value="Lithuania">Lithuania</option> <option value="Luxembourg">Luxembourg</option> <option value="Macau">Macau</option> <option value="Macedonia">Macedonia</option> <option value="Madagascar">Madagascar</option> <option value="Malaysia">Malaysia</option> <option value="Malawi">Malawi</option> <option value="Maldives">Maldives</option> <option value="Mali">Mali</option> <option value="Malta">Malta</option> <option value="Marshall Islands">Marshall Islands</option> <option value="Martinique">Martinique</option> <option value="Mauritania">Mauritania</option> <option value="Mauritius">Mauritius</option> <option value="Mayotte">Mayotte</option> <option value="Mexico">Mexico</option> <option value="Midway Islands">Midway Islands</option> <option value="Moldova">Moldova</option> <option value="Monaco">Monaco</option> <option value="Mongolia">Mongolia</option> <option value="Montserrat">Montserrat</option> <option value="Morocco">Morocco</option> <option value="Mozambique">Mozambique</option> <option value="Myanmar">Myanmar</option> <option value="Nambia">Nambia</option> <option value="Nauru">Nauru</option> <option value="Nepal">Nepal</option> <option value="Netherland Antilles">Netherland Antilles</option> <option value="Netherlands">Netherlands (Holland, Europe)</option> <option value="Nevis">Nevis</option> <option value="New Caledonia">New Caledonia</option> <option value="New Zealand">New Zealand</option> <option value="Nicaragua">Nicaragua</option> <option value="Niger">Niger</option> <option value="Nigeria">Nigeria</option> <option value="Niue">Niue</option> <option value="Norfolk Island">Norfolk Island</option> <option value="Norway">Norway</option> <option value="Oman">Oman</option> <option value="Pakistan">Pakistan</option> <option value="Palau Island">Palau Island</option> <option value="Palestine">Palestine</option> <option value="Panama">Panama</option> <option value="Papua New Guinea">Papua New Guinea</option> <option value="Paraguay">Paraguay</option> <option value="Peru">Peru</option> <option value="Phillipines">Philippines</option> <option value="Pitcairn Island">Pitcairn Island</option> <option value="Poland">Poland</option> <option value="Portugal">Portugal</option> <option value="Puerto Rico">Puerto Rico</option> <option value="Qatar">Qatar</option> <option value="Republic of Montenegro">Republic of Montenegro</option> <option value="Republic of Serbia">Republic of Serbia</option> <option value="Reunion">Reunion</option> <option value="Romania">Romania</option> <option value="Russia">Russia</option> <option value="Rwanda">Rwanda</option> <option value="St Barthelemy">St Barthelemy</option> <option value="St Eustatius">St Eustatius</option> <option value="St Helena">St Helena</option> <option value="St Kitts-Nevis">St Kitts-Nevis</option> <option value="St Lucia">St Lucia</option> <option value="St Maarten">St Maarten</option> <option value="St Pierre & Miquelon">St Pierre & Miquelon</option> <option value="St Vincent & Grenadines">St Vincent & Grenadines</option> <option value="Saipan">Saipan</option> <option value="Samoa">Samoa</option> <option value="Samoa American">Samoa American</option> <option value="San Marino">San Marino</option> <option value="Sao Tome & Principe">Sao Tome & Principe</option> <option value="Saudi Arabia">Saudi Arabia</option> <option value="Senegal">Senegal</option> <option value="Seychelles">Seychelles</option> <option value="Sierra Leone">Sierra Leone</option> <option value="Singapore">Singapore</option> <option value="Slovakia">Slovakia</option> <option value="Slovenia">Slovenia</option> <option value="Solomon Islands">Solomon Islands</option> <option value="Somalia">Somalia</option> <option value="South Africa">South Africa</option> <option value="Spain">Spain</option> <option value="Sri Lanka">Sri Lanka</option> <option value="Sudan">Sudan</option> <option value="Suriname">Suriname</option> <option value="Swaziland">Swaziland</option> <option value="Sweden">Sweden</option> <option value="Switzerland">Switzerland</option> <option value="Syria">Syria</option> <option value="Tahiti">Tahiti</option> <option value="Taiwan">Taiwan</option> <option value="Tajikistan">Tajikistan</option> <option value="Tanzania">Tanzania</option> <option value="Thailand">Thailand</option> <option value="Togo">Togo</option> <option value="Tokelau">Tokelau</option> <option value="Tonga">Tonga</option> <option value="Trinidad & Tobago">Trinidad & Tobago</option> <option value="Tunisia">Tunisia</option> <option value="Turkey">Turkey</option> <option value="Turkmenistan">Turkmenistan</option> <option value="Turks & Caicos Is">Turks & Caicos Is</option> <option value="Tuvalu">Tuvalu</option> <option value="Uganda">Uganda</option> <option value="United Kingdom">United Kingdom</option> <option value="Ukraine">Ukraine</option> <option value="United Arab Erimates">United Arab Emirates</option> <option value="United States of America">United States of America</option> <option value="Uraguay">Uruguay</option> <option value="Uzbekistan">Uzbekistan</option> <option value="Vanuatu">Vanuatu</option> <option value="Vatican City State">Vatican City State</option> <option value="Venezuela">Venezuela</option> <option value="Vietnam">Vietnam</option> <option value="Virgin Islands (Brit)">Virgin Islands (Brit)</option> <option value="Virgin Islands (USA)">Virgin Islands (USA)</option> <option value="Wake Island">Wake Island</option> <option value="Wallis & Futana Is">Wallis & Futana Is</option> <option value="Yemen">Yemen</option> <option value="Zaire">Zaire</option> <option value="Zambia">Zambia</option> <option value="Zimbabwe">Zimbabwe</option> </select>
                        </div>
                        <script>$("select[name='address_country']").val("<?php echo ($row['address_country'] ?? ''); ?>");</script>
                    </div>
                <?php endif; ?>

                <div class="field has-text-right">
                    <p class="control">
                        <button type="button" id="submitDetailsChange" class="button is-info is-fullwidth"><?php echo lang('save') ?></button>
                    </p>
                </div>
            </form>
            <?php else: ?>
                <p><?php echo lang('premium_no_parent'); ?></p>
            <?php endif; ?>
        </div>

        <?php
    endif;
else:
    ?>
    <?php
    if(isset($_GET['reset'])) :
        echo '<script>$( document ).ready(function() { Tabs.open("tab-account"); });</script>';
        ?>
        <div class="loginDiv resetDiv" id="loginDiv">
            <h1><?php echo lang('premium_reset_your_password') ?></h1>
            <form role="form">
                <input type="hidden" name="reset_code" id="reset_code" value="<?php echo $_GET['reset']; ?>">
                <div class="field">
                    <label class="label"><?php echo lang('password') ?></label>
                    <div class="control">
                        <input class="input" type="password" name="password1" placeholder="<?php echo lang('password') ?>">
                    </div>
                </div>
                <div class="field">
                    <label class="label"><?php echo lang('premium_password_re') ?></label>
                    <div class="control">
                        <input class="input" type="password" name="password2" placeholder="<?php echo lang('premium_password_re') ?>">
                    </div>
                </div>
                <div class="field has-text-right">
                    <p class="control">
                        <button type="button" id="submitReset" class="button is-info is-fullwidth"><?php echo lang('premium_submit') ?></button>
                    </p>
                </div>
            </form>
        </div>
        <?php
    else:
        ?>
        <div class="loginDiv" id="loginDiv">
            <h1><?php echo lang('premium_login_to_your_account') ?></h1>
            <div class="field">
                <label class="label"><?php echo lang('email') ?></label>
                <div class="control">
                    <input class="input" type="email" name="email" id="email" placeholder="<?php echo lang('email') ?>">
                </div>
            </div>
            <div class="field">
                <label class="label"><?php echo lang('password') ?></label>
                <div class="control">
                    <input class="input" type="password" name="password" id="pwd" placeholder="<?php echo lang('password') ?>">
                </div>
            </div>
            <a href="#" id="openForgot" class="openForm" style="float: right;"><?php echo lang('premium_forgot_pass'); ?></a>
            <br><br>
            <div class="field has-text-right">
                <p class="control">
                    <button type="button" id="submitLogin" class="button is-info is-fullwidth"><?php echo lang('login') ?></button>
                </p>
            </div>
        </div>
        <div class="forgotDiv" id="forgotDiv" style="min-height: 200px; display: none;">
            <h1><?php echo lang('premium_forgot_pass') ?></h1>
            <form role="form">
                <div class="field">
                    <label class="label"><?php echo lang('email') ?></label>
                    <div class="control">
                        <input class="input" type="email" name="email" id="email_forgot" placeholder="<?php echo lang('enter_own_email') ?>">
                    </div>
                </div>
                <a href="#" id="openLogin" class="openForm" style="float: right;"><?php echo lang('premium_login_page'); ?></a>
                <br><br>
                <div class="field has-text-right">
                    <p class="control">
                        <button type="button" id="submitForgot" class="button is-info is-fullwidth"><?php echo lang('premium_submit') ?></button>
                    </p>
                </div>
            </form>
        </div>
        <?php
    endif;
endif;