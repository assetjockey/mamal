<?php defined('ALTUMCODE') || die() ?>

<div id="audits">
    <button class="btn btn-block btn-gray-200 font-size-little-small font-weight-450 mb-4" type="button" data-toggle="collapse" data-target="#requests_container" aria-expanded="false" aria-controls="requests_container">
        <i class="fas fa-fw fa-circle-nodes fa-sm mr-1"></i> <?= l('admin_settings.audits.requests') ?>
    </button>

    <div class="collapse" data-parent="#audits" id="requests_container">
        <div class="form-group">
            <label for="accept_encoding"><?= l('admin_settings.audits.accept_encoding') ?></label>
            <input id="accept_encoding" type="text" name="accept_encoding" class="form-control" value="<?= settings()->audits->accept_encoding ?>" />
            <small class="form-text text-muted"><?= l('admin_settings.audits.accept_encoding_help') ?></small>
        </div>

        <div class="form-group">
            <label for="user_agent"><?= l('admin_settings.audits.user_agent') ?></label>
            <input id="user_agent" type="text" name="user_agent" class="form-control" value="<?= settings()->audits->user_agent ?>" />
            <small class="form-text text-muted"><?= l('admin_settings.audits.user_agent_help') ?></small>
        </div>

        <div class="form-group">
            <label for="request_timeout"><?= l('admin_settings.audits.request_timeout') ?></label>
            <div class="input-group">
                <input id="request_timeout" type="number" min="1" name="request_timeout" class="form-control" value="<?= settings()->audits->request_timeout ?>" />
                <div class="input-group-append">
                    <span class="input-group-text"><?= l('global.date.seconds') ?></span>
                </div>
            </div>
            <small class="form-text text-muted"><?= l('admin_settings.audits.request_timeout_help') ?></small>
        </div>

        <div class="form-group custom-control custom-switch">
            <input id="double_check_is_enabled" name="double_check_is_enabled" type="checkbox" class="custom-control-input" <?= settings()->audits->double_check_is_enabled ? 'checked="checked"' : null?>>
            <label class="custom-control-label" for="double_check_is_enabled"><?= l('admin_settings.audits.double_check_is_enabled') ?></label>
            <small class="form-text text-muted"><?= l('admin_settings.audits.double_check_is_enabled_help') ?></small>
        </div>

        <div class="form-group">
            <label for="double_check_wait"><?= l('admin_settings.audits.double_check_wait') ?></label>
            <div class="input-group">
                <input id="double_check_wait" type="number" min="0" max="5" name="double_check_wait" class="form-control" value="<?= settings()->audits->double_check_wait ?>" />
                <div class="input-group-append">
                    <span class="input-group-text"><?= l('global.date.seconds') ?></span>
                </div>
            </div>
            <small class="form-text text-muted"><?= l('admin_settings.audits.double_check_wait_help') ?></small>
        </div>

        <div class="form-group">
            <label for="blacklisted_domains"><?= l('admin_settings.audits.blacklisted_domains') ?></label>
            <textarea id="blacklisted_domains" class="form-control" name="blacklisted_domains"><?= implode(',', settings()->audits->blacklisted_domains) ?></textarea>
            <small class="form-text text-muted"><?= l('admin_settings.audits.blacklisted_domains_help') ?></small>
        </div>

        <div class="form-group">
            <label for="bulk_processing_before_queue"><?= l('admin_settings.audits.bulk_processing_before_queue') ?></label>
            <input id="bulk_processing_before_queue" type="number" min="1" max="15" name="bulk_processing_before_queue" class="form-control" value="<?= settings()->audits->bulk_processing_before_queue ?? 3 ?>" required="required" />
            <small class="form-text text-muted"><?= l('admin_settings.audits.bulk_processing_before_queue_help') ?></small>
        </div>
    </div>

    <button class="btn btn-block btn-gray-200 font-size-little-small font-weight-450 mb-4" type="button" data-toggle="collapse" data-target="#proxies_container" aria-expanded="false" aria-controls="proxies_container">
        <i class="fas fa-fw fa-server fa-sm mr-1"></i> <?= l('admin_settings.audits.proxies') ?>
    </button>

    <div class="collapse" data-parent="#audits" id="proxies_container">
        <div class="form-group custom-control custom-switch">
            <input id="proxies_is_enabled" name="proxies_is_enabled" type="checkbox" class="custom-control-input" <?= settings()->audits->proxies_is_enabled ? 'checked="checked"' : null?>>
            <label class="custom-control-label" for="proxies_is_enabled"><?= l('admin_settings.audits.proxies_is_enabled') ?></label>
            <small class="form-text text-muted"><?= l('admin_settings.audits.proxies_is_enabled_help') ?></small>
        </div>


        <div class="form-group custom-control custom-switch">
            <input id="proxies_exclusive" name="proxies_exclusive" type="checkbox" class="custom-control-input" <?= settings()->audits->proxies_exclusive ? 'checked="checked"' : null?>>
            <label class="custom-control-label" for="proxies_exclusive"><?= l('admin_settings.audits.proxies_exclusive') ?></label>
            <small class="form-text text-muted"><?= l('admin_settings.audits.proxies_exclusive_help') ?></small>
        </div>

        <label><?= l('admin_settings.audits.proxies') ?></label>
        <div id="proxies">
			<?php foreach(settings()->audits->proxies ?? [] as $key => $proxy): ?>
                <div class="proxy p-3 bg-gray-50 rounded mb-4">

                    <div class="d-flex align-items-center justify-content-between">
                        <div class="form-group custom-control custom-switch">
                            <input id="<?= 'is_enabled[' . $key . ']' ?>" name="<?= 'is_enabled[' . $key . ']' ?>" type="checkbox" class="custom-control-input" <?= $proxy->is_enabled ? 'checked="checked"' : null?>>
                            <label class="custom-control-label" for="<?= 'is_enabled[' . $key . ']' ?>"><?= l('admin_settings.audits.proxy_is_enabled') ?></label>
                        </div>

                        <div>
                            <button type="submit" name="proxy_test" value="<?= $key ?>" class="btn btn-sm btn-outline-primary"><?= l('admin_settings.audits.proxy_test') ?></button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="<?= 'name[' . $key . ']' ?>"><i class="fas fa-fw fa-sm fa-signature text-muted mr-1"></i> <?= l('global.name') ?></label>
                        <input id="<?= 'name[' . $key . ']' ?>" type="text" name="name[<?= $key ?>]" class="form-control" value="<?= $proxy->name ?>" required="required" />
                    </div>

                    <div class="form-group">
                        <label for="<?= 'type[' . $key . ']' ?>"><i class="fas fa-fw fa-sm fa-fingerprint text-muted mr-1"></i> <?= l('global.type') ?></label>
                        <select id="<?= 'type[' . $key . ']' ?>" name="<?= 'type[' . $key . ']' ?>" class="custom-select">
                            <option value="http" <?= $proxy->type =='http' ? 'selected="selected"' : null ?>>HTTP / HTTPS</option>
                            <option value="socks5" <?= $proxy->type =='socks5' ? 'selected="selected"' : null ?>>SOCKS5</option>
                            <option value="socks5_rdns" <?= $proxy->type =='socks5_rdns' ? 'selected="selected"' : null ?>>SOCKS5 (Remote DNS)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="<?= 'address[' . $key . ']' ?>"><i class="fas fa-fw fa-sm fa-globe text-muted mr-1"></i> <?= l('admin_settings.audits.proxy_address') ?></label>
                        <input id="<?= 'address[' . $key . ']' ?>" type="text" name="<?= 'address[' . $key . ']' ?>" class="form-control" value="<?= $proxy->address ?>" placeholder="<?= l('admin_settings.audits.proxy_address_placeholder') ?>" required="required" />
                    </div>

                    <button type="button" data-remove="proxies" class="btn btn-block btn-outline-danger"><i class="fas fa-fw fa-times fa-sm mr-1"></i> <?= l('global.delete') ?></button>
                </div>
			<?php endforeach ?>
        </div>

        <div class="mb-4">
            <button data-add="proxies" type="button" class="btn btn-block btn-outline-success"><i class="fas fa-fw fa-plus-circle fa-sm mr-1"></i> <?= l('global.create') ?></button>
        </div>
    </div>

    <button class="btn btn-block btn-gray-200 font-size-little-small font-weight-450 mb-4" type="button" data-toggle="collapse" data-target="#domains_container" aria-expanded="false" aria-controls="domains_container">
        <i class="fas fa-fw fa-globe fa-sm mr-1"></i> <?= l('admin_settings.audits.domains') ?>
    </button>

    <div class="collapse" data-parent="#audits" id="domains_container">
        <div class="form-group custom-control custom-switch">
            <input id="domains_is_enabled" name="domains_is_enabled" type="checkbox" class="custom-control-input" <?= settings()->audits->domains_is_enabled ? 'checked="checked"' : null?>>
            <label class="custom-control-label" for="domains_is_enabled"><?= l('admin_settings.audits.domains_is_enabled') ?></label>
            <small class="form-text text-muted"><?= l('admin_settings.audits.domains_is_enabled_help') ?></small>
        </div>

        <div class="form-group">
            <label for="domains_custom_main_ip"><?= l('admin_settings.audits.domains_custom_main_ip') ?></label>
            <input id="domains_custom_main_ip" name="domains_custom_main_ip" type="text" class="form-control" value="<?= settings()->audits->domains_custom_main_ip ?>" placeholder="<?= $_SERVER['SERVER_ADDR'] ?>">
            <small class="form-text text-muted"><?= l('admin_settings.audits.domains_custom_main_ip_help') ?></small>
        </div>
    </div>

    <button class="btn btn-block btn-gray-200 font-size-little-small font-weight-450 mb-4" type="button" data-toggle="collapse" data-target="#audits_container" aria-expanded="false" aria-controls="audits_container">
        <i class="fas fa-fw fa-bolt fa-sm mr-1"></i> <?= l('admin_settings.audits.audits') ?>
    </button>

    <div class="collapse" data-parent="#audits" id="audits_container">
        <div class="form-group">
            <label for="excluded_words"><?= l('admin_settings.audits.excluded_words') ?></label>
            <textarea id="excluded_words" class="form-control" name="excluded_words"><?= implode(',', settings()->audits->excluded_words ?? []) ?></textarea>
            <small class="form-text text-muted"><?= l('admin_settings.audits.excluded_words_help') ?></small>
        </div>

        <div class="form-group mt-5">
			<?php $available_tests = require APP_PATH . 'includes/available_audit_tests.php'; ?>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="h5"><?= l('admin_settings.audits.available_tests') . ' (' . count($available_tests) . ')' ?></h3>

                <div>
                    <button type="button" class="btn btn-sm btn-light" data-toggle="tooltip" title="<?= l('global.select_all') ?>" data-tooltip-hide-on-click onclick="document.querySelectorAll(`[name='available_tests[]']`).forEach(element => element.checked ? null : element.checked = true)"><i class="fas fa-fw fa-check-square"></i></button>
                    <button type="button" class="btn btn-sm btn-light" data-toggle="tooltip" title="<?= l('global.deselect_all') ?>" data-tooltip-hide-on-click onclick="document.querySelectorAll(`[name='available_tests[]']`).forEach(element => element.checked ? element.checked = false : null)"><i class="fas fa-fw fa-minus-square"></i></button>
                </div>
            </div>

            <div class="row">
				<?php foreach($available_tests as $key => $value): ?>
                    <div class="col-12 col-lg-6">
                        <div class="custom-control custom-checkbox my-2">
                            <input id="<?= 'test_' . $key ?>" name="available_tests[]" value="<?= $key ?>" type="checkbox" class="custom-control-input" <?= settings()->audits->available_tests->{$key} ? 'checked="checked"' : null ?>>
                            <label class="custom-control-label d-flex align-items-center" for="<?= 'test_' . $key ?>">
								<?= l('audits.test.' . $key) ?>
                            </label>
                        </div>
                    </div>
				<?php endforeach ?>
            </div>
        </div>

        <div class="form-group">
            <label for="example_url"><?= l('admin_settings.audits.example_url') ?></label>
            <input id="example_url" type="url" name="example_url" class="form-control" placeholder="<?= l('global.url_placeholder') ?>" value="<?= settings()->audits->example_url ?>" />
            <small class="form-text text-muted"><?= l('admin_settings.audits.example_url_help') ?></small>
        </div>

        <div class="form-group">
            <label for="google_safe_browsing_api_key"><?= l('admin_settings.audits.google_safe_browsing_api_key') ?></label>
            <input id="google_safe_browsing_api_key" type="text" name="google_safe_browsing_api_key" class="form-control" value="<?= settings()->audits->google_safe_browsing_api_key ?>" />
            <small class="form-text text-muted"><?= l('admin_settings.audits.google_safe_browsing_api_key_help') ?></small>
        </div>
    </div>

    <button class="btn btn-block btn-gray-200 font-size-little-small font-weight-450 mb-4" type="button" data-toggle="collapse" data-target="#ai_container" aria-expanded="false" aria-controls="ai_container">
        <i class="fas fa-fw fa-robot fa-sm mr-1"></i> <?= l('admin_settings.audits.ai') ?>
    </button>

    <div class="collapse" data-parent="#audits" id="ai_container">
        <div class="form-group custom-control custom-switch">
            <input id="ai_is_enabled" name="ai_is_enabled" type="checkbox" class="custom-control-input" <?= settings()->audits->ai_is_enabled ? 'checked="checked"' : null?>>
            <label class="custom-control-label" for="ai_is_enabled"><?= l('admin_settings.audits.ai_is_enabled') ?></label>
            <small class="form-text text-muted"><?= l('admin_settings.audits.ai_is_enabled_help') ?></small>
        </div>

        <div class="form-group">
            <label for="openai_api_url"><i class="fas fa-fw fa-sm fa-link text-muted mr-1"></i> <?= l('admin_settings.audits.openai_api_url') ?></label>
            <input id="openai_api_url" type="text" name="openai_api_url" class="form-control" value="<?= settings()->audits->openai_api_url ?>" />
            <small class="form-text text-muted"><?= l('admin_settings.audits.openai_api_url_help') ?></small>
        </div>

        <div class="form-group">
            <label for="openai_api_key"><i class="fas fa-fw fa-sm fa-key text-muted mr-1"></i> <?= l('admin_settings.audits.openai_api_key') ?></label>
            <input id="openai_api_key" type="text" name="openai_api_key" class="form-control" value="<?= settings()->audits->openai_api_key ?>" />
            <small class="form-text text-muted"><?= l('admin_settings.audits.openai_api_key_help') ?></small>
        </div>

        <div class="form-group">
            <label for="openai_webhook_secret_key"><i class="fas fa-fw fa-sm fa-lock text-muted mr-1"></i> <?= l('admin_settings.audits.openai_webhook_secret_key') ?></label>
            <input id="openai_webhook_secret_key" type="text" name="openai_webhook_secret_key" class="form-control" value="<?= settings()->audits->openai_webhook_secret_key ?>" />
            <small class="form-text text-muted"><?= l('admin_settings.audits.openai_webhook_secret_key_help') ?></small>
        </div>

        <div class="form-group">
            <label for="openai_model"><i class="fas fa-fw fa-sm fa-robot text-muted mr-1"></i> <?= l('admin_settings.audits.openai_model') ?></label>
            <input id="openai_model" type="text" name="openai_model" class="form-control" value="<?= settings()->audits->openai_model ?>" />
            <small class="form-text text-muted"><?= sprintf(l('admin_settings.audits.openai_model_help'), '<code data-copy>' . implode('</code>, <code data-copy>', ['gpt-5.2','gpt-5.1','gpt-5','gpt-5-mini','gpt-5-nano','gpt-4.1','gpt-4.1-mini']) . '</code>') ?></small>
        </div>

        <div class="form-group">
            <label for="openai_webhook_url"><i class="fas fa-fw fa-sm fa-link text-muted mr-1"></i> <?= l('admin_settings.audits.openai_webhook_url') ?></label>
            <input type="text" id="openai_webhook_url" value="<?= SITE_URL . 'webhook-audit-ai' ?>" class="form-control" onclick="this.select();" readonly="readonly" />
            <small class="form-text text-muted"><?= sprintf(l('admin_settings.audits.openai_webhook_url_help'), '<code>response.completed</code>') ?></small>
        </div>
    </div>

    <button class="btn btn-block btn-gray-200 font-size-little-small font-weight-450 mb-4" type="button" data-toggle="collapse" data-target="#directory_container" aria-expanded="false" aria-controls="directory_container">
        <i class="fas fa-fw fa-sitemap fa-sm mr-1"></i> <?= l('admin_settings.audits.directory') ?>
    </button>

    <div class="collapse" data-parent="#audits" id="directory_container">
        <div class="form-group custom-control custom-switch">
            <input id="directory_is_enabled" name="directory_is_enabled" type="checkbox" class="custom-control-input" <?= settings()->audits->directory_is_enabled ? 'checked="checked"' : null?>>
            <label class="custom-control-label" for="directory_is_enabled"><?= l('admin_settings.audits.directory_is_enabled') ?></label>
            <small class="form-text text-muted"><?= l('admin_settings.audits.directory_is_enabled_help') ?></small>
        </div>

        <div class="form-group">
            <label for="directory_access"><?= l('admin_settings.audits.directory_access') ?></label>
            <select id="directory_access" name="directory_access" class="custom-select">
                <option value="everyone" <?= settings()->audits->directory_access == 'everyone' ? 'selected="selected"' : null ?>><?= l('admin_settings.audits.directory_access_everyone') ?></option>
                <option value="users" <?= settings()->audits->directory_access == 'users' ? 'selected="selected"' : null ?>><?= l('admin_settings.audits.directory_access_users') ?></option>
            </select>
        </div>
    </div>

    <button class="btn btn-block btn-gray-200 font-size-little-small font-weight-450 mb-4" type="button" data-toggle="collapse" data-target="#cron_settings_container" aria-expanded="false" aria-controls="cron_settings_container">
        <i class="fas fa-fw fa-arrows-rotate fa-sm mr-1"></i> <?= l('admin_settings.cron.cron_settings') ?>
    </button>

    <div class="collapse" data-parent="#audits" id="cron_settings_container">
        <div class="alert alert-danger mb-3"><?= l('admin_settings.cron.cron_settings_help') ?></div>

        <div class="form-group">
            <label for="audits_per_cron"><?= l('admin_settings.audits.audits_per_cron') ?></label>
            <input id="audits_per_cron" type="number" min="0" name="audits_per_cron" class="form-control" value="<?= settings()->audits->audits_per_cron ?? 30 ?>" />
        </div>
    </div>
</div>

<button type="submit" name="submit" class="btn btn-lg btn-block btn-primary mt-4"><?= l('global.update') ?></button>

<template id="template_proxies">
    <div class="proxy p-3 bg-gray-50 rounded mb-4">
        <div class="form-group custom-control custom-switch">
            <input id="is_enabled" name="is_enabled[]" type="checkbox" class="custom-control-input" checked="checked" />
            <label class="custom-control-label" for="is_enabled"><?= l('admin_settings.audits.proxy_is_enabled') ?></label>
        </div>

        <div class="form-group">
            <label for="name"><i class="fas fa-fw fa-sm fa-signature text-muted mr-1"></i> <?= l('global.name') ?></label>
            <input id="name" type="text" name="name[]" class="form-control" required="required" />
        </div>

        <div class="form-group">
            <label for="type"><i class="fas fa-fw fa-sm fa-fingerprint text-muted mr-1"></i> <?= l('global.type') ?></label>
            <select id="type" name="type[]" class="custom-select">
                <option value="http" selected="selected">HTTP / HTTPS</option>
                <option value="socks5">SOCKS5</option>
                <option value="socks5_rdns">SOCKS5 (Remote DNS)</option>
            </select>
        </div>

        <div class="form-group">
            <label for="address"><i class="fas fa-fw fa-sm fa-globe text-muted mr-1"></i> <?= l('admin_settings.audits.proxy_address') ?></label>
            <input id="address" type="text" name="address[]" class="form-control" placeholder="<?= l('admin_settings.audits.proxy_address_placeholder') ?>" required="required" />
        </div>

        <button type="button" data-remove class="btn btn-block btn-outline-danger"><i class="fas fa-fw fa-times"></i> <?= l('global.delete') ?></button>
    </div>
</template>

<?php ob_start() ?>
<script>
    'use strict';

    /* add new request header */
    let add = event => {
        let type = event.currentTarget.getAttribute('data-add');
        let clone = document.querySelector(`#template_${type}`).content.cloneNode(true);

        let proxies_count = document.querySelectorAll(`#${type} .proxy`).length;

        clone.querySelectorAll(`input`).forEach(element => {
            element.name = element.name.replace('[]', `[${proxies_count}]`);
            element.closest('.form-group').querySelector('label').setAttribute('for', `${element.id}[${proxies_count}]`);
            element.id = `${element.id}[${proxies_count}]`;
        });

        document.querySelector(`#${type}`).appendChild(clone);

        remove_initiator();
    };

    document.querySelectorAll('[data-add]').forEach(element => {
        element.addEventListener('click', add);
    })

    /* remove request header */
    let remove = event => {
        event.currentTarget.closest('.proxy').remove();
    };

    let remove_initiator = () => {
        document.querySelectorAll('#proxies [data-remove]').forEach(element => {
            element.removeEventListener('click', remove);
            element.addEventListener('click', remove)
        })
    };

    remove_initiator();
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>

<?php include_view(THEME_PATH . 'views/partials/clipboard_js.php') ?>
