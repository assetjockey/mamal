<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <div class="mb-4">
        <h1 class="h4 m-0"><i class="fas fa-fw fa-xs fa-user-shield mr-1"></i> <?= l('teams_system.header') ?></h1>
    </div>

    <div class="card d-flex flex-row h-100 overflow-hidden mb-4">
        <div class="pl-4 d-flex flex-column justify-content-center">
            <div class="p-2 rounded-2x api-widget-icon d-flex align-items-center justify-content-center bg-primary-100">
                <a href="<?= url('teams') ?>" class="stretched-link">
                    <i class="fas fa-fw fa-user-cog text-primary-600"></i>
                </a>
            </div>
        </div>

        <div class="card-body d-flex justify-content-between align-items-center">
            <div class="d-flex flex-column">
                <span class="font-weight-bold"><?= l('teams.menu') ?></span>
                <span class="small text-muted"><?= l('teams.subheader') ?></span>
            </div>

            <div>
                <span class="badge badge-light"><?= nr($data->total_teams) ?></span>
            </div>
        </div>
    </div>

    <?php if (!empty($data->teams)): ?>
        <div class="mb-5">
            <div class="table-responsive table-custom-container">
                <table class="table table-custom">
                    <thead>
                    <tr>
                        <th><?= l('global.name') ?></th>
                        <th><?= l('teams.table.members') ?></th>
                        <th></th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach($data->teams as $row): ?>
                        <tr>
                            <td class="text-nowrap">
                                <a href="<?= url('team/' . $row->team_id) ?>"><?= $row->name ?></a>
                            </td>

                            <td class="text-nowrap">
                                <?php if($row->members): ?>

                                    <div class="d-flex align-items-center">
                                        <?php $i = 0 ?>

                                        <?php foreach($row->members_preview as $member): ?>
                                            <div class="dropdown" style="line-height: 1;">
                                                <a href="#" class="dropdown-toggle dropdown-toggle-simple" data-toggle="dropdown" data-boundary="viewport" style="<?= $i++ > 0 ? 'margin-left: -.9rem' : null; ?>">
                                                    <img src="<?= get_user_avatar(null, $member->user_email) ?>" class="team-user-avatar-small rounded-circle" data-tooltip title="<?= $member->user_email ?>" alt="" />
                                                </a>

                                                <div class="dropdown-menu dropdown-menu-right">
                                                    <a class="dropdown-item" href="<?= url('team-member-update/' . $member->team_member_id) ?>"><i class="fas fa-fw fa-sm fa-pencil-alt mr-2"></i> <?= l('global.edit') ?></a>
                                                    <a href="#" data-toggle="modal" data-target="#team_member_delete_modal" data-team-member-id="<?= $member->team_member_id ?>" data-resource-name="<?= $member->user_email ?>" class="dropdown-item"><i class="fas fa-fw fa-sm fa-trash-alt mr-2"></i> <?= l('global.delete') ?></a>
                                                </div>
                                            </div>
                                        <?php endforeach ?>

                                        <?php if($row->members > 5): ?>
                                            <a href="<?= url('team/' . $row->team_id) ?>" class="text-muted text-decoration-none small ml-1">
                                                <?= sprintf(l('team.members_preview_more'), ($row->members - 5)) ?>
                                            </a>
                                        <?php endif ?>
                                    </div>

                                <?php else: ?>
                                    <span class="badge badge-light">
                                        <?= l('global.none') ?>
                                    </span>
                                <?php endif ?>
                            </td>

                            <td class="text-nowrap">
                                <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.datetime_tooltip'), '<br />' . \Altum\Date::get($row->datetime, 2) . '<br /><small>' . \Altum\Date::get($row->datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->datetime) . ')</small>') ?>">
                                    <i class="fas fa-fw fa-calendar text-muted"></i>
                                </span>

                                <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.last_datetime_tooltip'), ($row->last_datetime ? '<br />' . \Altum\Date::get($row->last_datetime, 2) . '<br /><small>' . \Altum\Date::get($row->last_datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->last_datetime) . ')</small>' : '<br />' . l('global.na'))) ?>">
                                    <i class="fas fa-fw fa-history text-muted"></i>
                                </span>
                            </td>

                            <td>
                                <div class="d-flex justify-content-end">
                                    <?= include_view(THEME_PATH . 'views/team/team_dropdown_button.php', ['id' => $row->team_id, 'resource_name' => $row->name]) ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach ?>
                    </tbody>
                </table>
            </div>

            <div class="mt-3"><?= $data->teams_pagination ?></div>
        </div>
    <?php endif ?>

    <div class="card d-flex flex-row h-100 overflow-hidden mb-4">
        <div class="pl-4 d-flex flex-column justify-content-center">
            <div class="p-2 rounded-2x api-widget-icon d-flex align-items-center justify-content-center bg-primary-100">
                <a href="<?= url('teams-member') ?>" class="stretched-link">
                    <i class="fas fa-fw fa-user-tag text-primary-600"></i>
                </a>
            </div>
        </div>

        <div class="card-body d-flex justify-content-between align-items-center">
            <div class="d-flex flex-column">
                <span class="font-weight-bold"><?= l('teams_member.menu') ?></span>
                <span class="small text-muted"><?= l('teams_member.subheader') ?></span>
            </div>

            <div>
                <span class="badge badge-light"><?= nr($data->total_teams_member) ?></span>
            </div>
        </div>
    </div>

    <?php if (!empty($data->teams_member)): ?>
        <div class="table-responsive table-custom-container">
            <table class="table table-custom">
                <thead>
                <tr>
                    <th><?= l('teams_member.table.team') ?></th>
                    <th><?= l('team_members.permissions') ?></th>
                    <th><?= l('global.status') ?></th>
                    <th></th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach($data->teams_member as $row): ?>
                    <tr>
                        <td class="text-nowrap">
                            <?= $row->name ?>
                        </td>

                        <td class="text-nowrap">
                            <?php
                            $access_html = [];
                            foreach($data->teams_access as $key => $value) {
                                $access_html[$key] = '';
                                foreach($data->teams_access[$key] as $access_key => $access_translation) {
                                    $access_html[$key] .= ($row->access->{$access_key} ? $access_translation : '<s>' . $access_translation . '</s>') . '<br />';
                                }
                            }
                            ?>
                            <span class="badge badge-secondary mx-1" data-toggle="tooltip" data-html="true" title="<?= '<strong>' . l('team_members.access.read') . '</strong><br />' . $access_html['read'] ?>">
                                <i class="fas fa-fw fa-sm fa-eye"></i>
                            </span>

                            <span class="badge badge-success mx-1" data-toggle="tooltip" data-html="true" title="<?= '<strong>' . l('team_members.access.create') . '</strong><br />' . $access_html['create'] ?>">
                                <i class="fas fa-fw fa-plus-circle fa-sm"></i>
                            </span>

                            <span class="badge badge-info mx-1" data-toggle="tooltip" data-html="true" title="<?= '<strong>' . l('team_members.access.update') . '</strong><br />' . $access_html['update'] ?>">
                                <i class="fas fa-fw fa-sm fa-pencil-alt"></i>
                            </span>

                            <span class="badge badge-danger mx-1" data-toggle="tooltip" data-html="true" title="<?= '<strong>' . l('team_members.access.delete') . '</strong><br />' . $access_html['delete'] ?>">
                                <i class="fas fa-fw fa-sm fa-trash-alt"></i>
                            </span>
                        </td>

                        <td class="text-nowrap">
                            <?php if($row->status): ?>
                                <span class="badge badge-success"><?= l('team_members.table.status_accepted') ?></span>
                            <?php else: ?>
                                <span class="badge badge-warning"><?= l('team_members.table.status_invited') ?></span>
                            <?php endif ?>
                        </td>

                        <td class="text-nowrap">
                            <div class="d-flex align-items-center">
                                <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= l('team_members.table.status_invited') . '<br />' . \Altum\Date::get($row->datetime, 2) . '<br /><small>' . \Altum\Date::get($row->datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->datetime) . ')</small>' ?>">
                                    <i class="fas fa-fw fa-paper-plane text-muted"></i>
                                </span>

                                <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= l('team_members.table.status_accepted'), ($row->last_datetime ? '<br />' . \Altum\Date::get($row->last_datetime, 2) . '<br /><small>' . \Altum\Date::get($row->last_datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->last_datetime) . ')</small>' : '<br />' . l('global.na')) ?>">
                                    <i class="fas fa-fw fa-check-circle text-muted"></i>
                                </span>
                            </div>
                        </td>

                        <td>
                            <div class="d-flex justify-content-end">
                                <div>
                                    <?php if($row->status): ?>
                                        <button type="button" class="btn btn-light" title="<?= l('teams_member_login_modal.menu') ?>" data-team-member-login data-team-member-id="<?= $row->team_member_id ?>" data-tooltip data-tooltip-hide-on-click>
                                            <i class="fas fa-fw fa-sm fa-sign-in-alt"></i>
                                        </button>
                                    <?php else: ?>
                                        <button type="button" class="btn btn-light" title="<?= l('teams_member_join_modal.menu') ?>" data-team-member-join data-team-member-id="<?= $row->team_member_id ?>" data-tooltip data-tooltip-hide-on-click>
                                            <i class="fas fa-fw fa-sm fa-check-circle"></i>
                                        </button>
                                    <?php endif ?>
                                </div>

                                <?= include_view(THEME_PATH . 'views/teams-member/teams_member_dropdown_button.php', ['id' => $row->team_member_id, 'status' => $row->status]) ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach ?>
                </tbody>
            </table>
        </div>

        <div class="mt-3"><?= $data->teams_member_pagination ?></div>
    <?php endif ?>
</div>

<?php \Altum\Event::add_content(fn() => include_view(THEME_PATH . 'views/teams-member/teams_member_delete_modal.php'), 'modals'); ?>
<?php \Altum\Event::add_content(fn() => include_view(THEME_PATH . 'views/teams-member/teams_member_join_modal.php'), 'modals'); ?>
<?php \Altum\Event::add_content(fn() => include_view(THEME_PATH . 'views/teams-member/teams_member_login_modal.php'), 'modals'); ?>

<?php \Altum\Event::add_content(fn() => include_view(THEME_PATH . 'views/partials/universal_delete_modal_form.php', [
        'name' => 'team',
        'resource_id' => 'team_id',
        'has_dynamic_resource_name' => true,
        'path' => 'teams/delete'
]), 'modals'); ?>

<?php \Altum\Event::add_content(fn() => include_view(THEME_PATH . 'views/partials/universal_delete_modal_form.php', [
        'name' => 'team_member',
        'resource_id' => 'team_member_id',
        'has_dynamic_resource_name' => true,
        'path' => 'teams-members/delete'
]), 'modals'); ?>
