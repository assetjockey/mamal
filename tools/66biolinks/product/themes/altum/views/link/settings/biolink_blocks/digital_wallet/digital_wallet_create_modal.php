<?php defined('ALTUMCODE') || die() ?>
<?php $digital_wallets = db()->where('user_id', $this->user->user_id)->orderBy('digital_wallet_id', 'DESC')->get('digital_wallets', null, ['digital_wallet_id', 'name']) ?>

<div class="modal fade" id="create_biolink_digital_wallet" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <button type="button" data-toggle="modal" data-target="#biolink_link_create_modal" data-dismiss="modal" class="btn btn-sm btn-link"><i class="fas fa-fw fa-chevron-circle-left text-muted"></i></button>
                <h5 class="modal-title"><?= l('biolink_digital_wallet.header') ?></h5>
                <button type="button" class="close" data-dismiss="modal" title="<?= l('global.close') ?>">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <form name="create_biolink_digital_wallet" method="post" role="form">
                    <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" required="required" />
                    <input type="hidden" name="request_type" value="create" />
                    <input type="hidden" name="link_id" value="<?= $data->link->link_id ?>" />
                    <input type="hidden" name="block_type" value="digital_wallet" />

                    <div class="notification-container"></div>

                    <div class="form-group">
                        <div class="d-flex flex-wrap flex-row justify-content-between">
                            <label for="digital_wallet_digital_wallet_id"><i class="fas fa-fw fa-wifi fa-sm text-muted mr-1"></i> <?= l('biolink_digital_wallet.digital_wallet_id') ?></label>
                            <a href="<?= url('digital-wallet-create') ?>" target="_blank" class="small mb-2"><i class="fas fa-fw fa-sm fa-plus mr-1"></i> <?= l('digital_wallets.create') ?></a>
                        </div>
                        <select id="digital_wallet_digital_wallet_id" name="digital_wallet_id" class="custom-select" required="required">
							<?php foreach($digital_wallets as $digital_wallet): ?>
                                <option value="<?= $digital_wallet->digital_wallet_id ?>"><?= $digital_wallet->name ?></option>
							<?php endforeach ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="digital_wallet_wallet_provider"><i class="fas fa-fw fa-wallet fa-sm text-muted mr-1"></i> <?= l('biolink_digital_wallet.wallet_provider') ?></label>
                        <select id="digital_wallet_wallet_provider" name="wallet_provider" class="custom-select" required="required">
                            <?php if(digital_wallets_is_google_wallet_ready()): ?>
                                <option value="google"><?= l('digital_wallets.google_wallet') ?></option>
                            <?php endif ?>
                            <?php if(digital_wallets_is_apple_wallet_ready()): ?>
                                <option value="apple"><?= l('digital_wallets.apple_wallet') ?></option>
                            <?php endif ?>
                            <?php if(digital_wallets_is_google_wallet_ready() && digital_wallets_is_apple_wallet_ready()): ?>
                                <option value="both"><?= l('biolink_digital_wallet.wallet_provider_both') ?></option>
                            <?php endif ?>
                        </select>
                    </div>

                    <p class="small text-muted"><i class="fas fa-fw fa-sm fa-circle-info mr-1"></i> <?= l('link.create_info') ?></p>

                    <div class="text-center mt-4">
                        <button type="submit" name="submit" class="btn btn-block btn-primary <?= count($digital_wallets) ? null : 'disabled' ?>" data-is-ajax <?= count($digital_wallets) ? null : 'disabled="disabled"' ?>><?= l('link.biolink.create_block') ?></button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>
