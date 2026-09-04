<?php defined('ALTUMCODE') || die() ?>

<div class="modal fade" id="website_public_statistics_modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-body">
                <div class="d-flex justify-content-between mb-3">
                    <h5 class="modal-title">
                        <i class="fas fa-fw fa-sm fa-paper-plane text-dark mr-2"></i>
                        <?= l('website_public_statistics_modal.header') ?>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" title="<?= l('global.close') ?>">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <p class="text-muted"><?= l('website_public_statistics_modal.subheader') ?></p>

                <pre id="public_statistics_url" class="pre-custom rounded"></pre>

                <div class="mt-4">
                    <a href="" target="_blank" type="button" class="btn btn-lg btn-block btn-outline-primary" data-view-button><?= l('global.view') ?> <i class="fas fa-fw fa-xs fa-up-right-from-square ml-1"></i></a>
                    <button type="button" class="btn btn-lg btn-block btn-primary" data-clipboard-target="#public_statistics_url" data-copied="<?= l('website_public_statistics_modal.copied') ?>"><?= l('website_public_statistics_modal.copy') ?></button>
                </div>
            </div>

        </div>
    </div>
</div>

<?php ob_start() ?>
<script src="<?= ASSETS_FULL_URL ?>js/libraries/clipboard.min.js?v=<?= PRODUCT_CODE ?>"></script>

<script>
    'use strict';
    
    /* On modal show */
    $('#website_public_statistics_modal').on('show.bs.modal', event => {
        let pixel_key = $(event.relatedTarget).data('pixel-key');
        let base_url = $(event.relatedTarget).data('base-url');

        let public_statistics_url = `${base_url}statistics/${pixel_key}`;

        $(event.currentTarget).find('pre').html(public_statistics_url);
        event.currentTarget.querySelector('[data-view-button]').setAttribute('href', public_statistics_url);

        new ClipboardJS('[data-clipboard-target]');

        /* Handle on click button */
        let copy_button = $(event.currentTarget).find('[data-clipboard-target]');
        let initial_text = copy_button.text();

        copy_button.on('click', () => {

            copy_button.text(copy_button.data('copied'));

            setTimeout(() => {
                copy_button.text(initial_text);
            }, 2500);
        });
    });
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
