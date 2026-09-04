<?php

$clsPlans = new PremiumPlans();

//Get subs from database
$get_plans = $clsPlans->getAll();
?>

<?php
$clsPlans = new PremiumPlans();
$plans = $clsPlans->getAll();
if ((empty($premium_settings['username_api']) && empty($premium_settings['stripe_key']))) : ?>
    <div class="card">
        <div class="card-header">
            <div class="col">
                <h4 class="card-title">Plans</h4>
            </div>
        </div>
        <div class="card-body">
            <div class="alert alert-danger" style="margin: 10px 0 20px 0;">
                <h2>No payment gateway setup!</h2>
                It seems you haven't setup a payment gateway yet. Please setup your payment gateway first before creating a plan
                <br>
            </div>
        </div>
    </div>
<?php else: ?>

<div class="card">
    <div class="card-header">
        <div class="col">
            <h4 class="card-title">Plans</h4>
            <a href="<?php echo current_url() ?>?p=addplan" class="btn btn-primary" style="position: absolute; right: 10px; top: 10px;">Add plan</a>
        </div>
    </div>
    <div class="card-body">
        <?php
        //Check if there are any subs
        if(!$get_plans || count($get_plans) == 0) :
        ?>
            <h4>No plans have been found in the database</h4>
        <?php
        else:
            ?>
            <table class="table table-bordered table-striped table-condensed">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Time</th>
                    <th>Active</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                <?php
                // Echo table content
                foreach($get_plans AS $row) {
                    echo '<tr>';
                    echo '<td>' . $row['id'] . '</td>';
                    echo '<td>' . $row['plan_name'] . '</td>';
                    echo '<td>' . $row['plan_price'] . '</td>';
                    echo '<td>' . $row['plan_time'] . '</td>';
                    echo '<td>' . $row['plan_active'] . '</td>';
                    echo '<td><a href="' . current_url() .'?p=editplan&id='.$row['id'].'">Edit</a></td>';
                    echo '</tr>';
                }
                ?>
                </tbody>
            </table>
            <?php
        endif;
        ?>
        <p stlye="margin-left: 5px;">Please note it can sometimes take 1+ minute before your reactivation/cancelation will apply, if this does not happen you will need to check your IPN settings.</p>
    </div>
</div><!-- /content-panel -->
<?php endif; ?>