<?php
    $config = Config::getInstance();

    $getDescription = function ($id) use ($config) {
        $field = $config->getMetadata($id);
        return $field['description'];
    };
?>
<form method="post" action="<?= $controller->url_for('admin/settings/store') ?>">
    <?= CSRFProtection::tokenTag() ?>
    <input type="hidden" name="studip_ticket" value="<?= get_ticket() ?>">

    <table class="default">
        <thead>
            <tr>
                <th><?= _('Einstellung') ?></th>
                <th><?= _('Wert') ?></th>
            </tr>
        </thead>
        <tbody>
        <? foreach ($options as $key => $option): ?>
            <tr>
                <td>
                    <label for="option-<?= md5($key) ?>">
                    <?= htmlReady($option['description']) ?>
                </td>
                <td>
                <? if ($option['type'] === 'checkbox'): ?>
                    <input type="hidden" name="<?= $option['key'] ?>" value="0">
                    <input type="checkbox" name="<?= $option['key'] ?>"
                           id="option-<?= md5($key) ?>" value="1"
                           <? if ((bool)$option['value']) echo 'checked'; ?>
                           <? if ($option['activates']) printf('data-activates="#option-%s"', md5($option['activates'])); ?>>
                <? else: ?>
                    <input type="<?= $option['type'] ?>" name="<?= $option['key'] ?>"
                           id="option-<?= md5($key) ?>"
                           value="<?= htmlReady($option['value']) ?>">
                <? endif; ?>
                </td>
            </tr>
        <? endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2">
                    <?= Studip\Button::createAccept(_('Speichern')) ?>
                </td>
        </tfoot>
    </table>
</form>

<? if (!$visible_for_nobody): ?>
    <?= MessageBox::info(_('Hinweise'), array(_('Bei Aktivierung der RSS Feeds muss das Plugin für nobody freigegeben werden, damit die Feeds ohne Login abgerufen werden können.'))) ?>
<? endif; ?>