<?php

require_once dirname(__FILE__) . '/../../../../autoloader.php';

if(!isset($_SESSION['droppy_premium'])) :
    echo '<p style="text-align: center; padding-top: 20px;">' . lang('premium_need_premium') . '</p>';
else:
?>
<div id="add-sub-user-block" style="display: none;">
    <form id="add-sub-user">
        <input type="hidden" name="action" value="add_sub_user">
        <div class="field">
            <label class="label"><?php echo lang('email') ?></label>
            <div class="control">
                <input class="input" type="email" name="email" placeholder="<?php echo lang('enter_own_email') ?>">
            </div>
        </div>
        <div class="field">
            <label class="label"><?php echo lang('password') ?></label>
            <div class="control">
                <input class="input" type="password" name="password" placeholder="<?php echo lang('password') ?>" autocomplete="no" autofill="no">
            </div>
        </div>

        <p style="font-size: 14px;"><?php echo lang('sub_user_notice') ?></p><br>

        <div class="field has-text-right">
            <p class="control">
                <button type="button" class="button is-danger" id="cancel-add-user"><?php echo lang('cancel') ?></button> <input type="submit" class="button is-info" value="<?php echo lang('premium_add_user') ?>">
            </p>
        </div>
    </form>
</div>
<div id="sub-user-table">
<?php
    $clsUsers = new PremiumUser();

    $users = $clsUsers->getByParentID($_SESSION['droppy_premium']);

    if(!$users):
        echo '<p style="text-align: center; padding-top: 20px;">' . lang('premium_sub_users_desc') . '<br><br>' . lang('premium_no_users') . '<br><br><a href="#" class="button is-info is-rounded" id="add-user">'.lang('premium_add_user').'</a></p>';
    else:
        ?>
        <a href="#" class="button is-info is-rounded is-centered is-pulled-right" id="add-user"><?php echo lang('premium_add_user') ?></a>

        <table class="table is-striped is-fullwidth">
            <thead>
            <tr>
                <th>ID</th>
                <th><?php echo lang('email'); ?></th>
                <th>#</th>
            </tr>
            </thead>
            <tbody>
            <?php
            foreach($users AS $row)
            {
                //Table data
                echo '<tr>';
                echo '<td>' . $row['id'] . '</td>';
                echo '<td>' . $row['email'] . '</td>';
                echo '<td><a href="'.$this->config->item('site_url') . 'page/premium?action=delete_sub_user&user='.$row['id'].'">'.lang('delete') .'</a></td>';
                echo '</tr>';
            }
            ?>
            </tbody>
        </table>
    <?php
    endif;
    ?>
</div>
<script>
    $(document.body).on('click', '.account-tab#users #add-user', function(ev) {
        ev.preventDefault();
        $('#sub-user-table').hide();
        $('#add-sub-user-block').show();
    });
    $(document.body).on('click', '.account-tab#users #cancel-add-user', function() {
        $('#sub-user-table').show();
        $('#add-sub-user-block').hide();
    });

    $(document.body).on('submit', '.account-tab#users form#add-sub-user', function(ev) {
        ev.preventDefault();

        $.ajax({
            type: "POST",
            url: "<?php echo $this->config->item('site_url') ?>page/premium",
            data: $(this).serialize(),
            success: function(response) {
                if(response == 'success') {
                    $('#sub-user-table').show();
                    $('#add-sub-user-block').hide();

                    window.location.href = "<?php echo $this->config->item('site_url') . '?goto=custom_account&tab=users'; ?>";
                }
                else
                {
                    $('form#add-sub-user').prepend('<div class="notification is-danger"><?php echo lang('premium_email_exists') ?></div>')
                }
            }
        });
    });
</script>
<?php
endif;
?>