<?php

require_once dirname(__FILE__) . '/../../../../autoloader.php';

$this->load->helper('number');

if(!isset($_SESSION['droppy_premium'])) :
    echo '<p style="text-align: center; padding-top: 20px;">' . lang('premium_need_premium') . '</p>';
else:
    $clsUsers = new PremiumUser();
    $clsUploads = new PremiumUploads();
    $clsDownloads = new PremiumDownloads();

    $premium_session_id = $_SESSION['droppy_premium'];
    $parent_id = $clsUsers->getByID($premium_session_id)['parent_id'];
    $get_uploads = $clsUploads->getBySessionID($premium_session_id, $parent_id);
    $storage_used = $clsUsers->getTotalStorage($premium_session_id);

    if(!$get_uploads):
        echo '<p style="text-align: center; padding-top: 20px;">' . lang('premium_no_uploads') . '</p>';
    else:
        ?>
        <?php if($this->config->config['pm_max_storage'] > 0): ?>
            <span class="tag is-medium <?php echo ((($this->config->config['pm_max_storage'] * 1024 * 1024) - $storage_used) < (100 * 1024 * 1024) ? 'is-danger' : '') ?>" style="float: right; margin-bottom: 15px;"><?php echo byte_format($storage_used) ?> of <?php echo byte_format($this->config->config['pm_max_storage'] * 1024 * 1024) ?> used</span>
        <?php endif; ?>

        <table class="table is-striped is-fullwidth">
            <thead>
            <tr>
                <th>ID</th>
                <th><?php echo lang('email_from'); ?></th>
                <th><?php echo lang('total_size'); ?></th>
                <th><?php echo lang('destructed_on'); ?></th>
                <th><?php echo lang('premium_total_downloads'); ?></th>
                <th>#</th>
            </tr>
            </thead>
            <tbody>
            <?php
            foreach($get_uploads AS $row)
            {
                $uploadid = $row['upload_id'];

                $total_downloads = $clsDownloads->getTotalByUploadID($uploadid);

                //Table data
                echo '<tr>';
                echo '<td>' . $uploadid . '</td>';
                echo '<td>' . $row['email'] . '</td>';
                echo '<td>' . ($row['size'] > 0 ? byte_format($row['size']) : 0) . '</td>';
                echo '<td>' . ($row['time_expire'] == $row['time'] ? lang('never') : date("Y-m-d", $row['time_expire'])) . '</td>';
                echo '<td>' . $total_downloads . '</td>';
                echo '<td><a href="'.$this->config->item('site_url') . 'upload/delete/' . $uploadid . '/' . $row['secret_code'].'" onclick="return confirm(\''.lang('are_you_sure').'\');">'.lang('delete') .'</a> | <a href="' . $this->config->item('site_url') . $uploadid . '/'.$row['secret_code'].'">' . lang('open')  . '</a></td>';
                echo '</tr>';
            }
            ?>
            </tbody>
        </table>
        <?php
    endif;
endif;
?>