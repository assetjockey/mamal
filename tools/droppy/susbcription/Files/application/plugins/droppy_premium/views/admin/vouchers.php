<?php
$clsVoucher = new PremiumVoucher();

$_SESSION['goback'] = current_url() . '?p=vouchers';

//Get subs from database
$get_vouchers = $clsVoucher->getAll();
?>
<div class="modal modal-blur fade" id="addVoucher" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <form method="POST" action="<?php echo $this->config->item('site_url') ?>page/premium">
            <input type="hidden" name="action" value="add_voucher">
            <input type="hidden" name="goback" value="<?php echo current_url(); ?>?p=vouchers">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add voucher</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="min-width: 720px;">
                    <div class="mb-3">
                        <p>Voucher code</p>
                        <input type="text" class="form-control" name="code" placeholder="Voucher code" required="required">
                    </div>
                    <br>
                    <div class="mb-3">
                        <p>Voucher discount</p>
                        <input type="number" class="form-control" name="discount" placeholder="The voucher discount">
                    </div>
                    <br>
                    <p><b>Or</b> Voucher discount in percentage</p>
                    <div class="mb-3">
                        <div class="input-group mb-2">
                              <span class="input-group-text">%</span>
                            <input type="number" name="discount_percentage" min="0" max="100" class="form-control" placeholder="Voucher discount in percentage" autocomplete="off">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Create voucher</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="col">
            <h4 class="card-title">Add new subscription</h4>
        </div>
        <div class="col-auto ms-auto">
            <a href="#" data-bs-toggle="modal" data-bs-target="#addVoucher" class="btn btn-default" style="float:right;">Add voucher</a>
        </div>
    </div>
    <div class="card-body">
        <br>
        <?php
        //Check if there are any subs
        if(!$get_vouchers || count($get_vouchers) == 0) :
            ?>
            <h4>No vouchers have been found in the database</h4>
            <?php
        else:
            ?>
            <table class="table table-bordered table-striped table-condensed">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Code</th>
                        <th>Discount type</th>
                        <th>Discount</th>
                        <th>#</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    //Echo table content
                    foreach ($get_vouchers AS $row) {
                        echo '<tr>';
                        echo '<td>' . $row['id'] . '</td>';
                        echo '<td>' . $row['code'] . '</td>';
                        echo '<td>' . $row['discount_type'] . '</td>';
                        if($row['discount_type'] == 'value') {
                            echo '<td>' . $clsSettings->getSettings()['currency'] .  ' ' . $row['discount_value'] . '</td>';
                        }
                        elseif($row['discount_type'] == 'percentage') {
                            echo '<td>' . $row['discount_percentage'] . '&percnt;</td>';
                        }
                        echo '<td><a href="'.$this->config->item('site_url').'page/premium?action=delete_voucher&id=' . $row['id'] . '">Delete</a></td>';
                    }
                    ?>
                </tbody>
            </table>
        <?php
        endif;
        ?>
    </div>
</div>