<?php defined('ALTUMCODE') || die() ?>

<div id="content">
    <button class="btn btn-block btn-gray-200 font-size-little-small font-weight-450 mb-4" type="button" data-toggle="collapse" data-target="#blog_container" aria-expanded="false" aria-controls="blog_container">
        <i class="fas fa-fw fa-blog fa-sm mr-1"></i> <?= l('admin_settings.content.blog') ?>
    </button>

    <div class="collapse" data-parent="#content" id="blog_container">
        <div class="form-group custom-control custom-switch">
            <input id="blog_is_enabled" name="blog_is_enabled" type="checkbox" class="custom-control-input" <?= settings()->content->blog_is_enabled ? 'checked="checked"' : null?>>
            <label class="custom-control-label" for="blog_is_enabled"><i class="fas fa-fw fa-sm fa-blog text-muted mr-1"></i> <?= l('admin_settings.content.blog_is_enabled') ?></label>
        </div>

        <div class="form-group custom-control custom-switch">
            <input id="blog_share_is_enabled" name="blog_share_is_enabled" type="checkbox" class="custom-control-input" <?= settings()->content->blog_share_is_enabled ? 'checked="checked"' : null?>>
            <label class="custom-control-label" for="blog_share_is_enabled"><i class="fas fa-fw fa-sm fa-share-alt text-muted mr-1"></i> <?= l('admin_settings.content.blog_share_is_enabled') ?></label>
        </div>

        <div class="form-group custom-control custom-switch">
            <input id="blog_featured_post_is_enabled" name="blog_featured_post_is_enabled" type="checkbox" class="custom-control-input" <?= settings()->content->blog_featured_post_is_enabled ? 'checked="checked"' : null?>>
            <label class="custom-control-label" for="blog_featured_post_is_enabled"><i class="fas fa-fw fa-sm fa-star text-muted mr-1"></i> <?= l('admin_settings.content.blog_featured_post_is_enabled') ?></label>
        </div>

        <div class="form-group custom-control custom-switch">
            <input id="blog_related_posts_is_enabled" name="blog_related_posts_is_enabled" type="checkbox" class="custom-control-input" <?= settings()->content->blog_related_posts_is_enabled ? 'checked="checked"' : null?>>
            <label class="custom-control-label" for="blog_related_posts_is_enabled"><i class="fas fa-fw fa-sm fa-link text-muted mr-1"></i> <?= l('admin_settings.content.blog_related_posts_is_enabled') ?></label>
        </div>

        <div class="form-group">
            <label for="blog_related_posts_limit"><i class="fas fa-fw fa-sm fa-list-ol text-muted mr-1"></i> <?= l('admin_settings.content.blog_related_posts_limit') ?></label>
            <input id="blog_related_posts_limit" type="number" min="1" max="12" name="blog_related_posts_limit" class="form-control" value="<?= settings()->content->blog_related_posts_limit ?>" />
        </div>

        <div class="form-group custom-control custom-switch">
            <input id="blog_table_of_contents_is_enabled" name="blog_table_of_contents_is_enabled" type="checkbox" class="custom-control-input" <?= isset(settings()->content->blog_table_of_contents_is_enabled) && settings()->content->blog_table_of_contents_is_enabled ? 'checked="checked"' : null?>>
            <label class="custom-control-label" for="blog_table_of_contents_is_enabled"><i class="fas fa-fw fa-sm fa-list text-muted mr-1"></i> <?= l('admin_settings.content.blog_table_of_contents_is_enabled') ?></label>
        </div>

        <div class="form-group custom-control custom-switch">
            <input id="blog_search_widget_is_enabled" name="blog_search_widget_is_enabled" type="checkbox" class="custom-control-input" <?= settings()->content->blog_search_widget_is_enabled ? 'checked="checked"' : null?>>
            <label class="custom-control-label" for="blog_search_widget_is_enabled"><i class="fas fa-fw fa-sm fa-search text-muted mr-1"></i> <?= l('admin_settings.content.blog_search_widget_is_enabled') ?></label>
        </div>

        <div class="form-group custom-control custom-switch">
            <input id="blog_categories_widget_is_enabled" name="blog_categories_widget_is_enabled" type="checkbox" class="custom-control-input" <?= settings()->content->blog_categories_widget_is_enabled ? 'checked="checked"' : null?>>
            <label class="custom-control-label" for="blog_categories_widget_is_enabled"><i class="fas fa-fw fa-sm fa-map text-muted mr-1"></i> <?= l('admin_settings.content.blog_categories_widget_is_enabled') ?></label>
        </div>

        <div class="form-group custom-control custom-switch">
            <input id="blog_popular_widget_is_enabled" name="blog_popular_widget_is_enabled" type="checkbox" class="custom-control-input" <?= settings()->content->blog_popular_widget_is_enabled ? 'checked="checked"' : null?>>
            <label class="custom-control-label" for="blog_popular_widget_is_enabled"><i class="fas fa-fw fa-sm fa-fire text-muted mr-1"></i> <?= l('admin_settings.content.blog_popular_widget_is_enabled') ?></label>
        </div>

        <div class="form-group">
            <label for="blog_popular_widget_posts_limit"><i class="fas fa-fw fa-sm fa-list-ol text-muted mr-1"></i> <?= l('admin_settings.content.blog_popular_widget_posts_limit') ?></label>
            <input id="blog_popular_widget_posts_limit" type="number" min="1" name="blog_popular_widget_posts_limit" class="form-control" value="<?= settings()->content->blog_popular_widget_posts_limit ?>" />
        </div>

        <div class="form-group custom-control custom-switch">
            <input id="blog_latest_widget_is_enabled" name="blog_latest_widget_is_enabled" type="checkbox" class="custom-control-input" <?= settings()->content->blog_latest_widget_is_enabled ? 'checked="checked"' : null?>>
            <label class="custom-control-label" for="blog_latest_widget_is_enabled"><i class="fas fa-fw fa-sm fa-clock text-muted mr-1"></i> <?= l('admin_settings.content.blog_latest_widget_is_enabled') ?></label>
        </div>

        <div class="form-group">
            <label for="blog_latest_widget_posts_limit"><i class="fas fa-fw fa-sm fa-list-ol text-muted mr-1"></i> <?= l('admin_settings.content.blog_latest_widget_posts_limit') ?></label>
            <input id="blog_latest_widget_posts_limit" type="number" min="1" name="blog_latest_widget_posts_limit" class="form-control" value="<?= settings()->content->blog_latest_widget_posts_limit ?>" />
        </div>

        <div class="form-group custom-control custom-switch">
            <input id="blog_views_is_enabled" name="blog_views_is_enabled" type="checkbox" class="custom-control-input" <?= settings()->content->blog_views_is_enabled ? 'checked="checked"' : null?>>
            <label class="custom-control-label" for="blog_views_is_enabled"><i class="fas fa-fw fa-sm fa-eye text-muted mr-1"></i> <?= l('admin_settings.content.blog_views_is_enabled') ?></label>
        </div>

        <div class="form-group custom-control custom-switch">
            <input id="blog_ratings_is_enabled" name="blog_ratings_is_enabled" type="checkbox" class="custom-control-input" <?= settings()->content->blog_ratings_is_enabled ? 'checked="checked"' : null?>>
            <label class="custom-control-label" for="blog_ratings_is_enabled"><i class="fas fa-fw fa-sm fa-star text-muted mr-1"></i> <?= l('admin_settings.content.blog_ratings_is_enabled') ?></label>
        </div>

        <div class="form-group">
            <label for="blog_columns"><i class="fas fa-fw fa-sm fa-columns text-muted mr-1"></i> <?= l('admin_settings.content.blog_columns') ?></label>
            <select id="blog_columns" name="blog_columns" class="custom-select">
                <option value="1" <?= settings()->content->blog_columns == '1' ? 'selected="selected"' : null ?>>1</option>
                <option value="2" <?= settings()->content->blog_columns == '2' ? 'selected="selected"' : null ?>>2</option>
            </select>
        </div>

        <div class="form-group custom-control custom-switch">
            <input id="blog_authors_is_enabled" name="blog_authors_is_enabled" type="checkbox" class="custom-control-input" <?= isset(settings()->content->blog_authors_is_enabled) && settings()->content->blog_authors_is_enabled ? 'checked="checked"' : null?>>
            <label class="custom-control-label" for="blog_authors_is_enabled"><i class="fas fa-fw fa-sm fa-user-pen text-muted mr-1"></i> <?= l('admin_settings.content.blog_authors_is_enabled') ?></label>
        </div>

        <?php $blog_authors = isset(settings()->content->blog_authors) ? settings()->content->blog_authors : [] ?>

        <label><i class="fas fa-fw fa-sm fa-users text-muted mr-1"></i> <?= l('admin_settings.content.blog_authors') ?></label>
        <div id="blog_authors">
            <?php foreach($blog_authors as $blog_author): ?>
                <div class="blog-author p-3 bg-gray-50 rounded mb-4">
                    <div class="form-group">
                        <div class="d-flex justify-content-between">
                            <label for="<?= 'author_id[' . $blog_author->id . ']' ?>"><i class="fas fa-fw fa-sm fa-fingerprint text-muted mr-1"></i> <?= l('admin_settings.content.blog_authors_id') ?></label>

                            <span class="cursor-grab drag" data-toggle="tooltip" title="<?= l('global.drag_and_drop') ?>">
                                <i class="fas fa-fw fa-sm fa-bars text-muted"></i>
                            </span>
                        </div>
                        <input id="<?= 'author_id[' . $blog_author->id . ']' ?>" type="text" name="author_id[<?= $blog_author->id ?>]" class="form-control" value="<?= $blog_author->id ?>" onchange="update_this_value(this, get_slug)" onkeyup="update_this_value(this, get_slug)" required="required" />
                        <small class="form-text text-muted"><?= l('admin_settings.content.blog_authors_id_help') ?></small>
                    </div>

                    <button type="button" class="btn btn-block btn-sm btn-outline-primary mb-3" data-toggle="collapse" data-target="<?= '#' . 'blog_author_container_' . md5($blog_author->id) ?>" aria-expanded="false" aria-controls="<?= 'blog_author_container_' . md5($blog_author->id) ?>">
                        <i class="fas fa-fw fa-pencil fa-sm mr-1"></i> <?= l('global.update') ?>
                    </button>

                    <div class="collapse" data-parent="#blog_authors" id="<?= 'blog_author_container_' . md5($blog_author->id) ?>">
                        <div class="form-group">
                            <label for="<?= 'author_name[' . $blog_author->id . ']' ?>"><i class="fas fa-fw fa-sm fa-signature text-muted mr-1"></i> <?= l('global.name') ?></label>
                            <input id="<?= 'author_name[' . $blog_author->id . ']' ?>" type="text" name="author_name[<?= $blog_author->id ?>]" class="form-control" value="<?= $blog_author->name ?>" maxlength="256" required="required" />
                        </div>

                        <div class="form-group">
                            <label for="<?= 'author_avatar[' . $blog_author->id . ']' ?>"><i class="fas fa-fw fa-sm fa-image text-muted mr-1"></i> <?= l('admin_settings.content.blog_authors_avatar') ?></label>
                            <input id="<?= 'author_avatar[' . $blog_author->id . ']' ?>" type="url" name="author_avatar[<?= $blog_author->id ?>]" class="form-control" value="<?= isset($blog_author->avatar) ? $blog_author->avatar : null ?>" maxlength="512" />
                        </div>

                        <div class="form-group">
                            <label for="<?= 'author_description[' . $blog_author->id . ']' ?>"><i class="fas fa-fw fa-sm fa-pen text-muted mr-1"></i> <?= l('admin_settings.content.blog_authors_description') ?></label>
                            <textarea id="<?= 'author_description[' . $blog_author->id . ']' ?>" name="author_description[<?= $blog_author->id ?>]" class="form-control" maxlength="512"><?= isset($blog_author->description) ? $blog_author->description : null ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="<?= 'author_url[' . $blog_author->id . ']' ?>"><i class="fas fa-fw fa-sm fa-link text-muted mr-1"></i> <?= l('admin_settings.content.blog_authors_url') ?></label>
                            <input id="<?= 'author_url[' . $blog_author->id . ']' ?>" type="url" name="author_url[<?= $blog_author->id ?>]" class="form-control" value="<?= isset($blog_author->url) ? $blog_author->url : null ?>" maxlength="512" />
                        </div>

                        <div class="form-group custom-control custom-switch">
                            <input id="<?= 'author_url_nofollow[' . $blog_author->id . ']' ?>" name="author_url_nofollow[<?= $blog_author->id ?>]" type="checkbox" class="custom-control-input" <?= isset($blog_author->url_nofollow) && $blog_author->url_nofollow ? 'checked="checked"' : null?>>
                            <label class="custom-control-label" for="<?= 'author_url_nofollow[' . $blog_author->id . ']' ?>"><?= l('admin_settings.content.blog_authors_url_nofollow') ?></label>
                        </div>
                    </div>

                    <button type="button" data-remove="blog_authors" class="btn btn-block btn-outline-danger"><i class="fas fa-fw fa-times fa-sm mr-1"></i> <?= l('global.delete') ?></button>
                </div>
            <?php endforeach ?>
        </div>

        <div class="mb-4">
            <button data-add="blog_authors" type="button" class="btn btn-block btn-outline-success"><i class="fas fa-fw fa-plus-circle fa-sm mr-1"></i> <?= l('global.create') ?></button>
        </div>
    </div>

    <button class="btn btn-block btn-gray-200 font-size-little-small font-weight-450 mb-4" type="button" data-toggle="collapse" data-target="#pages_container" aria-expanded="false" aria-controls="pages_container">
        <i class="fas fa-fw fa-info-circle fa-sm mr-1"></i> <?= l('admin_settings.content.pages') ?>
    </button>

    <div class="collapse" data-parent="#content" id="pages_container">
        <div class="form-group custom-control custom-switch">
            <input id="pages_is_enabled" name="pages_is_enabled" type="checkbox" class="custom-control-input" <?= settings()->content->pages_is_enabled ? 'checked="checked"' : null?>>
            <label class="custom-control-label" for="pages_is_enabled"><i class="fas fa-fw fa-sm fa-info-circle text-muted mr-1"></i> <?= l('admin_settings.content.pages_is_enabled') ?></label>
        </div>

        <div class="form-group custom-control custom-switch">
            <input id="pages_share_is_enabled" name="pages_share_is_enabled" type="checkbox" class="custom-control-input" <?= settings()->content->pages_share_is_enabled ? 'checked="checked"' : null?>>
            <label class="custom-control-label" for="pages_share_is_enabled"><i class="fas fa-fw fa-sm fa-share-alt text-muted mr-1"></i> <?= l('admin_settings.content.pages_share_is_enabled') ?></label>
        </div>

        <div class="form-group custom-control custom-switch">
            <input id="pages_popular_widget_is_enabled" name="pages_popular_widget_is_enabled" type="checkbox" class="custom-control-input" <?= settings()->content->pages_popular_widget_is_enabled ? 'checked="checked"' : null?>>
            <label class="custom-control-label" for="pages_popular_widget_is_enabled"><i class="fas fa-fw fa-sm fa-fire text-muted mr-1"></i> <?= l('admin_settings.content.pages_popular_widget_is_enabled') ?></label>
        </div>

        <div class="form-group custom-control custom-switch">
            <input id="pages_table_of_contents_is_enabled" name="pages_table_of_contents_is_enabled" type="checkbox" class="custom-control-input" <?= settings()->content->pages_table_of_contents_is_enabled ? 'checked="checked"' : null?>>
            <label class="custom-control-label" for="pages_table_of_contents_is_enabled"><i class="fas fa-fw fa-sm fa-list-ul text-muted mr-1"></i> <?= l('admin_settings.content.pages_table_of_contents_is_enabled') ?></label>
        </div>

        <div class="form-group custom-control custom-switch">
            <input id="pages_views_is_enabled" name="pages_views_is_enabled" type="checkbox" class="custom-control-input" <?= settings()->content->pages_views_is_enabled ? 'checked="checked"' : null?>>
            <label class="custom-control-label" for="pages_views_is_enabled"><i class="fas fa-fw fa-sm fa-eye text-muted mr-1"></i> <?= l('admin_settings.content.pages_views_is_enabled') ?></label>
        </div>
    </div>

    <button class="btn btn-block btn-gray-200 font-size-little-small font-weight-450 mb-4" type="button" data-toggle="collapse" data-target="#broadcasts_container" aria-expanded="false" aria-controls="broadcasts_container">
        <i class="fas fa-fw fa-envelopes-bulk fa-sm mr-1"></i> <?= l('admin_settings.content.broadcasts') ?>
    </button>

    <div class="collapse" data-parent="#content" id="broadcasts_container">
        <div class="form-group custom-control custom-switch">
            <input id="broadcasts_is_enabled" name="broadcasts_is_enabled" type="checkbox" class="custom-control-input" <?= settings()->content->broadcasts_is_enabled ? 'checked="checked"' : null?>>
            <label class="custom-control-label" for="broadcasts_is_enabled"><i class="fas fa-fw fa-sm fa-envelope-open-text text-muted mr-1"></i> <?= l('admin_settings.content.broadcasts_is_enabled') ?></label>
            <small class="form-text text-muted"><?= l('admin_settings.content.broadcasts_is_enabled_help') ?></small>
        </div>

        <div class="form-group custom-control custom-switch">
            <input id="broadcasts_guests_is_enabled" name="broadcasts_guests_is_enabled" type="checkbox" class="custom-control-input" <?= settings()->content->broadcasts_guests_is_enabled ? 'checked="checked"' : null?>>
            <label class="custom-control-label" for="broadcasts_guests_is_enabled"><i class="fas fa-fw fa-sm fa-user-tag text-muted mr-1"></i> <?= l('admin_settings.content.broadcasts_guests_is_enabled') ?></label>
            <small class="form-text text-muted"><?= l('admin_settings.content.broadcasts_guests_is_enabled_help') ?></small>
        </div>

        <div class="form-group custom-control custom-switch">
            <input id="broadcasts_statistics_is_enabled" name="broadcasts_statistics_is_enabled" type="checkbox" class="custom-control-input" <?= settings()->content->broadcasts_statistics_is_enabled ? 'checked="checked"' : null?>>
            <label class="custom-control-label" for="broadcasts_statistics_is_enabled"><i class="fas fa-fw fa-sm fa-star text-muted mr-1"></i> <?= l('admin_settings.content.broadcasts_statistics_is_enabled') ?></label>
            <small class="form-text text-muted"><?= l('admin_settings.content.broadcasts_statistics_is_enabled_help') ?></small>
        </div>

        <div class="form-group custom-control custom-switch">
            <input id="broadcasts_display_index_box" name="broadcasts_display_index_box" type="checkbox" class="custom-control-input" <?= settings()->content->broadcasts_display_index_box ? 'checked="checked"' : null?>>
            <label class="custom-control-label" for="broadcasts_display_index_box"><i class="fas fa-fw fa-sm fa-square text-muted mr-1"></i> <?= l('admin_settings.content.broadcasts_display_index_box') ?></label>
        </div>

        <div class="form-group custom-control custom-switch">
            <input id="broadcasts_display_register_checkbox" name="broadcasts_display_register_checkbox" type="checkbox" class="custom-control-input" <?= settings()->content->broadcasts_display_register_checkbox ? 'checked="checked"' : null?>>
            <label class="custom-control-label" for="broadcasts_display_register_checkbox"><i class="fas fa-fw fa-sm fa-newspaper text-muted mr-1"></i> <?= l('admin_settings.content.broadcasts_display_register_checkbox') ?></label>
        </div>

        <div class="form-group custom-control custom-switch">
            <input id="broadcasts_display_account_checkbox" name="broadcasts_display_account_checkbox" type="checkbox" class="custom-control-input" <?= settings()->content->broadcasts_display_account_checkbox ? 'checked="checked"' : null?>>
            <label class="custom-control-label" for="broadcasts_display_account_checkbox"><i class="fas fa-fw fa-sm fa-newspaper text-muted mr-1"></i> <?= l('admin_settings.content.broadcasts_display_account_checkbox') ?></label>
        </div>

        <div class="alert alert-danger mb-3"><?= l('admin_settings.cron.cron_settings_help') ?></div>

        <div class="form-group">
            <label for="broadcasts_emails_per_cron"><i class="fas fa-fw fa-sm fa-refresh text-muted mr-1"></i> <?= l('admin_settings.content.broadcasts_emails_per_cron') ?></label>
            <input id="broadcasts_emails_per_cron" type="number" min="1" name="broadcasts_emails_per_cron" class="form-control" value="<?= settings()->content->broadcasts_emails_per_cron ?? 40 ?>" />
        </div>
    </div>
</div>

<button type="submit" name="submit" class="btn btn-lg btn-block btn-primary mt-4"><?= l('global.update') ?></button>

<template id="template_blog_authors">
    <div class="blog-author p-3 bg-gray-50 rounded mb-4">
        <div class="form-group">
            <label for="author_id"><i class="fas fa-fw fa-sm fa-fingerprint text-muted mr-1"></i> <?= l('admin_settings.content.blog_authors_id') ?></label>
            <input id="author_id" type="text" name="author_id[]" class="form-control" value="" onchange="update_this_value(this, get_slug)" onkeyup="update_this_value(this, get_slug)" required="required" />
            <small class="form-text text-muted"><?= l('admin_settings.content.blog_authors_id_help') ?></small>
        </div>

        <div class="form-group">
            <label for="author_name"><i class="fas fa-fw fa-sm fa-signature text-muted mr-1"></i> <?= l('global.name') ?></label>
            <input id="author_name" type="text" name="author_name[]" class="form-control" maxlength="256" required="required" />
        </div>

        <div class="form-group">
            <label for="author_avatar"><i class="fas fa-fw fa-sm fa-image text-muted mr-1"></i> <?= l('admin_settings.content.blog_authors_avatar') ?></label>
            <input id="author_avatar" type="url" name="author_avatar[]" class="form-control" maxlength="512" />
        </div>

        <div class="form-group">
            <label for="author_description"><i class="fas fa-fw fa-sm fa-pen text-muted mr-1"></i> <?= l('admin_settings.content.blog_authors_description') ?></label>
            <textarea id="author_description" name="author_description[]" class="form-control" maxlength="512"></textarea>
        </div>

        <div class="form-group">
            <label for="author_url"><i class="fas fa-fw fa-sm fa-link text-muted mr-1"></i> <?= l('admin_settings.content.blog_authors_url') ?></label>
            <input id="author_url" type="url" name="author_url[]" class="form-control" maxlength="512" />
        </div>

        <div class="form-group custom-control custom-switch">
            <input id="author_url_nofollow" name="author_url_nofollow[]" type="checkbox" class="custom-control-input">
            <label class="custom-control-label" for="author_url_nofollow"><?= l('admin_settings.content.blog_authors_url_nofollow') ?></label>
        </div>

        <button type="button" data-remove class="btn btn-block btn-outline-danger"><i class="fas fa-fw fa-times"></i> <?= l('global.delete') ?></button>
    </div>
</template>

<?php ob_start() ?>
<script>
    'use strict';

    /* Add author */
    let blog_author_add = event => {
        let type = event.currentTarget.getAttribute('data-add');
        let clone = document.querySelector(`#template_${type}`).content.cloneNode(true);

        document.querySelector(`#${type}`).appendChild(clone);

        blog_author_remove_initiator();
        blog_author_id_initiator();
    };

    document.querySelectorAll('[data-add="blog_authors"]').forEach(element => {
        element.addEventListener('click', blog_author_add);
    })

    /* Remove author */
    let blog_author_remove = event => {
        event.currentTarget.closest('.blog-author').remove();

        blog_author_id_initiator();
    };

    let blog_author_remove_initiator = () => {
        document.querySelectorAll('#blog_authors [data-remove]').forEach(element => {
            element.removeEventListener('click', blog_author_remove);
            element.addEventListener('click', blog_author_remove)
        })
    };

    blog_author_remove_initiator();

    let blog_author_id = event => {
        let blog_author = event.currentTarget.closest('.blog-author');
        let id = event.currentTarget.value;

        blog_author.querySelectorAll(`input, textarea`).forEach(element => {
            let cleaned_id = element.id.split('[')[0];
            element.name = `${cleaned_id}[${id}]`;
            element.id = `${cleaned_id}[${id}]`;
            element.closest('.form-group').querySelector('label').setAttribute('for', `${cleaned_id}[${id}]`);
        });
    }

    let blog_author_id_initiator = () => {
        document.querySelectorAll('#blog_authors [name^="author_id"]').forEach(element => {
            element.removeEventListener('change', blog_author_id);
            element.addEventListener('change', blog_author_id)
        })
    }

    blog_author_id_initiator();
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>

<?php ob_start() ?>
<script src="<?= ASSETS_FULL_URL . 'js/libraries/sortable.js?v=' . PRODUCT_CODE ?>"></script>
<script>
    'use strict';

    let blog_authors_sortable = Sortable.create(document.getElementById('blog_authors'), {
        animation: 150,
        handle: '.drag',
        onUpdate: event => {

            /* Refresh tooltips */
            tooltips_initiate();

        }
    });
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
