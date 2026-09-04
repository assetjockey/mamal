<form class="form-horizontal style-form" method="post" action="<?php echo $this->config->item('site_url') ?>page/premium">
    <div class="card">
        <div class="card-header">
            <div class="col">
                <h4 class="card-title">Add new plan</h4>
            </div>
        </div>
        <div class="card-body">
            <input type="hidden" name="action" value="add_plan">
            <input type="hidden" name="goback" value="<?php echo current_url() ?>?p=plans">
            <div class="mb-3">
                <label class="form-label">Plan name</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" name="plan_name" placeholder="Plan name">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Plan description</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" name="plan_desc" placeholder="Plan description (shown at payment provider)">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Plan features</label>
                <div class="col-sm-10">
                    <textarea class="form-control" name="plan_features" style="min-height: 200px;"><ul>
    <li>Feature 1</li>
    <li>Feature 2</li>
</ul></textarea>
                    <i>List of features, shown on the product page. You're able to use HTML, to create a list please use the code below as example:</i>
                    <pre>&lt;ul&gt;
    &lt;li&gt;Feature 1&lt;/li&gt;
    &lt;li&gt;Feature 2&lt;/li&gt;
&lt;/ul&gt;</pre>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Plan price</label>
                <div class="col-sm-10">
                    <input type="number" step=".01" class="form-control" name="plan_price" placeholder="Plan price, currency is set on General settings page">
                    <p><i>The price of the subscription.</i></p>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Billing time</label>
                <div class="col-sm-10">
                    <select class="form-control" name="plan_time">
                        <option value="Month">Month</option>
                        <option value="Week">Week</option>
                        <option value="Day">Day</option>
                    </select>
                    <p><i>Unit for billing during this subscription period.</i></p>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Billing frequent</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" name="plan_freq" placeholder="Recurring frequent" min="1">
                    <p><i>Number of billing periods that make up one billing cycle. (See documentation for further information). Set the value to 12 and Billing time to "Month" to create a yearly plan.</i></p>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Max upload size (Only premium)</label>
                <div class="col-sm-10">
                    <input type="number" class="form-control" name="plan_max_size" placeholder="Max upload size (Only for premium users)">
                    <p><i>Size in MB</i></p>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Max storage (in MB)</label>
                <div class="col-sm-10">
                    <input type="number" class="form-control" name="plan_max_storage" placeholder="Max account storage">
                    <p><i>Size in MB. This sets the maximum amount that can be stored in an account, when the user reaches their limit the max. upload size will decrease. Leave empty or set to 0 to allow unlimited storage.</i></p>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Password function enabled (Only premium)</label>
                <div class="col-sm-10">
                    <select class="form-control" name="plan_password_enabled">
                        <option value="true">Allow NON premium users.</option>
                        <option value="false">Block NON premium users.</option>
                    </select>
                    <p><i>Select if the password function should be enabled for premium users.</i></p>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Available expire times</label>
                <div class="col-sm-10">
                    <select class="form-control" name="plan_expire_time[]" multiple="multiple" style="min-height: 300px;">
                        <option value="0">Do not expire</option>
                        <optgroup label="Hours">
                            <option value="3600">1 Hour</option>
                            <option value="10800">3 Hours</option>
                            <option value="18000">5 Hours</option>
                            <option value="28800">8 Hours</option>
                            <option value="36000">10 Hours</option>
                            <option value="43200">12 Hours</option>
                            <option value="50400">14 Hours</option>
                            <option value="57600">16 Hours</option>
                            <option value="64800">18 Hours</option>
                            <option value="72000">20 Hours</option>
                            <option value="79200">22 Hours</option>
                        </optgroup>
                        <optgroup label="Days">
                            <option value="86400">1 Day</option>
                            <option value="172800">2 Days</option>
                            <option value="259200">3 Days</option>
                            <option value="345600">4 Days</option>
                            <option value="432000">5 Days</option>
                            <option value="518400">6 Days</option>
                        </optgroup>
                        <optgroup label="Weeks">
                            <option value="604800">1 Week</option>
                            <option value="1209600">2 Weeks</option>
                            <option value="1814400">3 Weeks</option>
                        </optgroup>
                        <optgroup label="Months">
                            <option value="2592000">1 Month</option>
                            <option value="5184000">2 Months</option>
                            <option value="7776000">3 Months</option>
                            <option value="10368000">4 Months</option>
                            <option value="12960000">5 Months</option>
                            <option value="15552000">6 Months</option>
                            <option value="18144000">7 Months</option>
                            <option value="20736000">8 Months</option>
                            <option value="23328000">9 Months</option>
                            <option value="25920000">10 Months</option>
                            <option value="28512000">11 Months</option>
                            <option value="31104000">12 Months</option>
                        </optgroup>
                    </select>
                    <p><i>Time till a file gets destroyed</i><br><i>Select multiple values by selecting them while holding the CTRL or CMD key.</i></p>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Advertising enabled (Only premium)</label>
                <div class="col-sm-10">
                    <select class="form-control" name="plan_ad_enabled">
                        <option value="true">Yes</option>
                        <option value="false">No</option>
                    </select>
                    <p><i>Select if the advertising section should be shown for premium users.</i></p>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Allow custom backgrounds</label>
                <div class="col-sm-10">
                    <select class="form-control" name="plan_backgrounds">
                        <option value="true">Yes</option>
                        <option value="false">No</option>
                    </select>
                    <p><i>User can upload their own backgrounds, these are shown to the user itself and on the download page of recipients.</i></p>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" ><i class="fa fa-floppy-o"></i>&nbsp;Add</button>
        </div>
    </div>
</form>