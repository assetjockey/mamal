<?php
if(
    !empty(settings()->ads->footer_transfers)
    && (
        ($this->transfer_request_user && !$this->transfer_request_user->plan_settings->no_ads)
        || (!$this->transfer_request_user && !settings()->plan_guest->settings->no_ads)
    )
): ?>
    <div class="container my-3 d-print-none"><?= settings()->ads->footer_transfers ?></div>
<?php endif ?>
