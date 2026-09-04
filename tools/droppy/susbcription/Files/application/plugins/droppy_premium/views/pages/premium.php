<?php

require_once dirname(__FILE__) . '/../../autoloader.php';

$clsSettings = new PremiumSettings();
$clsPlans = new PremiumPlans();

$premium_settings = $clsSettings->getSettings();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Set base url -->
    <base href="<?php echo base_url() ?>" target="_parent">

    <title><?php echo $this->config->item('site_title'); ?> - <?php echo lang('premium_go_pro'); ?></title>

    <link rel="shortcut icon" type="image/png" href="<?php echo $this->config->item('favicon_path') ?>"/>

    <!-- Icons -->
    <link href="//cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <!-- Fonts -->
    <link rel="stylesheet" href="//fonts.googleapis.com/css?family=Open+Sans:300,400,700">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.1/css/bulma.min.css" />
    <link rel="stylesheet" href="assets/plugins/droppy_premium/css/product-page.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma-social@2/css/all.min.css" />

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma-carousel@4.0.3/dist/css/bulma-carousel.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/bulma-carousel@4.0.3/dist/js/bulma-carousel.min.js"></script>

    <!--[if lt IE 9]><script src="assets/plugins/droppy_premium/js/html5shiv.js"></script><![endif]-->
</head>

<body>
<?php
if(isset($_GET['payment'])) {
    if($_GET['payment'] == 'canceled_user') {
        echo '<div class="notification is-warning" role="alert" style="text-align: center; margin-bottom: 0;">' . lang('premium_canceled_payment') . '</div>';
    }
    if($_GET['payment'] == 'pending') {
        echo '<div class="notification is-warning" role="alert" style="text-align: center; margin-bottom: 0;">' . lang('premium_pending_payment') . '</div>';
    }
    if($_GET['payment'] == 'reverse') {
        echo '<div class="notification is-warning" role="alert" style="text-align: center; margin-bottom: 0;">' . lang('premium_reverse_payment') . '</div>';
    }
    if($_GET['payment'] == 'created' || $_GET['payment'] == 'payment_confirm') {
        echo '<div class="notification is-success" role="alert" style="text-align: center; margin-bottom: 0;">' . lang('premium_received_payment') . '</div>';
    }
}
?>

<section class="section">
    <div class="container">
        <div class="has-text-centered" id="services-text-container">
            <h1 class="title is-1"><?php echo lang('premium_register'); ?></h1>
            <br>
            <h4 class="subtitle">
                <?php echo lang('premium_register_intro'); ?>
            </h4>
            <br>
        </div>
        <div id="package-errors"></div>
        <br />
        <div class="columns" id="package-section">
            <?php
            $plans = $clsPlans->getAllActive();

            foreach ($plans as $plan):
            ?>

                <div class="column">
                    <div class="card <?php echo (count($plans) == 1 ? 'active' : '') ?>">
                        <div class="card-content" style="text-align: center;">
                            <div class="has-text-centered" style="width: 100px; margin: 0 auto;">
                                <svg version="1.1" id="Warstwa_3" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 430 430" style="enable-background:new 0 0 430 430;" xml:space="preserve"> <style type="text/css"> .st0{fill:#156fd0;} .st1{fill: #000000;} .st2{fill: #156fd0;fill-opacity:0;} .st3{fill:none;} </style> <g> <path class="st1" d="M352.53,361.39H77.37c-3.79,0-6.87-3.07-6.87-6.87v-34.88c0-3.79,3.07-6.87,6.87-6.87h275.16 c3.79,0,6.87,3.07,6.87,6.87v34.88C359.4,358.31,356.33,361.39,352.53,361.39z M84.24,347.65h261.43v-21.15H84.24V347.65z"/> <path class="st0" d="M212.77,249c-1.76,0-3.51-0.67-4.86-2.01l-34.88-34.88c-2.68-2.68-2.68-7.03,0-9.71l34.88-34.88 c2.68-2.68,7.03-2.68,9.71,0l34.88,34.88c2.68,2.68,2.68,7.03,0,9.71l-34.88,34.88C216.29,248.33,214.53,249,212.77,249z M187.6,207.25l25.17,25.17l25.17-25.17l-25.17-25.17L187.6,207.25z"/> <path class="st1" d="M354.47,294.57H75.43c-4.68,0-8.7-3.3-9.62-7.89l-34.88-174.4c-0.81-4.04,1-8.16,4.52-10.3 c3.52-2.14,8.01-1.86,11.23,0.72l79.69,63.75l81.04-97.25c1.86-2.24,4.63-3.53,7.54-3.53s5.67,1.29,7.54,3.53l81.04,97.25 l79.69-63.75c3.47-2.78,8.38-2.87,11.94-0.24c3.09,2.21,4.67,6.07,3.9,9.9l-34.97,174.33 C363.17,291.27,359.15,294.57,354.47,294.57z M83.47,274.95h262.96l28.13-140.19l-66.28,53.02c-4.17,3.34-10.25,2.72-13.66-1.38 l-79.66-95.6l-79.66,95.6c-3.42,4.1-9.5,4.72-13.66,1.38l-66.17-52.94L83.47,274.95z"/> </g> </svg>
                            </div>
                            <h3 class="title is-5 has-text-centered" id="card-product-price"><?php echo $plan['plan_price'] . ' ' . $premium_settings['currency'] . ' / ' . ($plan['plan_freq'] > 1 && $plan['plan_freq'] < 12 ? $plan['plan_freq'] . ' ' : '') . ucfirst(lang((strtolower($plan['plan_time']) == 'month' && $plan['plan_freq'] == 12 ? 'year' : strtolower($plan['plan_time'])))) ?></h3>
                            <h3 class="title is-3 has-text-centered" id="card-product-description"><?php echo $plan['plan_name'] ?></h3>
                            <div class="card-product-features">
                                <?php echo $plan['plan_features'] ?>
                            </div>
                            <button class="button is-medium button is-link is-blue" data-id="<?php echo $plan['id'] ?>">
                                <?php echo (count($plans) == 1 ? lang('premium_selected') : lang('premium_select_package')) ?>
                            </button>
                        </div>
                    </div>
                </div>

            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php
$pm_id = false;
if(isset($_SESSION['droppy_premium_suspend'])) {
    $pm_id = $_SESSION['droppy_premium_suspend'];
}
if(isset($_SESSION['droppy_premium'])) {
    $pm_id = $_SESSION['droppy_premium'];
}

$clsUser = new PremiumUser();

$get_info = $clsUser->getByID($pm_id);

if($get_info !== false):
?>
    <div class="container">
        <div class="columns">
            <div class="column">
                <div class="card">
                    <div class="card-content">
                        <div class="registerDiv">
                            <form role="form" method="POST" id="addSubFrom" class="registerForm">
                                <input type="hidden" name="action" value="add_sub">
                                <input type="hidden" name="rd" value="<?php echo base_url(); ?>">
                                <input type="hidden" name="sub_id" value="<?php echo $get_info['sub_id']; ?>">
                                <input type="hidden" name="package" value="<?php echo (count($plans) == 1 ? $plans[0]['id'] : '') ?>">
                                <div class="form-group">
                                    <div class="field">
                                        <label class="label"><?php echo lang('premium_your_email'); ?></label>
                                        <div class="control">
                                            <input type="text" class="input registerInput" name="email" id="email" placeholder="<?php echo lang('premium_your_email'); ?>" readonly="readonly" value="<?php echo $get_info['email']; ?>">
                                        </div>
                                    </div>

                                    <div class="field">
                                        <label class="label"><?php echo lang('premium_fullname'); ?></label>
                                        <div class="control">
                                            <input type="text" class="input registerInput" name="name" id="name" placeholder="<?php echo lang('premium_fullname'); ?>" required="required">
                                        </div>
                                    </div>

                                    <div class="field">
                                        <label class="label"><?php echo lang('premium_company'); ?></label>
                                        <div class="control">
                                            <input type="text" class="input registerInput" name="company" id="company" placeholder="<?php echo lang('premium_company'); ?>">
                                        </div>
                                    </div>

                                    <hr>

                                    <div class="field">
                                        <label class="label"><?php echo lang('premium_voucher'); ?></label>
                                        <div class="control">
                                            <input type="text" class="input registerInput" name="voucher" id="voucher" placeholder="<?php echo lang('premium_voucher'); ?>">
                                        </div>
                                    </div>

                                    <br>

                                    <div class="termsAgree">
                                        <div class="field">
                                            <div class="control">
                                                <input type="checkbox" name="terms" id="terms" value="true" required="required"> <?php echo lang('premium_agree_terms'); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <br>
                                <div id="errors"></div>
                                <br>
                                <div id="paymentFooter">
                                    <?php if($premium_settings['payment_gateway'] == 'paypal'): ?>
                                        <img src="assets/plugins/droppy_premium/images/paypal.png" alt="paypal" style="float: left; width: 160px;">
                                    <?php elseif($premium_settings['payment_gateway'] == 'stripe'): ?>
                                        <img src="assets/plugins/droppy_premium/images/stripe.png" alt="stripe" style="float: left; width: 110px;">
                                    <?php endif; ?>
                                    <input type="submit" class="button is-medium button is-link is-blue" style="float: right;" value="<?php echo lang('premium_checkout'); ?>">
                                </div>
                                <br>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php
else:
?>
    <div class="container">
        <div class="columns">
            <div class="column">
                <div class="card">
                    <div class="card-content">
                        <div class="registerDiv">
                            <form role="form" method="POST" id="paymentForm" class="registerForm">
                                <input type="hidden" name="action" value="register">
                                <input type="hidden" name="package" value="<?php echo (count($plans) == 1 ? $plans[0]['id'] : '') ?>">
                                <input type="hidden" name="rd" value="<?php echo base_url(); ?>">
                                <div class="form-group">
                                    <div class="field">
                                        <label class="label"><?php echo lang('premium_your_email'); ?></label>
                                        <div class="control">
                                            <input type="text" class="input registerInput" name="email" id="email" placeholder="<?php echo lang('premium_your_email'); ?>" required="required">
                                        </div>
                                    </div>

                                    <div class="field">
                                        <label class="label"><?php echo lang('premium_password'); ?></label>
                                        <div class="control">
                                            <input type="password" class="input registerInput" name="password" id="password" placeholder="<?php echo lang('premium_password'); ?>" required="required">
                                        </div>
                                    </div>

                                    <div class="field">
                                        <label class="label"><?php echo lang('premium_password_re'); ?></label>
                                        <div class="control">
                                            <input type="password" class="input registerInput" name="re_password" id="re_password" placeholder="<?php echo lang('premium_password_re'); ?>" required="required">
                                        </div>
                                    </div>

                                    <hr>

                                    <div class="field">
                                        <label class="label"><?php echo lang('premium_fullname'); ?></label>
                                        <div class="control">
                                            <input type="text" class="input registerInput" name="name" id="name" placeholder="<?php echo lang('premium_fullname'); ?>" required="required">
                                        </div>
                                    </div>

                                    <div class="field">
                                        <label class="label"><?php echo lang('premium_company'); ?></label>
                                        <div class="control">
                                            <input type="text" class="input registerInput" name="company" id="company" placeholder="<?php echo lang('premium_company'); ?>">
                                        </div>
                                    </div>

                                    <?php if($premium_settings['enable_vat'] == 'true'): ?>
                                    <div class="field">
                                        <label class="label"><?php echo lang('premium_vat'); ?></label>
                                        <div class="control">
                                            <input type="text" class="input registerInput" name="vat" id="vat" placeholder="<?php echo lang('premium_vat'); ?>">
                                        </div>
                                    </div>
                                    <?php endif; ?>

                                    <?php if($premium_settings['enable_address'] == 'true'): ?>
                                    <div class="field">
                                        <label class="label"><?php echo lang('premium_address_street'); ?></label>
                                        <div class="control">
                                            <input type="text" class="input registerInput" name="address_street" id="address_street" placeholder="<?php echo lang('premium_address_street'); ?>" required="required">
                                        </div>
                                    </div>

                                    <div class="field">
                                        <label class="label"><?php echo lang('premium_address_zip'); ?></label>
                                        <div class="control">
                                            <input type="text" class="input registerInput" name="address_zip" id="address_zip" placeholder="<?php echo lang('premium_address_zip'); ?>" required="required">
                                        </div>
                                    </div>

                                    <div class="field">
                                        <label class="label"><?php echo lang('premium_address_city'); ?></label>
                                        <div class="control">
                                            <input type="text" class="input registerInput" name="address_city" id="address_city" placeholder="<?php echo lang('premium_address_city'); ?>" required="required">
                                        </div>
                                    </div>

                                    <div class="field">
                                        <label class="label"><?php echo lang('premium_address_country'); ?></label>
                                        <div class="control">
                                            <select class="input registerInput" id="address_country" name="address_country" placeholder="<?php echo lang('premium_address_country'); ?>"> <option value="Afganistan">Afghanistan</option> <option value="Albania">Albania</option> <option value="Algeria">Algeria</option> <option value="American Samoa">American Samoa</option> <option value="Andorra">Andorra</option> <option value="Angola">Angola</option> <option value="Anguilla">Anguilla</option> <option value="Antigua & Barbuda">Antigua & Barbuda</option> <option value="Argentina">Argentina</option> <option value="Armenia">Armenia</option> <option value="Aruba">Aruba</option> <option value="Australia">Australia</option> <option value="Austria">Austria</option> <option value="Azerbaijan">Azerbaijan</option> <option value="Bahamas">Bahamas</option> <option value="Bahrain">Bahrain</option> <option value="Bangladesh">Bangladesh</option> <option value="Barbados">Barbados</option> <option value="Belarus">Belarus</option> <option value="Belgium">Belgium</option> <option value="Belize">Belize</option> <option value="Benin">Benin</option> <option value="Bermuda">Bermuda</option> <option value="Bhutan">Bhutan</option> <option value="Bolivia">Bolivia</option> <option value="Bonaire">Bonaire</option> <option value="Bosnia & Herzegovina">Bosnia & Herzegovina</option> <option value="Botswana">Botswana</option> <option value="Brazil">Brazil</option> <option value="British Indian Ocean Ter">British Indian Ocean Ter</option> <option value="Brunei">Brunei</option> <option value="Bulgaria">Bulgaria</option> <option value="Burkina Faso">Burkina Faso</option> <option value="Burundi">Burundi</option> <option value="Cambodia">Cambodia</option> <option value="Cameroon">Cameroon</option> <option value="Canada">Canada</option> <option value="Canary Islands">Canary Islands</option> <option value="Cape Verde">Cape Verde</option> <option value="Cayman Islands">Cayman Islands</option> <option value="Central African Republic">Central African Republic</option> <option value="Chad">Chad</option> <option value="Channel Islands">Channel Islands</option> <option value="Chile">Chile</option> <option value="China">China</option> <option value="Christmas Island">Christmas Island</option> <option value="Cocos Island">Cocos Island</option> <option value="Colombia">Colombia</option> <option value="Comoros">Comoros</option> <option value="Congo">Congo</option> <option value="Cook Islands">Cook Islands</option> <option value="Costa Rica">Costa Rica</option> <option value="Cote DIvoire">Cote DIvoire</option> <option value="Croatia">Croatia</option> <option value="Cuba">Cuba</option> <option value="Curaco">Curacao</option> <option value="Cyprus">Cyprus</option> <option value="Czech Republic">Czech Republic</option> <option value="Denmark">Denmark</option> <option value="Djibouti">Djibouti</option> <option value="Dominica">Dominica</option> <option value="Dominican Republic">Dominican Republic</option> <option value="East Timor">East Timor</option> <option value="Ecuador">Ecuador</option> <option value="Egypt">Egypt</option> <option value="El Salvador">El Salvador</option> <option value="Equatorial Guinea">Equatorial Guinea</option> <option value="Eritrea">Eritrea</option> <option value="Estonia">Estonia</option> <option value="Ethiopia">Ethiopia</option> <option value="Falkland Islands">Falkland Islands</option> <option value="Faroe Islands">Faroe Islands</option> <option value="Fiji">Fiji</option> <option value="Finland">Finland</option> <option value="France">France</option> <option value="French Guiana">French Guiana</option> <option value="French Polynesia">French Polynesia</option> <option value="French Southern Ter">French Southern Ter</option> <option value="Gabon">Gabon</option> <option value="Gambia">Gambia</option> <option value="Georgia">Georgia</option> <option value="Germany">Germany</option> <option value="Ghana">Ghana</option> <option value="Gibraltar">Gibraltar</option> <option value="Great Britain">Great Britain</option> <option value="Greece">Greece</option> <option value="Greenland">Greenland</option> <option value="Grenada">Grenada</option> <option value="Guadeloupe">Guadeloupe</option> <option value="Guam">Guam</option> <option value="Guatemala">Guatemala</option> <option value="Guinea">Guinea</option> <option value="Guyana">Guyana</option> <option value="Haiti">Haiti</option> <option value="Hawaii">Hawaii</option> <option value="Honduras">Honduras</option> <option value="Hong Kong">Hong Kong</option> <option value="Hungary">Hungary</option> <option value="Iceland">Iceland</option> <option value="Indonesia">Indonesia</option> <option value="India">India</option> <option value="Iran">Iran</option> <option value="Iraq">Iraq</option> <option value="Ireland">Ireland</option> <option value="Isle of Man">Isle of Man</option> <option value="Israel">Israel</option> <option value="Italy">Italy</option> <option value="Jamaica">Jamaica</option> <option value="Japan">Japan</option> <option value="Jordan">Jordan</option> <option value="Kazakhstan">Kazakhstan</option> <option value="Kenya">Kenya</option> <option value="Kiribati">Kiribati</option> <option value="Korea North">Korea North</option> <option value="Korea Sout">Korea South</option> <option value="Kuwait">Kuwait</option> <option value="Kyrgyzstan">Kyrgyzstan</option> <option value="Laos">Laos</option> <option value="Latvia">Latvia</option> <option value="Lebanon">Lebanon</option> <option value="Lesotho">Lesotho</option> <option value="Liberia">Liberia</option> <option value="Libya">Libya</option> <option value="Liechtenstein">Liechtenstein</option> <option value="Lithuania">Lithuania</option> <option value="Luxembourg">Luxembourg</option> <option value="Macau">Macau</option> <option value="Macedonia">Macedonia</option> <option value="Madagascar">Madagascar</option> <option value="Malaysia">Malaysia</option> <option value="Malawi">Malawi</option> <option value="Maldives">Maldives</option> <option value="Mali">Mali</option> <option value="Malta">Malta</option> <option value="Marshall Islands">Marshall Islands</option> <option value="Martinique">Martinique</option> <option value="Mauritania">Mauritania</option> <option value="Mauritius">Mauritius</option> <option value="Mayotte">Mayotte</option> <option value="Mexico">Mexico</option> <option value="Midway Islands">Midway Islands</option> <option value="Moldova">Moldova</option> <option value="Monaco">Monaco</option> <option value="Mongolia">Mongolia</option> <option value="Montserrat">Montserrat</option> <option value="Morocco">Morocco</option> <option value="Mozambique">Mozambique</option> <option value="Myanmar">Myanmar</option> <option value="Nambia">Nambia</option> <option value="Nauru">Nauru</option> <option value="Nepal">Nepal</option> <option value="Netherland Antilles">Netherland Antilles</option> <option value="Netherlands">Netherlands (Holland, Europe)</option> <option value="Nevis">Nevis</option> <option value="New Caledonia">New Caledonia</option> <option value="New Zealand">New Zealand</option> <option value="Nicaragua">Nicaragua</option> <option value="Niger">Niger</option> <option value="Nigeria">Nigeria</option> <option value="Niue">Niue</option> <option value="Norfolk Island">Norfolk Island</option> <option value="Norway">Norway</option> <option value="Oman">Oman</option> <option value="Pakistan">Pakistan</option> <option value="Palau Island">Palau Island</option> <option value="Palestine">Palestine</option> <option value="Panama">Panama</option> <option value="Papua New Guinea">Papua New Guinea</option> <option value="Paraguay">Paraguay</option> <option value="Peru">Peru</option> <option value="Phillipines">Philippines</option> <option value="Pitcairn Island">Pitcairn Island</option> <option value="Poland">Poland</option> <option value="Portugal">Portugal</option> <option value="Puerto Rico">Puerto Rico</option> <option value="Qatar">Qatar</option> <option value="Republic of Montenegro">Republic of Montenegro</option> <option value="Republic of Serbia">Republic of Serbia</option> <option value="Reunion">Reunion</option> <option value="Romania">Romania</option> <option value="Russia">Russia</option> <option value="Rwanda">Rwanda</option> <option value="St Barthelemy">St Barthelemy</option> <option value="St Eustatius">St Eustatius</option> <option value="St Helena">St Helena</option> <option value="St Kitts-Nevis">St Kitts-Nevis</option> <option value="St Lucia">St Lucia</option> <option value="St Maarten">St Maarten</option> <option value="St Pierre & Miquelon">St Pierre & Miquelon</option> <option value="St Vincent & Grenadines">St Vincent & Grenadines</option> <option value="Saipan">Saipan</option> <option value="Samoa">Samoa</option> <option value="Samoa American">Samoa American</option> <option value="San Marino">San Marino</option> <option value="Sao Tome & Principe">Sao Tome & Principe</option> <option value="Saudi Arabia">Saudi Arabia</option> <option value="Senegal">Senegal</option> <option value="Seychelles">Seychelles</option> <option value="Sierra Leone">Sierra Leone</option> <option value="Singapore">Singapore</option> <option value="Slovakia">Slovakia</option> <option value="Slovenia">Slovenia</option> <option value="Solomon Islands">Solomon Islands</option> <option value="Somalia">Somalia</option> <option value="South Africa">South Africa</option> <option value="Spain">Spain</option> <option value="Sri Lanka">Sri Lanka</option> <option value="Sudan">Sudan</option> <option value="Suriname">Suriname</option> <option value="Swaziland">Swaziland</option> <option value="Sweden">Sweden</option> <option value="Switzerland">Switzerland</option> <option value="Syria">Syria</option> <option value="Tahiti">Tahiti</option> <option value="Taiwan">Taiwan</option> <option value="Tajikistan">Tajikistan</option> <option value="Tanzania">Tanzania</option> <option value="Thailand">Thailand</option> <option value="Togo">Togo</option> <option value="Tokelau">Tokelau</option> <option value="Tonga">Tonga</option> <option value="Trinidad & Tobago">Trinidad & Tobago</option> <option value="Tunisia">Tunisia</option> <option value="Turkey">Turkey</option> <option value="Turkmenistan">Turkmenistan</option> <option value="Turks & Caicos Is">Turks & Caicos Is</option> <option value="Tuvalu">Tuvalu</option> <option value="Uganda">Uganda</option> <option value="United Kingdom">United Kingdom</option> <option value="Ukraine">Ukraine</option> <option value="United Arab Erimates">United Arab Emirates</option> <option value="United States of America">United States of America</option> <option value="Uraguay">Uruguay</option> <option value="Uzbekistan">Uzbekistan</option> <option value="Vanuatu">Vanuatu</option> <option value="Vatican City State">Vatican City State</option> <option value="Venezuela">Venezuela</option> <option value="Vietnam">Vietnam</option> <option value="Virgin Islands (Brit)">Virgin Islands (Brit)</option> <option value="Virgin Islands (USA)">Virgin Islands (USA)</option> <option value="Wake Island">Wake Island</option> <option value="Wallis & Futana Is">Wallis & Futana Is</option> <option value="Yemen">Yemen</option> <option value="Zaire">Zaire</option> <option value="Zambia">Zambia</option> <option value="Zimbabwe">Zimbabwe</option> </select>
                                        </div>
                                    </div>
                                    <?php endif; ?>

                                    <hr>

                                    <div class="field">
                                        <label class="label"><?php echo lang('premium_voucher'); ?></label>
                                        <div class="control">
                                            <input type="text" class="input registerInput" name="voucher" id="voucher" placeholder="<?php echo lang('premium_voucher'); ?>">
                                        </div>
                                    </div>

                                    <br>

                                    <div class="termsAgree">
                                        <div class="field">
                                            <div class="control">
                                                <input type="checkbox" name="terms" id="terms" value="true" required="required"> <?php echo lang('premium_agree_terms'); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <br>
                                <div id="errors"></div>
                                <br>
                                <div id="paymentFooter">
                                    <?php if($premium_settings['payment_gateway'] == 'paypal'): ?>
                                        <img src="assets/plugins/droppy_premium/images/paypal.png" alt="paypal" style="float: left; width: 160px;">
                                    <?php elseif($premium_settings['payment_gateway'] == 'stripe'): ?>
                                        <img src="assets/plugins/droppy_premium/images/stripe.png" alt="stripe" style="float: left; width: 110px;">
                                    <?php endif; ?>
                                    <button type="button" id="submitPayment" class="button is-medium button is-link is-blue" style="float: right;"><?php echo lang('premium_register'); ?></button>
                                </div>
                                <br>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<br><br><br>

<script src="//code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="assets/plugins/droppy_premium/js/template.js"></script>
<script>
    "use strict"
    //Validating and submitting form data
    $(document).ready( function() {
        $('#package-section .card button').click(function() {
            $('#package-section .card button').html('<?php echo lang('premium_select_package') ?>');
            $('#package-section .card.active').removeClass('active');
            $(this).parents('.card').addClass('active');
            $(this).html('<?php echo lang('premium_selected') ?>');

            $('input[name="package"]').val($(this).data('id'));

            $([document.documentElement, document.body]).animate({
                scrollTop: $("#paymentForm").offset().top
            }, 2000);
        });

        $('body #submitPayment').click(function() {
            $(this).text('<?php echo lang('premium_processing'); ?>');

            $('#package-errors').html('');

            var email 	= $('#email').val();
            var pass 	= $('#password').val();
            var re_pass = $('#re_password').val();
            var name	= $('#name').val();
            var terms 	= $('#terms');
            var voucher = $('#voucher').val();

            if($('input[name="package"]').val() == '') {
                $([document.documentElement, document.body]).animate({
                    scrollTop: $("#package-section").offset().top
                }, 2000);
                $('#package-errors').html("<div class='notification is-warning' style='text-align: center;'><?php echo lang('premium_package_not_selected'); ?></div>");
                $('#submitPayment').text('<?php echo lang('premium_register'); ?>');
            } else {
                if (email == '' || email == null || pass == '' || pass == null || re_pass == '' || re_pass == null || name == '' || name == null || !terms.is(':checked')) {
                    $('#submitPayment').text('<?php echo lang('premium_register'); ?>');
                    $('#errors').html('<div class="notification is-warning" role="alert" style="text-align: center;"><?php echo lang('premium_fill_fields'); ?></div>');
                } else {
                    //Data that will be post to the php file to check if the email already exists in the database
                    var dataString = 'action=check_email&email=' + email;

                    //Ajax post
                    $.ajax({
                        type: "POST",
                        url: "",
                        data: dataString,
                        success: function (return_data) {
                            console.log(return_data);

                            //When the email does not exists
                            if (return_data == 1) {
                                if (voucher == '') {
                                    //If the password is shorter than 6 characters
                                    if (pass.length < 6) {
                                        $('#submitPayment').text('<?php echo lang('premium_register'); ?>');
                                        $('#errors').html('<div class="notification is-warning" role="alert" style="text-align: center;"><?php echo lang('premium_password_longer'); ?></div>');
                                    } else {
                                        //If the passwords are the same
                                        if (pass == re_pass) {
                                            //Submit form
                                            document.getElementById('paymentForm').submit();
                                        } else {
                                            $('#submitPayment').text('<?php echo lang('premium_register'); ?>');
                                            $('#errors').html('<div class="notification is-warning" role="alert" style="text-align: center;"><?php echo lang('premium_password_match'); ?></div>');
                                        }
                                    }
                                } else if (voucher != '') {
                                    var dataString = 'action=check_voucher&voucher=' + voucher;
                                    $.ajax({
                                        type: "POST",
                                        url: "",
                                        data: dataString,
                                        success: function (return_data) {
                                            //When the email does not exists
                                            if (return_data == 1) {
                                                //If the password is shorter than 6 characters
                                                if (pass.length < 6) {
                                                    $('#submitPayment').text('<?php echo lang('premium_register'); ?>');
                                                    $('#errors').html('<div class="notification is-warning" role="alert" style="text-align: center;"><?php echo lang('premium_password_longer'); ?></div>');
                                                } else {
                                                    //If the passwords are the same
                                                    if (pass == re_pass) {
                                                        //Submit form
                                                        document.getElementById('paymentForm').submit();
                                                    } else {
                                                        $('#submitPayment').text('<?php echo lang('premium_register'); ?>');
                                                        $('#errors').html('<div class="notification is-warning" role="alert" style="text-align: center;"><?php echo lang('premium_password_match'); ?></div>');
                                                    }
                                                }
                                            } else {
                                                $('#submitPayment').text('<?php echo lang('premium_register'); ?>');
                                                $('#errors').html('<div class="notification is-warning" role="alert" style="text-align: center;"><?php echo lang('premium_invalid_voucher'); ?></div>');
                                            }
                                        }
                                    });
                                }
                            }
                            if (return_data == 2) {
                                $('#submitPayment').text('<?php echo lang('premium_register'); ?>');
                                $('#errors').html('<div class="notification is-warning" role="alert" style="text-align: center;"><?php echo lang('premium_email_exists'); ?></div>');
                            }
                            if (return_data == 3) {
                                $('#submitPayment').text('<?php echo lang('premium_register'); ?>');
                                $('#errors').html('<div class="notification is-warning" role="alert" style="text-align: center;"><?php echo lang('premium_invalid_email'); ?></div>');
                            }
                        }
                    });
                }
            }
        });
    });
    $(document).ready( function() {
        $('body').on('submit', '#addSubFrom', function(ev) {
            ev.preventDefault();
            $(this).find('input[type="submit"]').val('<?php echo lang('premium_processing'); ?>');
            var name	= $('#name').val();
            var terms 	= $('#terms');

            if(name == '' || name == null || !terms.is(':checked'))
            {
                $(this).find('input[type="submit"]').val('<?php echo lang('premium_checkout'); ?>');
                $('#errors').html('<div class="notification is-warning" role="alert" style="text-align: center;"><?php echo lang('premium_fill_fields'); ?></div>');
            }
            else
            {
                document.getElementById('addSubFrom').submit();
            }
        });
    });

    $("a[href='#footer']").on('click', function() {
      $("html, body").animate({ scrollTop: $(document).height() }, "slow");
      return false;
    });
</script>

</body>
</html>