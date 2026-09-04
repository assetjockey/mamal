<?php
$this->load->helper('number');

$clsUser = new PremiumUser();
$clsUploads = new PremiumUploads();
$clsDownloads = new PremiumDownloads();

//Get users from database
$get_users = $clsUser->getAll();
?>
<div class="card">
    <div class="card-header">
        <div class="col">
            <h4 class="card-title">Users</h4>
        </div>
    </div>
    <div class="card-body">
        <?php
        if($get_users === false) :
        ?>
        <h4>No users have been found in the database</h4>
        <?php
        else:
        ?>
          <table class="table table-bordered table-striped table-condensed">
            <thead>
              <tr>
                  <th>ID</th>
                  <th>E-Mail</th>
                  <th>Sub-ID</th>
                  <th>IP</th>
                  <th>Total upload (last month)</th>
                  <th>Total download (last month)</th>
                  <th>Action</th>
              </tr>
            </thead>
            <tbody>
            <?php
              //Get table page
              $rows_per_page = 20;
              if(isset($_GET['table'])) {
                $table_page = $_GET['table'];
              }
              else
              {
                $table_page = 0;
              }
              $current_table = $table_page * $rows_per_page;

              $get_users_table = $clsUser->getAll($current_table, $rows_per_page);

              //Echo table content
              foreach ($get_users_table AS $row) {
                echo '<tr>';
                echo '<td>' . $row['id'] . '</td>';
                echo '<td>' . $row['email'] . '</td>';
                echo '<td>' . $row['sub_id'] . '</td>';
                echo '<td>' . $row['ip'] . '</td>';
                echo '<td>' . byte_format($clsUploads->getTotalUploadByUser($row['id'])['total_size']) . '</td>';
                echo '<td>' . byte_format($clsDownloads->getTotalByUserID($row['id'])) . '</td>';
                echo '<td><form method="POST" action="'.$this->config->item('site_url').'page/premium"><input type="hidden" name="action" value="delete_user"><input type="hidden" name="id" value="' . $row['id'] . '"><input type="hidden" name="return" value="' . current_url() . '?p=users"><button class="btn btn-danger btn-xs" type="submit" onclick="return confirm(\'Are you sure?\')">Delete</button></form></td>';
                echo '</tr>';
              }
            ?>
          </tbody>
      </table>
      <?php
      //Pagination script
      $get_all = $get_users;
      $count_rows = count($get_users);
      $total_pages = round($count_rows / $rows_per_page);
      $page_up = $table_page + 1;
      $page_down = $table_page - 1;
      ?>
      <div style="float: right; padding-right: 10px;">
      <?php
      if($table_page > 0):
      ?>
        <a href="<?php echo current_url() ?>?p=users&table=<?php echo $page_down; ?>" class="btn btn-danger"><i class="fa fa-arrow-left"></i> Prev</a>
      <?php
      endif;
      if($total_pages > $table_page + 1) :
      ?>
        <a href="<?php current_url() ?>?p=users&table=<?php echo $page_up; ?>" class="btn btn-success">Next <i class="fa fa-arrow-right"></i></a>
      <?php
      endif;
      ?>
      </div>
      <?php
      endif;
      ?>
    </div>
</div>