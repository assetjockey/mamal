<?php
require_once dirname(__FILE__) . '/../../../../autoloader.php';
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
                document.getElementById('errors').innerHTML = '<div class="alert alert-warning" role="alert" style="text-align: center;"><?php echo lang('premium_fill_fields'); ?></div>';
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
                            document.getElementById('errors').innerHTML = '<div class="alert alert-warning" role="alert" style="text-align: center;"><?php echo lang('premium_account_suspended'); ?></div>';
                        }
                        if(return_data == 0)
                        {
                            document.getElementById('errors').innerHTML = '<div class="alert alert-warning" role="alert" style="text-align: center;"><?php echo lang('invalid_login'); ?></div>';
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
                document.getElementById('errors').innerHTML = '<div class="alert alert-warning" role="alert" style="text-align: center;"><?php echo lang('premium_fill_fields'); ?></div>';
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
                            document.getElementById('errors').innerHTML = '<div class="alert alert-success" role="alert" style="text-align: center;"><?php echo lang('premium_email_sent'); ?></div>';
                        }
                        if(return_data == 0)
                        {
                            document.getElementById('errors').innerHTML = '<div class="alert alert-warning" role="alert" style="text-align: center;"><?php echo lang('premium_email_not_exists'); ?></div>';
                        }
                    }
                });
            }
        });
        $('body').on('click', '#submitReset', function(ev) {
            ev.preventDefault();

            var pass1 		= document.getElementById('password1').value;
            var pass2 		= document.getElementById('password2').value;
            var reset_code 	= document.getElementById('reset_code').value;

            if(pass1 == '' || pass1 == null || pass2 == '' || pass2 == null)
            {
                document.getElementById('errors').innerHTML = '<div class="alert alert-warning" role="alert" style="text-align: center;"><?php echo lang('premium_fill_fields'); ?></div>';
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
                                window.location.href = "?goto=custom_account&error=<?php echo lang('premium_pass_changed'); ?>";
                            }
                            if(return_data == 0)
                            {
                                document.getElementById('errors').innerHTML = '<div class="alert alert-warning" role="alert" style="text-align: center;"><?php echo lang('premium_something_wrong'); ?></div>';
                            }
                        }
                    });
                }
                else
                {
                    document.getElementById('errors').innerHTML = '<div class="alert alert-warning" role="alert" style="text-align: center;"><?php echo lang('premium_pass_same'); ?></div>';
                }
            }
        });
        $('body').on('click', '#submitDetailsChange', function(ev) {
            ev.preventDefault();

            var email 	= document.getElementById('email').value;
            var pass 	= document.getElementById('pwd').value;
            var name 	= document.getElementById('name').value;
            var company	= document.getElementById('company').value;
            var subid	= document.getElementById('sub_id').value;

            if(email == '' || email == null || name == '' || name == null || subid == '' || subid == null)
            {
                document.getElementById('errorsOther').innerHTML = '<p style="color: red;"><?php echo lang('premium_fill_fields'); ?></p>';
            }
            else
            {
                // Data that will be post to the php file to check if the email already exists in the database
                var dataString = 'action=change_details&email='+email+'&password='+pass+'&name='+name+'&company='+company+'&sub_id='+subid;
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
                            document.getElementById('emailFrom').value = email;
                            document.getElementById('errorsOther').innerHTML = '<p style="color: green;"><?php echo lang('premium_info_changed'); ?></p>';
                        }
                        if(return_data == 2)
                        {
                            document.getElementById('errorsOther').innerHTML = '<p style="color: red;"><?php echo lang('premium_email_exists'); ?></p>';
                        }
                        if(return_data == 3)
                        {
                            document.getElementById('errorsOther').innerHTML = '<p style="color: red;"><?php echo lang('premium_fill_fields'); ?></p>';
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
    });
</script>
<?php
if(isset($_GET['error'])) :
    ?>
    <div id="errors"><div class="alert alert-info" role="alert" style="text-align: center;"><?php echo $_GET['error'] ?></div></div>
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
        <table>
            <tr>
                <td><strong><?php echo lang('premium_account_status'); ?>:</strong></td>
                <td id="table_value"><?php echo $sub_status; ?></td>
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
        <a class="btn btn-default btn-xs" href="<?php echo $this->config->item('site_url') ?>page/premium?manage=<?php echo $sub_id; ?>#footer"><?php echo lang('premium_create_new_sub'); ?></a>
        <?php
    elseif($sub_status == 'active' || $sub_status == 'canceled_end') :
        ?>
        <p><?php echo lang('premium_hi') . ' ' . $name; ?></p>
        <hr>
        <h4><?php echo lang('premium_your_sub_details'); ?>:</h4>
        <table>
            <tr>
                <td><strong><?php echo lang('premium_account_status'); ?>:</strong></td>
                <td id="table_value"><?php echo $sub_status; ?></td>
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
        <hr>
        <h4><?php echo lang('premium_other_details'); ?>:</h4>
        <div id="errorsOther"></div>
        <form role="form">
            <input type="hidden" id="sub_id" value="<?php echo $sub_id; ?>">
            <div class="form-group">
                <label for="email"><?php echo lang('email'); ?>:</label>
                <input type="email" class="form-control input-sm" id="email" placeholder="<?php echo lang('enter_own_email'); ?>" value="<?php echo $email; ?>">
            </div>
            <div class="form-group">
                <label for="pwd"><?php echo lang('password'); ?>:</label>
                <input type="password" class="form-control input-sm" id="pwd" placeholder="<?php echo lang('password'); ?>">
            </div>
            <div class="form-group">
                <label for="pwd"><?php echo lang('premium_fullname'); ?>:</label>
                <input type="text" class="form-control input-sm" id="name" placeholder="<?php echo lang('premium_fullname'); ?>" value="<?php echo $name; ?>">
            </div>
            <div class="form-group">
                <label for="pwd"><?php echo lang('premium_company'); ?>:</label>
                <input type="text" class="form-control input-sm" id="company" placeholder="<?php echo lang('premium_company'); ?>" value="<?php echo $company; ?>">
            </div>
            <button type="button" id="submitDetailsChange" class="btn btn-default btn-sm"><?php echo lang('save'); ?></button>
        </form>
        <?php
    endif;
else:
    ?>
    <?php
    if(isset($_GET['reset'])) :
        ?>
        <div class="loginDiv resetDiv" id="loginDiv">
            <form role="form">
                <input type="hidden" name="reset_code" id="reset_code" value="<?php echo $_GET['reset']; ?>">
                <div class="form-group">
                    <label for="pwd"><?php echo lang('password'); ?>:</label>
                    <input type="password" class="form-control" id="password1" placeholder="<?php echo lang('password'); ?>">
                </div>
                <div class="form-group">
                    <label for="pwd"><?php echo lang('premium_password_re'); ?>:</label>
                    <input type="password" class="form-control" id="password2" placeholder="<?php echo lang('premium_password_re'); ?>">
                </div>
                <button type="button" id="submitReset" class="btn btn-default"><?php echo lang('premium_submit'); ?></button>
            </form>
        </div>
        <?php
    else:
        ?>
        <div class="loginDiv" id="loginDiv">
            <form role="form">
                <div class="form-group">
                    <label for="email"><?php echo lang('email'); ?>:</label>
                    <input type="email" class="form-control" id="email" placeholder="<?php echo lang('enter_own_email'); ?>">
                </div>
                <div class="form-group">
                    <label for="pwd"><?php echo lang('password'); ?>:</label>
                    <input type="password" class="form-control" id="pwd" placeholder="<?php echo lang('password'); ?>">
                </div>
                <a href="#" id="openForgot" class="openForm"><?php echo lang('premium_forgot_pass'); ?></a>
                <button type="button" id="submitLogin" class="btn btn-default"><?php echo lang('sign_in'); ?></button>
            </form>
        </div>
        <div class="forgotDiv" id="forgotDiv" style="min-height: 200px">
            <form role="form">
                <div class="form-group">
                    <label for="email"><?php echo lang('email'); ?>:</label>
                    <input type="email" class="form-control" id="email_forgot" placeholder="<?php echo lang('enter_own_email'); ?>">
                </div>
                <a href="#" id="openLogin" class="openForm"><?php echo lang('premium_login_page'); ?></a>
                <button type="button" id="submitForgot" class="btn btn-default"><?php echo lang('premium_submit'); ?></button>
            </form>
        </div>
        <?php
    endif;
endif;