<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <?= $this->views['account_header_menu'] ?>

    <div class="row mb-4">
        <div class="col-12 col-lg d-flex align-items-end mb-3 mb-lg-0 text-truncate">
            <h1 class="h4 m-0 text-truncate"><?= l('referrals_users.header') ?></h1>

            <div class="ml-2">
                <span data-toggle="tooltip" title="<?= l('referrals_users.subheader') ?>">
                    <i class="fas fa-fw fa-info-circle text-muted"></i>
                </span>
            </div>
        </div>

        <div class="col-12 col-lg-auto d-flex flex-wrap gap-3 d-print-none">
            <div>
                <a href="<?= url('referrals') ?>" class="btn btn-light" data-toggle="tooltip" title="<?= l('referrals.menu') ?>">
                    <i class="fas fa-fw fa-sm fa-wallet"></i>
                </a>
            </div>

            <div>
                <div class="dropdown">
                    <button type="button" class="btn <?= $data->filters->has_applied_filters ? 'btn-dark' : 'btn-light' ?> filters-button dropdown-toggle-simple <?= !empty($data->referrals_users) || $data->filters->has_applied_filters ? null : 'disabled' ?>" data-toggle="dropdown" data-boundary="viewport" data-tooltip data-html="true" title="<?= l('global.filters.tooltip') ?>" data-tooltip-hide-on-click>
                        <i class="fas fa-fw fa-sm fa-filter"></i>
                    </button>

                    <div class="dropdown-menu dropdown-menu-right filters-dropdown">
                        <div class="dropdown-header d-flex justify-content-between">
                            <span class="h6 m-0"><?= l('global.filters.header') ?></span>

                            <?php if($data->filters->has_applied_filters): ?>
                                <a href="<?= url(\Altum\Router::$original_request) ?>" class="text-muted"><?= l('global.filters.reset') ?></a>
                            <?php endif ?>
                        </div>

                        <div class="dropdown-divider"></div>

                        <form action="" method="get" role="form">
                            <div class="form-group px-4">
                                <label for="filters_referred_by_has_converted" class="small"><?= l('global.status') ?></label>
                                <select name="referred_by_has_converted" id="filters_referred_by_has_converted" class="custom-select custom-select-sm">
                                    <option value=""><?= l('global.all') ?></option>
                                    <option value="1" <?= isset($data->filters->filters['referred_by_has_converted']) && $data->filters->filters['referred_by_has_converted'] == '1' ? 'selected="selected"' : null ?>><?= l('referrals_users.status.converted') ?></option>
                                    <option value="0" <?= isset($data->filters->filters['referred_by_has_converted']) && $data->filters->filters['referred_by_has_converted'] == '0' ? 'selected="selected"' : null ?>><?= l('referrals_users.status.signed_up') ?></option>
                                </select>
                            </div>

                            <div class="form-group px-4">
                                <label for="filters_datetime_field" class="small"><?= l('global.filters.datetime_field') ?></label>
                                <select name="datetime_field" id="filters_datetime_field" class="custom-select custom-select-sm" data-toggle-datetime-filters="#filters_datetime">
                                    <option value=""><?= l('global.none') ?></option>
                                    <option value="datetime" <?= $data->filters->datetime_field == 'datetime' ? 'selected="selected"' : null ?>><?= l('referrals_users.signed_up') ?></option>
                                </select>
                            </div>

                            <div id="filters_datetime">
                                <div class="form-group px-4">
                                    <label for="filters_datetime_start" class="small"><?= l('global.filters.datetime_start') ?></label>
                                    <input type="datetime-local" name="datetime_start" id="filters_datetime_start" class="form-control form-control-sm" value="<?= $data->filters->datetime_start ?>" />
                                </div>

                                <div class="form-group px-4">
                                    <label for="filters_datetime_end" class="small"><?= l('global.filters.datetime_end') ?></label>
                                    <input type="datetime-local" name="datetime_end" id="filters_datetime_end" class="form-control form-control-sm" value="<?= $data->filters->datetime_end ?>" />
                                </div>
                            </div>

                            <div class="form-group px-4">
                                <label for="filters_order_by" class="small"><?= l('global.filters.order_by') ?></label>
                                <select name="order_by" id="filters_order_by" class="custom-select custom-select-sm">
                                    <option value="user_id" <?= $data->filters->order_by == 'user_id' ? 'selected="selected"' : null ?>><?= l('global.id') ?></option>
                                    <option value="datetime" <?= $data->filters->order_by == 'datetime' ? 'selected="selected"' : null ?>><?= l('referrals_users.signed_up') ?></option>
                                    <option value="referred_by_has_converted" <?= $data->filters->order_by == 'referred_by_has_converted' ? 'selected="selected"' : null ?>><?= l('global.status') ?></option>
                                </select>
                            </div>

                            <div class="form-group px-4">
                                <label for="filters_order_type" class="small"><?= l('global.filters.order_type') ?></label>
                                <select name="order_type" id="filters_order_type" class="custom-select custom-select-sm">
                                    <option value="ASC" <?= $data->filters->order_type == 'ASC' ? 'selected="selected"' : null ?>><?= l('global.filters.order_type_asc') ?></option>
                                    <option value="DESC" <?= $data->filters->order_type == 'DESC' ? 'selected="selected"' : null ?>><?= l('global.filters.order_type_desc') ?></option>
                                </select>
                            </div>

                            <div class="form-group px-4">
                                <label for="filters_results_per_page" class="small"><?= l('global.filters.results_per_page') ?></label>
                                <select name="results_per_page" id="filters_results_per_page" class="custom-select custom-select-sm">
                                    <?php foreach($data->filters->allowed_results_per_page as $key): ?>
                                        <option value="<?= $key ?>" <?= $data->filters->results_per_page == $key ? 'selected="selected"' : null ?>><?= $key ?></option>
                                    <?php endforeach ?>
                                </select>
                            </div>

                            <div class="form-group px-4 mt-4">
                                <button type="submit" name="submit" class="btn btn-sm btn-primary btn-block"><?= l('global.submit') ?></button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if(!empty($data->referrals_users)): ?>
        <div class="table-responsive table-custom-container">
            <table class="table table-custom">
                <thead>
                <tr>
                    <th><?= l('referrals_users.referral') ?></th>
                    <th><?= l('global.status') ?></th>
                    <th><?= l('pay.custom_plan.payment_type') ?></th>
                    <th><?= l('referrals_users.commission') ?></th>
                    <th></th>
                </tr>
                </thead>
                <tbody>

                <?php foreach($data->referrals_users as $row): ?>
                    <tr>
                        <td class="text-nowrap font-weight-450 font-size-little-small">
                            <?= $row->referral_code ?>
                        </td>

                        <td class="text-nowrap">
                            <?php if($row->referred_by_has_converted): ?>
                                <span class="badge badge-success"><i class="fas fa-fw fa-sm fa-user-check mr-1"></i> <?= l('referrals_users.status.converted') ?></span>
                            <?php else: ?>
                                <span class="badge badge-light"><i class="fas fa-fw fa-sm fa-user-plus mr-1"></i> <?= l('referrals_users.status.signed_up') ?></span>
                            <?php endif ?>

                            <?php if($row->pending_affiliate_commissions): ?>
                                <span class="badge badge-warning mr-1" data-toggle="tooltip" title="<?= sprintf(l('referrals_users.commission_status.pending'), nr($row->pending_affiliate_commissions)) ?>"><i class="fas fa-fw fa-sm fa-spinner fa-spin"></i></span>
                            <?php endif ?>

                            <?php if($row->approved_affiliate_commissions): ?>
                                <span class="badge badge-success mr-1" data-toggle="tooltip" title="<?= sprintf(l('referrals_users.commission_status.approved'), nr($row->approved_affiliate_commissions)) ?>"><i class="fas fa-fw fa-sm fa-check"></i></span>
                            <?php endif ?>

                            <?php if($row->withdrawn_affiliate_commissions): ?>
                                <span class="badge badge-light mr-1" data-toggle="tooltip" title="<?= sprintf(l('referrals_users.commission_status.withdrawn'), nr($row->withdrawn_affiliate_commissions)) ?>"><i class="fas fa-fw fa-sm fa-wallet"></i></span>
                            <?php endif ?>

                            <?php if(!$row->total_affiliate_commissions): ?>
                                <span class="badge badge-light mr-1" data-toggle="tooltip" title="<?= l('referrals_users.commission_status.none') ?>"><i class="fas fa-fw fa-sm fa-times"></i></span>
                            <?php endif ?>
                        </td>

                        <td class="text-nowrap">
                            <?php if(!empty($row->payment_types)): ?>
                                <?php foreach($row->payment_types as $payment_type): ?>
                                    <?php if($payment_type == 'recurring'): ?>
                                        <span class="badge badge-primary mr-1" data-toggle="tooltip" title="<?= l('pay.custom_plan.recurring_type') ?>"><i class="fas fa-fw fa-sm fa-sync fa-spin"></i></span>
                                    <?php elseif($payment_type == 'one_time'): ?>
                                        <span class="badge badge-info mr-1" data-toggle="tooltip" title="<?= l('pay.custom_plan.one_time_type') ?>"><i class="fas fa-fw fa-sm fa-bolt"></i></span>
                                    <?php endif ?>
                                <?php endforeach ?>
                            <?php else: ?>
                                <span class="badge badge-light" data-toggle="tooltip" title="<?= l('global.none') ?>">
                                    <i class="fas fa-fw fa-sm fa-times text-muted"></i>
                                </span>
                            <?php endif ?>
                        </td>

                        <td class="text-nowrap">
                            <span class="badge <?= $row->total_affiliate_commissions_amount > 0 ? 'badge-success' : 'badge-light' ?> ml-1"><?= nr($row->total_affiliate_commissions_amount, 2) . ' ' . settings()->payment->default_currency ?></span>
                        </td>

                        <td class="text-nowrap text-muted">
                            <span data-toggle="tooltip" data-html="true" title="<?= sprintf(l('referrals_users.signed_up_datetime_tooltip'), '<br />' . \Altum\Date::get($row->datetime, 2) . '<br /><small>' . \Altum\Date::get($row->datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->datetime) . ')</small>') ?>">
                                <i class="fas fa-fw fa-calendar text-muted"></i>
                            </span>

                            <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('referrals_users.latest_commission_datetime_tooltip'), ($row->latest_affiliate_commission_datetime ? '<br />' . \Altum\Date::get($row->latest_affiliate_commission_datetime, 2) . '<br /><small>' . \Altum\Date::get($row->latest_affiliate_commission_datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->latest_affiliate_commission_datetime) . ')</small>' : '<br />' . l('global.na'))) ?>">
                                <i class="fas fa-fw fa-wallet text-muted"></i>
                            </span>
                        </td>
                    </tr>
                <?php endforeach ?>

                </tbody>
            </table>
        </div>

        <div class="mt-3"><?= $data->pagination ?></div>
    <?php else: ?>
        <?= include_view(THEME_PATH . 'views/partials/no_data.php', [
            'filters_get' => $data->filters->get,
            'name' => 'referrals_users',
            'has_secondary_text' => true,
        ]); ?>
    <?php endif ?>

</div>
