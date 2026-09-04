<?php

require_once dirname(__FILE__) . '/../../../../autoloader.php';

if(!isset($_SESSION['droppy_premium'])) :
    echo '<p style="text-align: center; padding-top: 20px;">' . lang('premium_need_premium') . '</p>';
else:
    $clsUploads = new PremiumUploads();
    $clsDownloads = new PremiumDownloads();

    $premium_session_id = $_SESSION['droppy_premium'];
    $get_uploads = $clsUploads->getBySessionID($premium_session_id);

    if(!$get_uploads):
        echo '<p style="text-align: center; padding-top: 20px;">' . lang('premium_no_uploads') . '</p>';
    else:
        ?>
        <table class="table is-striped is-fullwidth">
            <thead>
            <tr>
                <th>ID</th>
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
                echo '<td>' . round($row['size'] / 1048576, 2) . ' MB</td>';
                echo '<td>' . date("Y-m-d", $row['time_expire']) . '</td>';
                echo '<td>' . $total_downloads . '</td>';
                echo '<td><a href="'.$this->config->item('site_url') . 'upload/delete/' . $uploadid . '/' . $row['secret_code'].'">'.lang('delete') .'</a> | <a href="' . $this->config->item('site_url') . $uploadid . '/'.$row['secret_code'].'">' . lang('open')  . '</a></td>';
                echo '</tr>';
            }
            ?>
            </tbody>
        </table>
        <?php
    endif;
endif;
?>