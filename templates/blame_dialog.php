<form name="add" method="post" action="<?=$approvalLink ?>" class="modaloverlay">
    <div class="messagebox">
        <div class="content">
            <?= formatReady($question) ?><br>
            <textarea placeholder="<?= _('Grund') ?>" style="width: 99%;" name="blame_reason"></textarea>
        </div>
        <div class="buttons" style="margin-top: 1em;">
            <?= Studip\Button::createAccept(_('Ja'), 'ja') ?>
            <?= Studip\LinkButton::createCancel(_('Nein'), $disapprovalLink) ?>
        </div>
    </div>
</form>
