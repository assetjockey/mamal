<?php defined('ALTUMCODE') || die() ?>

<?php if(settings()->main->breadcrumbs_is_enabled): ?>
    <nav aria-label="breadcrumb">
        <ol class="custom-breadcrumbs small">
            <li>
                <a href="<?= url('admin/plans') ?>"><?= l('admin_plans.breadcrumb') ?></a><i class="fas fa-fw fa-angle-right"></i>
            </li>
            <li class="active" aria-current="page"><?= l('admin_plan_update.breadcrumb') ?></li>
        </ol>
    </nav>
<?php endif ?>

<div class="d-flex justify-content-between mb-4">
    <h1 class="h3 mb-0 text-truncate"><i class="fas fa-fw fa-xs fa-box-open text-primary-900 mr-2"></i> <?= l('admin_plan_update.header') ?></h1>

    <?= include_view(THEME_PATH . 'views/admin/plans/admin_plan_dropdown_button.php', ['id' => $data->plan->plan_id]) ?>
</div>

<?= \Altum\Alerts::output_alerts() ?>

<div class="card">
    <div class="card-body">

        <form action="" method="post" role="form">
            <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" />
            <input type="hidden" name="type" value="update" />

            <?php if(is_numeric($data->plan_id)): ?>
                <div class="form-group">
                    <label for="plan_id"><?= l('admin_plans.plan_id') ?></label>
                    <input type="text" id="plan_id" name="plan_id" class="form-control <?= \Altum\Alerts::has_field_errors('plan_id') ? 'is-invalid' : null ?>" value="<?= $data->plan->plan_id ?>" disabled="disabled" />
                    <?= \Altum\Alerts::output_field_error('name') ?>
                </div>
            <?php endif ?>

            <div class="form-group">
                <label for="name"><i class="fas fa-fw fa-sm fa-signature text-muted mr-1"></i> <?= l('global.name') ?></label>
                <div class="input-group">
                    <input type="text" id="name" name="name" class="form-control <?= \Altum\Alerts::has_field_errors('name') ? 'is-invalid' : null ?>" value="<?= $data->plan->name ?>" maxlength="64" required="required" />
                    <div class="input-group-append">
                        <button class="btn btn-dark" type="button" data-toggle="collapse" data-target="#name_translate_container" aria-expanded="false" aria-controls="name_translate_container" data-tooltip title="<?= l('global.translate') ?>" data-tooltip-hide-on-click><i class="fas fa-fw fa-sm fa-language"></i></button>
                    </div>
                </div>
                <?= \Altum\Alerts::output_field_error('name') ?>
            </div>

            <div class="collapse" id="name_translate_container">
                <div class="p-3 bg-gray-50 rounded mb-4">
                    <?php foreach(\Altum\Language::$active_languages as $language_name => $language_code): ?>
                        <div class="form-group">
                            <label for="<?= 'translation_' . $language_name . '_name' ?>"><i class="fas fa-fw fa-sm fa-signature text-muted mr-1"></i> <?= l('global.name') ?></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><?= $language_name ?></span>
                                </div>
                                <input type="text" id="<?= 'translation_' . $language_name . '_name' ?>" name="<?= 'translations[' . $language_name . '][name]' ?>" value="<?= $data->plan->translations->{$language_name}->name ?? null ?>" class="form-control" maxlength="64" />
                            </div>
                        </div>
                    <?php endforeach ?>
                </div>
            </div>

            <div class="form-group">
                <label for="description"><i class="fas fa-fw fa-sm fa-pen text-muted mr-1"></i> <?= l('global.description') ?></label>
                <div class="input-group">
                    <input type="text" id="description" name="description" class="form-control <?= \Altum\Alerts::has_field_errors('description') ? 'is-invalid' : null ?>" value="<?= e($data->plan->description) ?>" maxlength="512" />
                    <div class="input-group-append">
                        <button class="btn btn-dark" type="button" data-toggle="collapse" data-target="#description_translate_container" aria-expanded="false" aria-controls="description_translate_container" data-tooltip title="<?= l('global.translate') ?>" data-tooltip-hide-on-click><i class="fas fa-fw fa-sm fa-language"></i></button>
                    </div>
                </div>
                <?= \Altum\Alerts::output_field_error('description') ?>
                <small class="form-text text-muted" data-toggle="tooltip" title="<?= l('admin_global.html_info_tooltip') ?>"><?= l('admin_global.html_info') ?></small>
            </div>

            <div class="collapse" id="description_translate_container">
                <div class="p-3 bg-gray-50 rounded mb-4">
                    <?php foreach(\Altum\Language::$active_languages as $language_name => $language_code): ?>
                        <div class="form-group">
                            <label for="<?= 'translation_' . $language_name . '_description' ?>"><i class="fas fa-fw fa-sm fa-pen text-muted mr-1"></i> <?= l('global.description') ?></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><?= $language_name ?></span>
                                </div>
                                <input type="text" id="<?= 'translation_' . $language_name . '_description' ?>" name="<?= 'translations[' . $language_name . '][description]' ?>" value="<?= e($data->plan->translations->{$language_name}->description ?? null) ?>" class="form-control" maxlength="512" />
                            </div>
                        </div>
                    <?php endforeach ?>
                </div>
            </div>

            <div class="row">
                <div class="col-12 col-lg-6">
                    <div class="form-group">
                        <label for="tag"><i class="fas fa-fw fa-sm fa-tag text-muted mr-1"></i> <?= l('admin_plans.tag') ?></label>
                        <div class="input-group">
                            <input type="text" id="tag" name="tag" class="form-control <?= \Altum\Alerts::has_field_errors('tag') ? 'is-invalid' : null ?>" value="<?= $data->plan->settings->tag ?? null ?>" maxlength="64" />
                            <div class="input-group-append">
                                <button class="btn btn-dark" type="button" data-toggle="collapse" data-target="#tag_translate_container" aria-expanded="false" aria-controls="tag_translate_container" data-tooltip title="<?= l('global.translate') ?>" data-tooltip-hide-on-click><i class="fas fa-fw fa-sm fa-language"></i></button>
                            </div>
                        </div>
                        <?= \Altum\Alerts::output_field_error('tag') ?>
                        <small class="form-text text-muted"><?= l('admin_plans.tag_help') ?></small>
                    </div>
                </div>

                <div class="col-6 col-lg-3">
                    <div class="form-group">
                        <label for="tag_background_color"><i class="fas fa-fw fa-palette fa-sm text-muted mr-1"></i> <?= l('admin_plans.tag_background_color') ?></label>
                        <input type="hidden" id="tag_background_color" name="tag_background_color" class="form-control" value="<?= $data->plan->additional_settings->tag_background_color ?? '' ?>" data-color-picker data-color-picker-has-clear="true" />
                    </div>
                </div>

                <div class="col-6 col-lg-3">
                    <div class="form-group">
                        <label for="tag_text_color"><i class="fas fa-fw fa-palette fa-sm text-muted mr-1"></i> <?= l('admin_plans.tag_text_color') ?></label>
                        <input type="hidden" id="tag_text_color" name="tag_text_color" class="form-control" value="<?= $data->plan->additional_settings->tag_text_color ?? '' ?>" data-color-picker data-color-picker-has-clear="true" />
                    </div>
                </div>
            </div>

            <div class="collapse" id="tag_translate_container">
                <div class="p-3 bg-gray-50 rounded mb-4">
                    <?php foreach(\Altum\Language::$active_languages as $language_name => $language_code): ?>
                        <div class="form-group">
                            <label for="<?= 'translation_' . $language_name . '_tag' ?>"><i class="fas fa-fw fa-sm fa-tag text-muted mr-1"></i> <?= l('admin_plans.tag') ?></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><?= $language_name ?></span>
                                </div>
                                <input type="text" id="<?= 'translation_' . $language_name . '_tag' ?>" name="<?= 'translations[' . $language_name . '][tag]' ?>" value="<?= $data->plan->translations->{$language_name}->tag ?? null ?>" class="form-control" maxlength="64" />
                            </div>
                        </div>
                    <?php endforeach ?>
                </div>
            </div>

            <?php if(in_array($data->plan_id, ['guest', 'free', 'custom'])): ?>
                <div class="form-group">
                    <label for="price"><i class="fas fa-fw fa-sm fa-dollar-sign text-muted mr-1"></i> <?= l('admin_plans.price') ?></label>
                    <div class="input-group">
                        <input type="text" id="price" name="price" class="form-control <?= \Altum\Alerts::has_field_errors('price') ? 'is-invalid' : null ?>" value="<?= $data->plan->price ?>" required="required" />
                        <div class="input-group-append">
                            <button class="btn btn-dark" type="button" data-toggle="collapse" data-target="#price_translate_container" aria-expanded="false" aria-controls="price_translate_container" data-tooltip title="<?= l('global.translate') ?>" data-tooltip-hide-on-click><i class="fas fa-fw fa-sm fa-language"></i></button>
                        </div>
                    </div>
                    <?= \Altum\Alerts::output_field_error('price') ?>
                </div>

                <div class="collapse" id="price_translate_container">
                    <div class="p-3 bg-gray-50 rounded mb-4">
                        <?php foreach(\Altum\Language::$active_languages as $language_name => $language_code): ?>
                            <div class="form-group">
                                <label for="<?= 'translation_' . $language_name . '_price' ?>"><i class="fas fa-fw fa-sm fa-dollar-sign text-muted mr-1"></i> <?= l('admin_plans.price') ?></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><?= $language_name ?></span>
                                    </div>
                                    <input type="text" id="<?= 'translation_' . $language_name . '_price' ?>" name="<?= 'translations[' . $language_name . '][price]' ?>" value="<?= $data->plan->translations->{$language_name}->price ?? null ?>" class="form-control" maxlength="256" />
                                </div>
                            </div>
                        <?php endforeach ?>
                    </div>
                </div>
            <?php endif ?>

            <?php if($data->plan_id == 'custom'): ?>
                <div class="form-group">
                    <label for="custom_button_url"><i class="fas fa-fw fa-sm fa-link text-muted mr-1"></i> <?= l('admin_plans.custom_button_url') ?></label>
                    <input type="text" id="custom_button_url" name="custom_button_url" class="form-control <?= \Altum\Alerts::has_field_errors('custom_button_url') ? 'is-invalid' : null ?>" value="<?= $data->plan->custom_button_url ?>" required="required" />
                    <?= \Altum\Alerts::output_field_error('custom_button_url') ?>
                </div>
            <?php endif ?>

            <?php if(is_numeric($data->plan_id)): ?>
                <div class="form-group">
                    <label for="order"><i class="fas fa-fw fa-sm fa-sort text-muted mr-1"></i> <?= l('global.order') ?></label>
                    <input id="order" type="number" min="0"  name="order" class="form-control" value="<?= $data->plan->order ?>" />
                    <small class="form-text text-muted"><?= l('global.order_int_help') ?></small>
                </div>

                <div class="form-group">
                    <label for="trial_days"><i class="fas fa-fw fa-sm fa-calendar-check text-muted mr-1"></i> <?= l('admin_plans.trial_days') ?></label>
                    <input id="trial_days" type="number" min="0" name="trial_days" class="form-control" value="<?= $data->plan->trial_days ?>" />
                    <div><small class="form-text text-muted"><?= l('admin_plans.trial_days_help') ?></small></div>
                </div>

                <?php foreach((array) settings()->payment->currencies as $currency => $currency_data): ?>
                    <div class="p-3 bg-gray-50 rounded mb-4">
                        <div class="row">
                            <div class="col-12 col-lg-4">
                                <div class="form-group">
                                    <label for="monthly_price[<?= $currency ?>]"><i class="fas fa-fw fa-sm fa-calendar-alt text-muted mr-1"></i> <?= l('admin_plans.monthly_price') ?></label>
                                    <div class="input-group input-group-sm">
                                        <input type="text" id="monthly_price[<?= $currency ?>]" name="monthly_price[<?= $currency ?>]" class="form-control form-control-sm <?= \Altum\Alerts::has_field_errors('monthly_price[' . $currency . ']') ? 'is-invalid' : null ?>" value="<?= $data->plan->prices->monthly->{$currency} ?? 0 ?>" required="required" />
                                        <div class="input-group-append">
                                            <span class="input-group-text"><?= $currency ?></span>
                                        </div>
                                    </div>
                                    <?= \Altum\Alerts::output_field_error('monthly_price[' . $currency . ']') ?>
                                    <small class="form-text text-muted"><?= sprintf(l('admin_plans.price_help'), l('admin_plans.monthly_price')) ?></small>
                                </div>
                            </div>

                            <div class="col-12 col-lg-4">
                                <div class="form-group">
                                    <label for="quarterly_price[<?= $currency ?>]"><i class="fas fa-fw fa-sm fa-calendar-alt text-muted mr-1"></i> <?= l('admin_plans.quarterly_price') ?></label>
                                    <div class="input-group input-group-sm">
                                        <input type="text" id="quarterly_price[<?= $currency ?>]" name="quarterly_price[<?= $currency ?>]" class="form-control form-control-sm <?= \Altum\Alerts::has_field_errors('quarterly_price[' . $currency . ']') ? 'is-invalid' : null ?>" value="<?= $data->plan->prices->quarterly->{$currency} ?? 0 ?>" required="required" />
                                        <div class="input-group-append">
                                            <span class="input-group-text"><?= $currency ?></span>
                                        </div>
                                    </div>
                                    <?= \Altum\Alerts::output_field_error('quarterly_price[' . $currency . ']') ?>
                                    <small class="form-text text-muted"><?= sprintf(l('admin_plans.price_help'), l('admin_plans.quarterly_price')) ?></small>
                                </div>
                            </div>

                            <div class="col-12 col-lg-4">
                                <div class="form-group">
                                    <label for="biannual_price[<?= $currency ?>]"><i class="fas fa-fw fa-sm fa-calendar-alt text-muted mr-1"></i> <?= l('admin_plans.biannual_price') ?></label>
                                    <div class="input-group input-group-sm">
                                        <input type="text" id="biannual_price[<?= $currency ?>]" name="biannual_price[<?= $currency ?>]" class="form-control form-control-sm <?= \Altum\Alerts::has_field_errors('biannual_price[' . $currency . ']') ? 'is-invalid' : null ?>" value="<?= $data->plan->prices->biannual->{$currency} ?? 0 ?>" required="required" />
                                        <div class="input-group-append">
                                            <span class="input-group-text"><?= $currency ?></span>
                                        </div>
                                    </div>
                                    <?= \Altum\Alerts::output_field_error('biannual_price[' . $currency . ']') ?>
                                    <small class="form-text text-muted"><?= sprintf(l('admin_plans.price_help'), l('admin_plans.biannual_price')) ?></small>
                                </div>
                            </div>

                            <div class="col-12 col-lg-4">
                                <div class="form-group">
                                    <label for="annual_price[<?= $currency ?>]"><i class="fas fa-fw fa-sm fa-calendar text-muted mr-1"></i> <?= l('admin_plans.annual_price') ?></label>
                                    <div class="input-group input-group-sm">
                                        <input type="text" id="annual_price[<?= $currency ?>]" name="annual_price[<?= $currency ?>]" class="form-control form-control-sm <?= \Altum\Alerts::has_field_errors('annual_price[' . $currency . ']') ? 'is-invalid' : null ?>" value="<?= $data->plan->prices->annual->{$currency} ?? 0 ?>" required="required" />
                                        <div class="input-group-append">
                                            <span class="input-group-text"><?= $currency ?></span>
                                        </div>
                                    </div>
                                    <?= \Altum\Alerts::output_field_error('annual_price[' . $currency . ']') ?>
                                    <small class="form-text text-muted"><?= sprintf(l('admin_plans.price_help'), l('admin_plans.annual_price')) ?></small>
                                </div>
                            </div>

                            <div class="col-12 col-lg-4">
                                <div class="form-group">
                                    <label for="lifetime_price[<?= $currency ?>]"><i class="fas fa-fw fa-sm fa-infinity text-muted mr-1"></i> <?= l('admin_plans.lifetime_price') ?></label>
                                    <div class="input-group input-group-sm">
                                        <input type="text" id="lifetime_price[<?= $currency ?>]" name="lifetime_price[<?= $currency ?>]" class="form-control form-control-sm <?= \Altum\Alerts::has_field_errors('lifetime_price[' . $currency . ']') ? 'is-invalid' : null ?>" value="<?= $data->plan->prices->lifetime->{$currency} ?? 0 ?>" required="required" />
                                        <div class="input-group-append">
                                            <span class="input-group-text"><?= $currency ?></span>
                                        </div>
                                    </div>
                                    <?= \Altum\Alerts::output_field_error('lifetime_price[' . $currency . ']') ?>
                                    <small class="form-text text-muted"><?= sprintf(l('admin_plans.price_help'), l('admin_plans.lifetime_price')) ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach ?>

                <div class="form-group">
                    <label for="taxes_ids"><i class="fas fa-fw fa-sm fa-paperclip text-muted mr-1"></i> <?= l('admin_plans.taxes_ids') ?></label>
                    <select id="taxes_ids" name="taxes_ids[]" class="custom-select" multiple="multiple">
                        <?php if($data->taxes): ?>
                            <?php foreach($data->taxes as $tax): ?>
                                <option value="<?= $tax->tax_id ?>" <?= in_array($tax->tax_id, $data->plan->taxes_ids)  ? 'selected="selected"' : null ?>>
                                    <?= $tax->name . ' - ' . $tax->description ?>
                                </option>
                            <?php endforeach ?>
                        <?php endif ?>
                    </select>
                    <small class="form-text text-muted"><?= sprintf(l('admin_plans.taxes_ids_help'), '<a href="' . url('admin/taxes') .'">', '</a>') ?></small>
                    <?php if(get_this_device_type() == 'desktop'): ?><small class="form-text text-muted"><?= l('global.select_multiple_help') ?></small><?php endif ?>
                </div>

                <div class="form-group">
                    <label for="custom_redirect_url"><i class="fas fa-fw fa-sm fa-link text-muted mr-1"></i> <?= l('admin_plans.custom_redirect_url') ?></label>
                    <input type="url" id="custom_redirect_url" name="custom_redirect_url" class="form-control <?= \Altum\Alerts::has_field_errors('custom_redirect_url') ? 'is-invalid' : null ?>" value="<?= $data->plan->settings->custom_redirect_url ?? null ?>" />
                    <?= \Altum\Alerts::output_field_error('custom_redirect_url') ?>
                    <small class="form-text text-muted"><?= l('admin_plans.custom_redirect_url_help') ?></small>
                </div>

            <?php endif ?>

            <div class="form-group">
                <label for="color"><i class="fas fa-fw fa-sm fa-palette text-muted mr-1"></i> <?= l('admin_plans.color') ?></label>
                <input type="hidden" id="color" name="color" class="form-control <?= \Altum\Alerts::has_field_errors('color') ? 'is-invalid' : null ?>" value="<?= $data->plan->color ?>" placeholder="<?= l('admin_plans.color_placeholder') ?>" data-color-picker data-color-picker-has-clear="true" />
                <?= \Altum\Alerts::output_field_error('color') ?>
                <small class="form-text text-muted"><?= l('admin_plans.color_help') ?></small>
            </div>

            <div class="form-group">
                <label for="suggested_plan_id"><i class="fas fa-fw fa-sm fa-arrow-up text-muted mr-1"></i> <?= l('admin_plans.suggested_plan_id') ?></label>
                <select id="suggested_plan_id" name="suggested_plan_id" class="custom-select">
                    <option value="" <?= $data->plan->additional_settings->suggested_plan_id == '' ? 'selected="selected"' : null ?>><?= l('global.none') ?></option>
                    <?php foreach($data->plans as $plan): ?>
                    <option value="<?= $plan->plan_id ?>" <?= $data->plan->additional_settings->suggested_plan_id == $plan->plan_id ? 'selected="selected"' : null ?> <?= $data->plan->plan_id == 'guest' ? 'disabled="disabled"' : null ?>><?= $plan->name ?></option>
                    <?php endforeach ?>
                </select>
                <small class="form-text text-muted"><?= l('admin_plans.suggested_plan_id_help') ?></small>
            </div>

            <div class="form-group">
                <label for="suggested_plan_code_id"><i class="fas fa-fw fa-sm fa-tag text-muted mr-1"></i> <?= l('admin_plans.suggested_plan_code_id') ?></label>
                <select id="suggested_plan_code_id" name="suggested_plan_code_id" class="custom-select">
                    <option value="" <?= $data->plan->additional_settings->suggested_plan_code_id == '' ? 'selected="selected"' : null ?>><?= l('global.none') ?></option>
                    <?php foreach($data->codes as $code): ?>
                        <option value="<?= $code->code_id ?>" <?= $data->plan->additional_settings->suggested_plan_code_id == $code->code_id ? 'selected="selected"' : null ?> <?= $data->plan->plan_id == 'guest' ? 'disabled="disabled"' : null ?>><?= $code->code . ' - ' . $code->discount . '%' ?></option>
                    <?php endforeach ?>
                </select>
                <small class="form-text text-muted"><?= l('admin_plans.suggested_plan_code_id_help') ?></small>
            </div>

            <div class="form-group">
                <label for="status"><i class="fas fa-fw fa-sm fa-circle-dot text-muted mr-1"></i> <?= l('global.status') ?></label>
                <select id="status" name="status" class="custom-select">
                    <option value="1" <?= $data->plan->status == 1 ? 'selected="selected"' : null ?>><?= l('global.active') ?></option>
                    <option value="0" <?= $data->plan->status == 0 ? 'selected="selected"' : null ?> <?= $data->plan->plan_id == 'custom' ? 'disabled="disabled"' : null ?>><?= l('global.disabled') ?></option>
                    <option value="2" <?= $data->plan->status == 2 ? 'selected="selected"' : null ?>><?= l('global.hidden') ?></option>
                </select>
            </div>

            <h2 class="h4 mt-5 mb-4"><?= l('admin_plans.plan.header') ?></h2>

            <div>
                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="url_minimum_characters"><?= l('admin_plans.plan.url_minimum_characters') ?></label>
                            <input type="number" id="url_minimum_characters" name="url_minimum_characters" min="1" class="form-control" value="<?= $data->plan->settings->url_minimum_characters ?? 1 ?>" />
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="url_maximum_characters"><?= l('admin_plans.plan.url_maximum_characters') ?></label>
                            <input type="number" id="url_maximum_characters" name="url_maximum_characters" min="1" max="256" class="form-control" value="<?= $data->plan->settings->url_maximum_characters ?? 64 ?>" />
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="storage_size_limit"><?= l('admin_plans.plan.storage_size_limit') ?></label>
                    <div class="input-group">
                        <input type="number" id="storage_size_limit" name="storage_size_limit" min="-1" class="form-control" value="<?= $data->plan->settings->storage_size_limit ?>" <?= $data->plan_id == 'guest' ? 'disabled="disabled"' : null ?> />
                        <div class="input-group-append">
                            <span class="input-group-text"><?= l('global.mb') ?></span>
                        </div>
                    </div>
                    <small class="form-text text-muted"><?= l('admin_plans.plan.unlimited') ?></small>
                </div>

                <div class="form-group">
                    <label for="transfers_limit"><?= l('admin_plans.plan.transfers_limit') ?></label>
                    <input type="number" id="transfers_limit" name="transfers_limit" min="-1" <?= $data->plan_id == 'guest' ? 'max="0"' : null ?> class="form-control" value="<?= $data->plan->settings->transfers_limit ?>" />
                    <small class="form-text text-muted"><?= l('admin_plans.plan.unlimited') ?> <?= l('admin_plans.plan.transfers_limit_help') ?></small>
                </div>

                <div class="form-group">
                    <label for="transfer_requests_limit"><?= l('admin_plans.plan.transfer_requests_limit') ?></label>
                    <input type="number" id="transfer_requests_limit" name="transfer_requests_limit" min="-1" <?= $data->plan_id == 'guest' ? 'max="0"' : null ?> class="form-control" value="<?= $data->plan->settings->transfer_requests_limit ?>" />
                    <small class="form-text text-muted"><?= l('admin_plans.plan.unlimited') ?> <?= l('admin_plans.plan.transfer_requests_limit_help') ?></small>
                </div>

                <div class="form-group">
                    <label for="transfer_size_limit"><?= l('admin_plans.plan.transfer_size_limit') ?></label>
                    <div class="input-group">
                        <input type="number" id="transfer_size_limit" name="transfer_size_limit" min="-1" class="form-control" value="<?= $data->plan->settings->transfer_size_limit ?>" />
                        <div class="input-group-append">
                            <span class="input-group-text"><?= l('global.mb') ?></span>
                        </div>
                    </div>
                    <small class="form-text text-muted"><?= l('admin_plans.plan.unlimited') ?></small>
                </div>

                <div class="form-group">
                    <label for="files_per_transfer_limit"><?= l('admin_plans.plan.files_per_transfer_limit') ?></label>
                    <input type="number" id="files_per_transfer_limit" name="files_per_transfer_limit" min="-1" class="form-control" value="<?= $data->plan->settings->files_per_transfer_limit ?>" />
                    <small class="form-text text-muted"><?= l('admin_plans.plan.unlimited') ?></small>
                </div>

                <div class="form-group">
                    <label for="downloads_per_transfer_limit"><?= l('admin_plans.plan.downloads_per_transfer_limit') ?></label>
                    <input type="number" id="downloads_per_transfer_limit" name="downloads_per_transfer_limit" min="-1" class="form-control" value="<?= $data->plan->settings->downloads_per_transfer_limit ?>" />
                    <small class="form-text text-muted"><?= l('admin_plans.plan.unlimited') ?></small>
                </div>

                <div class="form-group">
                    <label for="transfers_retention"><?= l('admin_plans.plan.transfers_retention') ?></small></label>
                    <div class="input-group">
                        <input type="number" id="transfers_retention" name="transfers_retention" min="-1" class="form-control" value="<?= $data->plan->settings->transfers_retention ?>" />
                        <div class="input-group-append">
                            <span class="input-group-text"><?= l('global.date.days') ?></span>
                        </div>
                    </div>
                    <small class="form-text text-muted"><?= l('admin_plans.plan.transfers_retention_help') ?><?= l('admin_plans.plan.unlimited') ?></small>
                </div>

                <div class="form-group">
                    <label for="download_unlocking_time"><?= l('admin_plans.plan.download_unlocking_time') ?></small></label>
                    <input type="number" id="download_unlocking_time" name="download_unlocking_time" min="-1" class="form-control" value="<?= $data->plan->settings->download_unlocking_time ?>" />
                    <small class="form-text text-muted"><?= l('admin_plans.plan.download_unlocking_time_help') ?></small>
                </div>

                <div class="form-group">
                    <label for="projects_limit"><?= l('admin_plans.plan.projects_limit') ?></label>
                    <input type="number" id="projects_limit" name="projects_limit" min="-1" class="form-control" value="<?= $data->plan->settings->projects_limit ?>" <?= $data->plan_id == 'guest' ? 'disabled="disabled"' : null ?> />
                    <small class="form-text text-muted"><?= l('admin_plans.plan.unlimited') ?></small>
                </div>

                <div class="form-group">
                    <label for="pixels_limit"><?= l('admin_plans.plan.pixels_limit') ?></label>
                    <input type="number" id="pixels_limit" name="pixels_limit" min="-1" class="form-control" value="<?= $data->plan->settings->pixels_limit ?>" <?= $data->plan_id == 'guest' ? 'disabled="disabled"' : null ?> />
                    <small class="form-text text-muted"><?= l('admin_plans.plan.unlimited') ?></small>
                </div>

                <div class="form-group">
                    <label for="domains_limit"><?= l('admin_plans.plan.domains_limit') ?></label>
                    <input type="number" id="domains_limit" name="domains_limit" min="-1" class="form-control" value="<?= $data->plan->settings->domains_limit ?>" <?= $data->plan_id == 'guest' ? 'disabled="disabled"' : null ?> />
                    <small class="form-text text-muted"><?= l('admin_plans.plan.unlimited') ?></small>
                </div>

                <?php if(\Altum\Plugin::is_active('teams')): ?>
                    <div class="form-group">
                        <label for="teams_limit"><?= l('admin_plans.plan.teams_limit') ?></label>
                        <input type="number" id="teams_limit" name="teams_limit" min="-1" class="form-control" value="<?= $data->plan->settings->teams_limit ?>" <?= $data->plan_id == 'guest' ? 'disabled="disabled"' : null ?> />
                        <small class="form-text text-muted"><?= l('admin_plans.plan.unlimited') ?></small>
                    </div>

                    <div class="form-group">
                        <label for="team_members_limit"><?= l('admin_plans.plan.team_members_limit') ?></label>
                        <input type="number" id="team_members_limit" name="team_members_limit" min="-1" class="form-control" value="<?= $data->plan->settings->team_members_limit ?>" <?= $data->plan_id == 'guest' ? 'disabled="disabled"' : null ?> />
                        <small class="form-text text-muted"><?= l('admin_plans.plan.unlimited') ?></small>
                    </div>
                <?php endif ?>

                <?php if(\Altum\Plugin::is_active('affiliate')): ?>
                    <div class="form-group">
                        <label for="affiliate_commission_percentage"><?= l('admin_plans.plan.affiliate_commission_percentage') ?></label>
                        <input type="number" id="affiliate_commission_percentage" name="affiliate_commission_percentage" min="0" max="100" class="form-control" value="<?= $data->plan->settings->affiliate_commission_percentage ?>" <?= $data->plan_id == 'guest' ? 'disabled="disabled"' : null ?> />
                        <small class="form-text text-muted"><?= l('admin_plans.plan.affiliate_commission_percentage_help') ?></small>
                    </div>
                <?php endif ?>

                <div class="form-group">
                    <label for="statistics_retention"><?= l('admin_plans.plan.statistics_retention') ?></small></label>
                    <div class="input-group">
                        <input type="number" id="statistics_retention" name="statistics_retention" min="-1" class="form-control" value="<?= $data->plan->settings->statistics_retention ?>" <?= $data->plan_id == 'guest' ? 'disabled="disabled"' : null ?> />
                        <div class="input-group-append">
                            <span class="input-group-text"><?= l('global.date.days') ?></span>
                        </div>
                    </div>
                    <small class="form-text text-muted"><?= l('admin_plans.plan.statistics_retention_help') ?></small>
                </div>

                <div class="form-group">
                    <label for="additional_domains"><?= l('admin_plans.plan.additional_domains') ?></label>
                    <select id="additional_domains" name="additional_domains[]" class="custom-select" multiple="multiple">
                        <?php foreach($data->additional_domains as $domain): ?>
                            <option value="<?= $domain->domain_id ?>" <?= in_array($domain->domain_id, $data->plan->settings->additional_domains ?? [])  ? 'selected="selected"' : null ?>>
                                <?= $domain->host ?>
                            </option>
                        <?php endforeach ?>
                    </select>
                    <?php if(get_this_device_type() == 'desktop'): ?><small class="form-text text-muted"><?= l('global.select_multiple_help') ?></small><?php endif ?>
                </div>

                <div class="form-group custom-control custom-switch">
                    <input id="no_ads" name="no_ads" type="checkbox" class="custom-control-input" <?= $data->plan->settings->no_ads ? 'checked="checked"' : null ?>>
                    <label class="custom-control-label" for="no_ads"><?= l('admin_plans.plan.no_ads') ?></label>
                    <div><small class="form-text text-muted"><?= l('admin_plans.plan.no_ads_help') ?></small></div>
                </div>

                <div class="form-group custom-control custom-switch">
                    <input id="analytics_is_enabled" name="analytics_is_enabled" type="checkbox" class="custom-control-input" <?= $data->plan->settings->analytics_is_enabled ? 'checked="checked"' : null ?>>
                    <label class="custom-control-label" for="analytics_is_enabled"><?= l('admin_plans.plan.analytics_is_enabled') ?></label>
                    <div><small class="form-text text-muted"><?= l('admin_plans.plan.analytics_is_enabled_help') ?></small></div>
                </div>

                <div class="form-group custom-control custom-switch">
                    <input id="custom_url_is_enabled" name="custom_url_is_enabled" type="checkbox" class="custom-control-input" <?= $data->plan->settings->custom_url_is_enabled ? 'checked="checked"' : null ?>>
                    <label class="custom-control-label" for="custom_url_is_enabled"><?= l('admin_plans.plan.custom_url_is_enabled') ?></label>
                    <div><small class="form-text text-muted"><?= l('admin_plans.plan.custom_url_is_enabled_help') ?></small></div>
                </div>

                <div class="form-group custom-control custom-switch">
                    <input id="password_protection_is_enabled" name="password_protection_is_enabled" type="checkbox" class="custom-control-input" <?= $data->plan->settings->password_protection_is_enabled ? 'checked="checked"' : null ?>>
                    <label class="custom-control-label" for="password_protection_is_enabled"><?= l('admin_plans.plan.password_protection_is_enabled') ?></label>
                    <div><small class="form-text text-muted"><?= l('admin_plans.plan.password_protection_is_enabled_help') ?></small></div>
                </div>

                <div class="form-group custom-control custom-switch">
                    <input id="file_encryption_is_enabled" name="file_encryption_is_enabled" type="checkbox" class="custom-control-input" <?= $data->plan->settings->file_encryption_is_enabled ? 'checked="checked"' : null ?>>
                    <label class="custom-control-label" for="file_encryption_is_enabled"><?= l('admin_plans.plan.file_encryption_is_enabled') ?></label>
                    <div><small class="form-text text-muted"><?= l('admin_plans.plan.file_encryption_is_enabled_help') ?></small></div>
                </div>

                <div class="form-group custom-control custom-switch">
                    <input id="removable_branding_is_enabled" name="removable_branding_is_enabled" type="checkbox" class="custom-control-input" <?= $data->plan->settings->removable_branding_is_enabled ? 'checked="checked"' : null ?>>
                    <label class="custom-control-label" for="removable_branding_is_enabled"><?= l('admin_plans.plan.removable_branding_is_enabled') ?></label>
                    <div><small class="form-text text-muted"><?= l('admin_plans.plan.removable_branding_is_enabled_help') ?></small></div>
                </div>

                <div class="form-group custom-control custom-switch">
                    <input id="custom_css_is_enabled" name="custom_css_is_enabled" type="checkbox" class="custom-control-input" <?= $data->plan->settings->custom_css_is_enabled ? 'checked="checked"' : null ?>>
                    <label class="custom-control-label" for="custom_css_is_enabled"><?= l('admin_plans.plan.custom_css_is_enabled') ?></label>
                    <div><small class="form-text text-muted"><?= l('admin_plans.plan.custom_css_is_enabled_help') ?></small></div>
                </div>

                <div class="form-group custom-control custom-switch">
                    <input id="custom_js_is_enabled" name="custom_js_is_enabled" type="checkbox" class="custom-control-input" <?= $data->plan->settings->custom_js_is_enabled ? 'checked="checked"' : null ?>>
                    <label class="custom-control-label" for="custom_js_is_enabled"><?= l('admin_plans.plan.custom_js_is_enabled') ?></label>
                    <div><small class="form-text text-muted"><?= l('admin_plans.plan.custom_js_is_enabled_help') ?></small></div>
                </div>

                <div class="form-group custom-control custom-switch">
                    <input id="api_is_enabled" name="api_is_enabled" type="checkbox" class="custom-control-input" <?= $data->plan->settings->api_is_enabled ? 'checked="checked"' : null ?> <?= $data->plan_id == 'guest' ? 'disabled="disabled"' : null ?>>
                    <label class="custom-control-label" for="api_is_enabled"><?= l('admin_plans.plan.api_is_enabled') ?></label>
                    <div><small class="form-text text-muted"><?= l('admin_plans.plan.api_is_enabled_help') ?></small></div>
                </div>

                <div class="form-group custom-control custom-switch">
                    <input id="white_labeling_is_enabled" name="white_labeling_is_enabled" type="checkbox" class="custom-control-input" <?= $data->plan->settings->white_labeling_is_enabled ? 'checked="checked"' : null ?> <?= $data->plan_id == 'guest' ? 'disabled="disabled"' : null ?>>
                    <label class="custom-control-label" for="white_labeling_is_enabled"><?= l('admin_plans.plan.white_labeling_is_enabled') ?></label>
                    <div><small class="form-text text-muted"><?= l('admin_plans.plan.white_labeling_is_enabled_help') ?></small></div>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-5 mb-3">
                    <h3 class="h5"><?= l('admin_plans.plan.export') ?></h3>

                    <div>
                        <button type="button" class="btn btn-sm btn-light" data-toggle="tooltip" title="<?= l('global.select_all') ?>" data-tooltip-hide-on-click onclick="document.querySelectorAll(`[name='export[]']`).forEach(element => element.checked ? null : element.checked = true)"><i class="fas fa-fw fa-check-square"></i></button>
                        <button type="button" class="btn btn-sm btn-light" data-toggle="tooltip" title="<?= l('global.deselect_all') ?>" data-tooltip-hide-on-click onclick="document.querySelectorAll(`[name='export[]']`).forEach(element => element.checked ? element.checked = false : null)"><i class="fas fa-fw fa-minus-square"></i></button>
                    </div>
                </div>

                <div class="form-group custom-control custom-checkbox">
                    <input id="export_csv" name="export[]" value="csv" type="checkbox" class="custom-control-input" <?= $data->plan->settings->export->csv ? 'checked="checked"' : null ?>>
                    <label class="custom-control-label" for="export_csv"><?= sprintf(l('global.export_to'), 'CSV') ?></label>
                </div>

                <div class="form-group custom-control custom-checkbox">
                    <input id="export_json" name="export[]" value="json" type="checkbox" class="custom-control-input" <?= $data->plan->settings->export->json ? 'checked="checked"' : null ?>>
                    <label class="custom-control-label" for="export_json"><?= sprintf(l('global.export_to'), 'JSON') ?></label>
                </div>

                <div class="form-group custom-control custom-checkbox">
                    <input id="export_pdf" name="export[]" value="pdf" type="checkbox" class="custom-control-input" <?= $data->plan->settings->export->pdf ? 'checked="checked"' : null ?>>
                    <label class="custom-control-label" for="export_pdf"><?= sprintf(l('global.export_to'), 'PDF') ?></label>
                </div>

                <h2 class="h5 mt-5 mb-4"><?= l('admin_plans.plan.notification_handlers_limit') ?></h2>

                <div class="form-group">
                    <label for="active_notification_handlers_per_resource_limit"><?= l('admin_plans.plan.active_notification_handlers_per_resource_limit') ?></label>
                    <input type="number" id="active_notification_handlers_per_resource_limit" name="active_notification_handlers_per_resource_limit" min="-1" class="form-control" value="<?= $data->plan->settings->active_notification_handlers_per_resource_limit ?>" <?= $data->plan_id == 'guest' ? 'disabled="disabled"' : null ?> />
                    <small class="form-text text-muted"><?= l('admin_plans.plan.unlimited') ?></small>
                </div>

                <div class="row">
                    <?php foreach(array_keys(require APP_PATH . 'includes/notification_handlers.php') as $notification_handler): ?>
                        <div class="col-12 col-lg-6">
                            <div class="form-group">
                                <label for="<?= 'notification_handlers_' . $notification_handler . '_limit' ?>"><?= l('notification_handlers.type_' . $notification_handler) ?></label>
                                <input type="number" id="<?= 'notification_handlers_' . $notification_handler . '_limit' ?>" name="<?= 'notification_handlers_' . $notification_handler . '_limit' ?>" min="-1" class="form-control" value="<?= $data->plan->settings->{'notification_handlers_' . $notification_handler . '_limit'} ?>" <?= $data->plan_id == 'guest' ? 'disabled="disabled"' : null ?> />
                                <small class="form-text text-muted"><?= l('admin_plans.plan.unlimited') ?></small>
                            </div>
                        </div>
                    <?php endforeach ?>
                </div>
            </div>

            <?php if($data->plan_id == 'custom'): ?>
                <div class="alert alert-warning" role="alert"><?= l('admin_plans.custom_help') ?></div>
                <button type="submit" name="submit" class="btn btn-lg btn-block btn-primary mt-4"><?= l('global.update') ?></button>
            <?php elseif($data->plan_id == 'guest'): ?>
                <button type="submit" name="submit" class="btn btn-lg btn-block btn-primary mt-4"><?= l('global.update') ?></button>
            <?php else: ?>
                <button type="submit" name="submit" class="btn btn-lg btn-block btn-primary mt-4"><?= l('global.update') ?></button>
                <button type="submit" name="submit_update_users_plan_settings" class="btn btn-lg btn-block btn-outline-primary mt-2"><?= l('admin_plan_update.update_users_plan_settings.button') ?></button>
            <?php endif ?>
        </form>

    </div>
</div>

<?= include_view(THEME_PATH . 'views/partials/scroll_top_bottom.php', ['top_selector' => '.admin-content', 'bottom_selector' => 'footer']) ?>
<?php include_view(THEME_PATH . 'views/partials/clipboard_js.php') ?>
<?php include_view(THEME_PATH . 'views/partials/color_picker_js.php') ?>
