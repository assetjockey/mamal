<?php
require_once dirname(__FILE__) . '/../../../../autoloader.php';
?>

<div style="display: none; border-bottom: 1px solid #CCC; padding-bottom: 20px; margin-bottom: 20px;" id="add-background-div">
    <form method="POST" action="<?php echo $this->config->item('site_url') ?>page/premium" enctype="multipart/form-data">
        <input type="hidden" name="action" value="upload_background">
        <input type="hidden" name="goback" value="<?php echo $this->config->item('site_url') ?>?goto=custom_account&tab=backgrounds">

        <div class="field">
            <label class="label"><?php echo lang('premium_background_file2') ?></label>
            <div class="control">
                <input type="file" name="file" id="background-file-select" accept="image/*, video/mp4">
            </div>
        </div>

        <div class="field">
            <label class="label"><?php echo lang('premium_background_url') ?></label>
            <div class="control">
                <input class="input" type="text" name="url" placeholder="<?php echo lang('premium_background_url_desc') ?>">
            </div>
        </div>

        <div class="field">
            <label class="label"><?php echo lang('premium_background_duration') ?></label>
            <div class="control">
                <input class="input" type="number" name="duration" placeholder="<?php echo lang('premium_background_duration_desc') ?>">
            </div>
        </div>

        <div class="field is-grouped">
            <div class="control">
                <button class="button is-link"><?php echo lang('save') ?></button>
            </div>
        </div>
    </form>
</div>

    <script>
        var uploadField = document.getElementById("background-file-select");

        uploadField.onchange = function() {
            if(this.files[0].size > 20971520){
                alert("<?php echo lang('file_too_large') ?>");
                this.value = "";
            };
        };
    </script>

<?php
$this->load->helper('number');

if(!isset($_SESSION['droppy_premium'])) :
    echo '<p style="text-align: center; padding-top: 20px;">' . lang('premium_need_premium') . '</p>';
elseif($this->config->config['custom_backgrounds_enabled'] != 'true') :
    echo '<p style="text-align: center; padding-top: 20px;">' . lang('premium_plan_no_backgrounds') . '</p>';
else:
    $clsBackgrounds = new PremiumBackgrounds();

    $premium_session_id = $_SESSION['droppy_premium'];
    $backgrounds = $clsBackgrounds->getByUserID($premium_session_id);

    if(!$backgrounds):
    ?>
        <a href="#" class="button is-info is-rounded is-centered is-pulled-right js-modal-trigger" onclick="$('#add-background-div').show(); return false;"><?php echo lang('premium_add_background') ?></a>
        <br><br>
        <p style="text-align: center; padding-top: 20px;"><?php echo lang('premium_no_backgrounds') ?></p>
    <?php
        else:
    ?>
        <a href="#" class="button is-info is-rounded is-centered is-pulled-right" id="add-user" onclick="$('#add-background-div').show(); return false;"><?php echo lang('premium_add_background') ?></a>

        <table class="table is-striped is-fullwidth">
            <thead>
            <tr>
                <th><?php echo lang('premium_background_file'); ?></th>
                <th><?php echo lang('premium_background_url'); ?></th>
                <th><?php echo lang('premium_background_duration'); ?></th>
                <th>#</th>
            </tr>
            </thead>
            <tbody>
            <?php
            foreach($backgrounds AS $row)
            {
                echo '<tr>';
                echo '<td>' . basename($row['src']) . '</td>';
                echo '<td>' . $row['url'] . '</td>';
                echo '<td>' . $row['duration'] . '</td>';
                echo '<td><a href="'.$this->config->item('site_url') . $row['src'] . '" target="_blank">'.lang('premium_view') .'</a> | <a href="'.$this->config->item('site_url') . 'page/premium?action=delete_background&id='.$row['id'].'" onclick="return confirm(\''.lang('are_you_sure').'\');">'.lang('delete') .'</a></td>';
                echo '</tr>';
            }
            ?>
            </tbody>
        </table>
        <?php
    endif;
endif;
?>