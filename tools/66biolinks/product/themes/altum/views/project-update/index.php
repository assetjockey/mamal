<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <?php if(settings()->main->breadcrumbs_is_enabled): ?>
        <nav aria-label="breadcrumb">
            <ol class="custom-breadcrumbs small">
                <li>
                    <a href="<?= url('projects') ?>"><?= l('projects.breadcrumb') ?></a><i class="fas fa-fw fa-angle-right"></i>
                </li>
                <li class="active" aria-current="page"><?= l('project_update.breadcrumb') ?></li>
            </ol>
        </nav>
    <?php endif ?>

    <div class="d-flex justify-content-between align-items-end mb-4">
        <h1 class="h4 text-truncate mb-0"><i class="fas fa-fw fa-xs fa-project-diagram mr-1"></i> <?= l('project_update.header') ?></h1>

        <?= include_view(THEME_PATH . 'views/projects/project_dropdown_button.php', ['id' => $data->project->project_id, 'resource_name' => $data->project->name]) ?>
    </div>

    <div class="card">
        <div class="card-body">

            <form action="" method="post" role="form">
                <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" />

                <div class="form-group">
                    <label for="name"><i class="fas fa-fw fa-signature fa-sm text-muted mr-1"></i> <?= l('global.name') ?></label>
                    <input type="text" id="name" name="name" class="form-control" value="<?= $data->project->name ?>" maxlength="64" required="required" />
                </div>

                <div class="form-group">
                    <label for="color"><i class="fas fa-fw fa-palette fa-sm text-muted mr-1"></i> <?= l('projects.color') ?></label>
                    <input type="hidden" id="color" name="color" class="form-control" value="<?= $data->project->color ?>" required="required" data-color-picker />
                    <small class="text-muted form-text"><?= l('projects.color_help') ?></small>
                </div>

                <?php if((settings()->links->projects_email_reports_is_enabled ?? false) && settings()->notification_handlers->is_enabled): ?>
                    <div <?= $this->user->plan_settings->email_reports_is_enabled ? null : get_plan_feature_disabled_info() ?>>
                        <div class="form-group <?= $this->user->plan_settings->email_reports_is_enabled ? null : 'container-disabled' ?>">
                            <div class="d-flex flex-wrap flex-row justify-content-between">
                                <label><i class="fas fa-fw fa-sm fa-envelope-open-text text-muted mr-1"></i> <?= l('global.plan_settings.email_reports_is_enabled_' . settings()->links->projects_email_reports_is_enabled) ?></label>
                                <a href="<?= url('notification-handler-create') ?>" target="_blank" class="small mb-2"><i class="fas fa-fw fa-sm fa-plus mr-1"></i> <?= l('notification_handlers.create') ?></a>
                            </div>
                            <div class="mb-2"><small class="text-muted"><?= l('projects.email_reports_is_enabled_help') ?></small></div>

                            <div class="row">
                                <?php foreach($data->notification_handlers as $notification_handler): ?>
                                    <?php if($notification_handler->type != 'email') continue ?>
                                    <div class="col-12 col-lg-6">
                                        <div class="custom-control custom-checkbox my-2">
                                            <input id="<?= 'email_reports_' . $notification_handler->notification_handler_id ?>" name="email_reports[]" value="<?= $notification_handler->notification_handler_id ?>" type="checkbox" class="custom-control-input" <?= in_array($notification_handler->notification_handler_id, $data->project->email_reports) ? 'checked="checked"' : null ?>>
                                            <label class="custom-control-label" for="<?= 'email_reports_' . $notification_handler->notification_handler_id ?>">
                                                <span class="mr-1"><?= $notification_handler->name ?></span>
                                                <small class="badge badge-light badge-pill"><?= l('notification_handlers.type_' . $notification_handler->type) ?></small>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach ?>
                            </div>
                        </div>
                    </div>
                <?php endif ?>

                <button type="submit" name="submit" class="btn btn-block btn-primary"><?= l('global.update') ?></button>
            </form>

        </div>
    </div>
</div>



<?php include_view(THEME_PATH . 'views/partials/color_picker_js.php') ?>
