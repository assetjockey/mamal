<div class="card">
    <div class="card-header">
        <div class="col">
            <h4 class="card-title">Add new subscription</h4>
        </div>
    </div>
    <div class="card-body">
        <form class="form-horizontal style-form" method="post" action="<?php echo $this->config->item('site_url') ?>page/premium">
            <input type="hidden" name="action" value="add_usersub">
            <input type="hidden" name="goback" value="<?php echo current_url(); ?>?p=subs">
            <div class="mb-3">
                <label class="form-label">Email</label>
                <div class="col-sm-10">
                    <input type="email" class="form-control" name="email" placeholder="Email of the user" required="required">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Full name</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" name="fullname" placeholder="Full name of the user" required="required">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Company name (not required)</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" name="company" placeholder="Company name of the user">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Subscription plan</label>
                <div class="col-sm-10">
                    <select class="form-control" name="plan_id" required>
                        <?php
                        $clsPlans = new PremiumPlans();
                        $plans = $clsPlans->getAll();

                        if(count($plans) > 0) {
                            foreach ($plans as $plan) {
                                echo '<option value="'.$plan['id'].'">'.$plan['plan_name'].'</option>';
                            }
                        }
                        ?>
                    </select>
                    <p><i>Select if you want to show the address fields in the subscription form</i></p>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                <div class="col-sm-10">
                    <input type="password" class="form-control" name="password" placeholder="The password the user will be logging in with." required="required">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Subscription expiry</label>
                <div class="col-sm-10">
                    <input type="date" class="form-control" name="expiry" placeholder="The expiry date of the subscription" required="required">
                    <i>You should enter it like <strong>24/09/2021</strong> (will be 24 sept. 2021).</i>
                </div>
            </div>

            <br>
            <button type="submit" class="btn btn-primary" ><i class="fa fa-floppy-o"></i>&nbsp;Create user</button>
        </form>
    </div>
</div>