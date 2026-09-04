<?php defined('ALTUMCODE') || die() ?>

<?php /* FaceTime Audio */ ?>
<div>
    <div class="form-group" data-type="facetime_audio">
        <label for="facetime_audio"><i class="fas fa-fw fa-headphones fa-sm text-muted mr-1"></i> <?= l('qr_codes.input.facetime_audio') ?></label>
        <input type="text" id="facetime_audio" name="facetime_audio" class="form-control" value="" maxlength="<?= $data->available_qr_codes['facetime_audio']['max_length'] ?>" required="required" data-reload-qr-code />
    </div>
</div>
