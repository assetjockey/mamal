<?php defined('ALTUMCODE') || die() ?>

<?php
$filters_clear_filters = isset($_COOKIE['filters']) ? json_decode($_COOKIE['filters']) : null;
$filters_clear_is_enabled = false;

if(is_array($filters_clear_filters)) {
    foreach($filters_clear_filters as $filters_clear_filter) {
        if(isset($filters_clear_filter->by, $filters_clear_filter->rule, $filters_clear_filter->value) && trim((string) $filters_clear_filter->value) !== '') {
            $filters_clear_is_enabled = true;
            break;
        }
    }
}
?>

<div id="filters" class="card border-0 my-4" style="display: none;">
    <div class="card-body">

        <div class="row justify-content-between mb-4">
            <div class="col-12 col-md-auto">
                <h2 class="h6"><?= l('analytics.filters.header') ?></h2>
            </div>

            <div class="col-12 col-md-auto dropdown">
                <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-toggle="dropdown" data-boundary="viewport" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-fw fa-plus-circle fa-sm mr-1"></i> <?= l('analytics.filters.create') ?>
                </button>

                <div id="create_filters_list" class="dropdown-menu dropdown-menu-right">
                    <?php /* Visitor based filters */ ?>
                    <?php if(!$data->available_filters || $data->available_filters == 'websites_visitors' || $data->available_filters == 'lightweight_events'): ?>
                        <h6 class="dropdown-header"><?= l('analytics.visitors') ?></h6>
                        <?php /* IP is not stored on lightweight events */ ?>
                        <?php if((!$data->available_filters || $data->available_filters == 'websites_visitors') && settings()->analytics->ip_storage_is_enabled): ?>
                            <button type="button" class="dropdown-item btn-sm" data-filter-by="ip"><i class="fas fa-fw fa-sm fa-terminal mr-2"></i> <?= l('global.ip') ?></button>
                        <?php endif ?>
                        <button type="button" class="dropdown-item btn-sm" data-filter-by="continent_code"><i class="fas fa-fw fa-sm fa-globe-europe mr-2"></i> <?= l('analytics.filters.by.continent_code') ?></button>
                        <button type="button" class="dropdown-item btn-sm" data-filter-by="country_code"><i class="fas fa-fw fa-sm fa-flag mr-2"></i> <?= l('analytics.filters.by.country_code') ?></button>
                        <button type="button" class="dropdown-item btn-sm" data-filter-by="region_name"><i class="fas fa-fw fa-sm fa-location-dot mr-2"></i> <?= l('global.region') ?></button>
                        <button type="button" class="dropdown-item btn-sm" data-filter-by="city_name"><i class="fas fa-fw fa-sm fa-city mr-2"></i> <?= l('analytics.filters.by.city_name') ?></button>
                        <button type="button" class="dropdown-item btn-sm" data-filter-by="screen_resolution"><i class="fas fa-fw fa-sm fa-desktop mr-2"></i> <?= l('analytics.filters.by.screen_resolution') ?></button>
                        <button type="button" class="dropdown-item btn-sm" data-filter-by="browser_language"><i class="fas fa-fw fa-sm fa-language mr-2"></i> <?= l('analytics.filters.by.browser_language') ?></button>
                        <button type="button" class="dropdown-item btn-sm" data-filter-by="browser_timezone"><i class="fas fa-fw fa-sm fa-user-clock mr-2"></i> <?= l('analytics.filters.by.browser_timezone') ?></button>
                        <button type="button" class="dropdown-item btn-sm" data-filter-by="os_name"><i class="fas fa-fw fa-sm fa-server mr-2"></i> <?= l('analytics.filters.by.os_name') ?></button>
                        <button type="button" class="dropdown-item btn-sm" data-filter-by="device_type"><i class="fas fa-fw fa-sm fa-laptop mr-2"></i> <?= l('analytics.filters.by.device_type') ?></button>
                        <button type="button" class="dropdown-item btn-sm" data-filter-by="browser_name"><i class="fas fa-fw fa-sm fa-window-restore mr-2"></i> <?= l('analytics.filters.by.browser_name') ?></button>
                        <?php /* Theme is available for visitors and lightweight events */ ?>
                        <?php if(!$data->available_filters || $data->available_filters == 'websites_visitors' || $data->available_filters == 'lightweight_events'): ?>
                            <button type="button" class="dropdown-item btn-sm" data-filter-by="theme"><i class="fas fa-fw fa-sm fa-moon mr-2"></i> <?= l('analytics.filters.by.theme') ?></button>
                        <?php endif ?>
                        <?php /* Custom parameters are only stored on visitors */ ?>
                        <?php if((!$data->available_filters || $data->available_filters == 'websites_visitors') && $data->tracking_type != 'lightweight'): ?>
                            <button type="button" class="dropdown-item btn-sm" data-filter-by="custom_parameters_key"><i class="fas fa-fw fa-sm fa-fingerprint mr-2"></i> <?= l('analytics.filters.by.custom_parameters_key') ?></button>
                            <button type="button" class="dropdown-item btn-sm" data-filter-by="custom_parameters_value"><i class="fas fa-fw fa-sm fa-fingerprint mr-2"></i> <?= l('analytics.filters.by.custom_parameters_value') ?></button>
                        <?php endif ?>
                    <?php endif ?>

                    <?php /* Pageview based filters */ ?>
                    <?php if(!$data->available_filters || $data->available_filters == 'sessions_events' || $data->available_filters == 'lightweight_events'): ?>
                        <h6 class="dropdown-header"><?= l('analytics.pageviews') ?></h6>
                        <button type="button" class="dropdown-item btn-sm" data-filter-by="type"><i class="fas fa-fw fa-sm fa-eye mr-2"></i> <?= l('analytics.filters.by.type') ?></button>
                        <button type="button" class="dropdown-item btn-sm" data-filter-by="path"><i class="fas fa-fw fa-sm fa-copy mr-2"></i> <?= l('analytics.filters.by.path') ?></button>
                        <?php if($data->tracking_type != 'lightweight'): ?>
                            <button type="button" class="dropdown-item btn-sm" data-filter-by="title"><i class="fas fa-fw fa-sm fa-heading mr-2"></i> <?= l('analytics.filters.by.title') ?></button>
                        <?php endif ?>
                        <button type="button" class="dropdown-item btn-sm" data-filter-by="referrer_host"><i class="fas fa-fw fa-sm fa-random mr-2"></i> <?= l('analytics.filters.by.referrer_host') ?></button>
                        <button type="button" class="dropdown-item btn-sm" data-filter-by="referrer_path"><i class="fas fa-fw fa-sm fa-random mr-2"></i> <?= l('analytics.filters.by.referrer_path') ?></button>
                        <button type="button" class="dropdown-item btn-sm" data-filter-by="utm_source"><i class="fas fa-fw fa-sm fa-link mr-2"></i> <?= l('analytics.filters.by.utm_source') ?></button>
                        <button type="button" class="dropdown-item btn-sm" data-filter-by="utm_medium"><i class="fas fa-fw fa-sm fa-link mr-2"></i> <?= l('analytics.filters.by.utm_medium') ?></button>
                        <button type="button" class="dropdown-item btn-sm" data-filter-by="utm_campaign"><i class="fas fa-fw fa-sm fa-link mr-2"></i> <?= l('analytics.filters.by.utm_campaign') ?></button>
                    <?php endif ?>
                </div>
            </div>
        </div>

        <form id="filters_form" action="" method="post" role="form">
            <div id="filters_list"></div>

            <button type="submit" name="submit" class="btn btn-sm btn-block btn-primary mt-4"><?= l('global.update') ?></button>
            <button type="button" id="filters_clear" class="btn btn-sm btn-block btn-outline-secondary mt-2" <?= $filters_clear_is_enabled ? null : 'disabled="disabled"' ?>><?= l('global.filters.clear') ?></button>
        </form>
    </div>
</div>

<template id="template_filter">
    <div class="filter">
        <input type="hidden" name="filter_by[]" value="" />

        <div class="row mb-4 mb-md-3">
            <div class="col-12 col-lg-3 mb-3 mb-lg-0 d-flex align-items-center">
                <span id="template_filter_by_display" class="font-weight-bold font-size-small"></span>
            </div>

            <div class="col-12 col-lg-3 mb-3 mb-lg-0">
                <select name="filter_rule[]" class="custom-select custom-select-sm">
                    <option value="is"><?= l('analytics.filters.rule.is') ?></option>
                    <option value="is_not"><?= l('analytics.filters.rule.is_not') ?></option>
                    <option value="contains"><?= l('analytics.filters.rule.contains') ?></option>
                    <option value="starts_with"><?= l('analytics.filters.rule.starts_with') ?></option>
                    <option value="ends_with"><?= l('analytics.filters.rule.ends_with') ?></option>
                </select>
            </div>

            <div class="col-12 col-lg-4 mb-3 mb-lg-0">
                <input type="text" name="filter_value[]" class="form-control form-control-sm ml-lg-3" />
            </div>

            <div class="col-12 col-lg-2 d-flex">
                <button type="button" class="btn btn-sm btn-block btn-outline-danger ml-lg-3 align-self-center filter_delete" title="<?= l('global.delete') ?>"><i class="fas fa-fw fa-times fa-sm"></i></button>
            </div>
        </div>
    </div>
</template>

<?php ob_start() ?>
<script>
    'use strict';

    /* Populate with already existing filters */
    let filters_cookie = get_cookie('filters');
    let template = document.querySelector('#template_filter');

    if(filters_cookie) {
        let filters = JSON.parse(filters_cookie);

        let filters_to_show = 0;

        for(let filter of filters) {
            /* Prepare template */
            let clone = template.content.cloneNode(true);

            let filter_origin = $(`button[data-filter-by="${filter.by}"]`);


            /* Add the data in the template */
            $(clone).find('#template_filter_by_display').html(filter_origin.html());
            $(clone).find('[name="filter_by\[\]"]').val(filter.by);
            $(clone).find(`[name="filter_rule\[\]"] option[value="${filter.rule}"]`).attr('selected', 'selected');
            $(clone).find('[name="filter_value\[\]"]').val(filter.value);

            /* Hide the filter if it shouldn't show */
            if(!filter_origin.length) {
                $(clone).find('.filter').hide();
            }

            else {
                filters_to_show++;
            }

            $('#filters_list').append(clone);
        }

        if(filters_to_show && !get_cookie('dashboard_view_id')) {
            $('#filters').show();

            /* Refresh tooltips */
            tooltips_initiate();
        }
    }

    /* Create new filter handler */
    $('#create_filters_list > button').on('click', event => {

        let template = document.querySelector('#template_filter');

        /* Prepare template */
        let clone = template.content.cloneNode(true);

        $(clone).find('#template_filter_by_display').html($(event.currentTarget).html());
        $(clone).find('[name="filter_by\[\]"]').val($(event.currentTarget).data('filter-by'));

        /* Add */
        $('#filters_list').append(clone);

        /* Initiate handlers */
        initiate_delete_handler();

        /* Refresh tooltips */
        tooltips_initiate();
    });

    /* Delete handler */
    let initiate_delete_handler = () => {
        $('.filter_delete').off().on('click', event => {
            $(event.currentTarget).tooltip('hide');

            $(event.currentTarget).closest('.filter').remove();

            event.preventDefault();
        });
    };

    /* Initiate handlers */
    initiate_delete_handler();

    /* Handling the form submission */
    $('#filters_form').on('submit', event => {

        let form = $(event.currentTarget).serializeArray();
        let filters = [];

        for(let i = 0; i <= form.length -1; i += 3) {

            filters.push({
                by: form[i].value,
                rule: form[i+1].value,
                value: form[i+2].value
            })

        }

        /* Set the cookie */
        set_cookie('filters', JSON.stringify(filters), 30, <?= json_encode(COOKIE_PATH) ?>);
        delete_cookie('dashboard_view_id', <?= json_encode(COOKIE_PATH) ?>);

        /* Refresh */
        window.location.href = window.location.href;

        event.preventDefault();
    });

    $('#filters_clear').on('click', event => {

        /* Clear the cookie */
        delete_cookie('filters', <?= json_encode(COOKIE_PATH) ?>);
        delete_cookie('dashboard_view_id', <?= json_encode(COOKIE_PATH) ?>);

        /* Refresh */
        window.location.href = window.location.href;

        event.preventDefault();
    });

    let get_row_filters = element => {
        return $(element).attr('data-add-filters') ? JSON.parse($(element).attr('data-add-filters')) : [{
            by: $(element).attr('data-filter-by'),
            rule: $(element).attr('data-filter-rule'),
            value: $(element).attr('data-filter-value')
        }];
    };

    let row_filter_exists = (filters, filter) => {
        for(let existing_filter of filters) {
            if(existing_filter.by == filter.by && existing_filter.rule == filter.rule && existing_filter.value == filter.value) {
                return true;
            }
        }

        return false;
    };

    let row_filters_exist = (filters, filters_to_check) => {
        for(let filter of filters_to_check) {
            if(!row_filter_exists(filters, filter)) {
                return false;
            }
        }

        return true;
    };

    let process_row_filters = () => {
        let filters_cookie = get_cookie('filters');
        let filters = filters_cookie ? JSON.parse(filters_cookie) : [];

        $('[data-add-filter], [data-add-filters]').each((index, element) => {
            let filters_to_add = get_row_filters(element);
            let has_filters = row_filters_exist(filters, filters_to_add);
            let title = has_filters ? <?= json_encode(l('analytics.filters.remove')) ?> : <?= json_encode(l('analytics.filters.create')) ?>;

            element.setAttribute('title', title);
            element.setAttribute('data-original-title', title);
            element.innerHTML = `<i class="fas fa-fw fa-xs ${has_filters ? 'fa-times' : 'fa-filter'}"></i>`;
        });
    };

    let tooltips_initiate_original = tooltips_initiate;
    tooltips_initiate = () => {
        process_row_filters();
        tooltips_initiate_original();
    };

    process_row_filters();

    /* Toggle a filter directly from statistic rows */
    $(document).on('click', '[data-add-filter], [data-add-filters]', event => {
        $(event.currentTarget).tooltip('hide');

        let filters_to_add = get_row_filters(event.currentTarget);
        let filters_cookie = get_cookie('filters');
        let filters = filters_cookie ? JSON.parse(filters_cookie) : [];
        let has_filters = row_filters_exist(filters, filters_to_add);

        if(has_filters) {
            let filters_filtered = [];

            for(let existing_filter of filters) {
                let remove_filter = false;

                for(let filter of filters_to_add) {
                    if(existing_filter.by == filter.by && existing_filter.rule == filter.rule && existing_filter.value == filter.value) {
                        remove_filter = true;
                    }
                }

                if(!remove_filter) {
                    filters_filtered.push(existing_filter);
                }
            }

            filters = filters_filtered;
        }

        else {
            for(let filter of filters_to_add) {
                if(!row_filter_exists(filters, filter)) {
                    filters.push(filter);
                }
            }
        }

        /* Set the cookie */
        set_cookie('filters', JSON.stringify(filters), 30, <?= json_encode(COOKIE_PATH) ?>);
        delete_cookie('dashboard_view_id', <?= json_encode(COOKIE_PATH) ?>);

        /* Refresh */
        window.location.href = window.location.href;

        event.preventDefault();
    });
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
