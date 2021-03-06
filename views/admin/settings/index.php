<?php
    $config = Config::getInstance();

    $getDescription = function ($id) use ($config) {
        $field = $config->getMetadata($id);
        return $field['description'];
    };
?>
<form method="post" action="<?= $controller->store() ?>">
    <?= CSRFProtection::tokenTag() ?>
    <input type="hidden" name="studip_ticket" value="<?= get_ticket() ?>">

    <table class="default" id="bb-config">
        <colgroup>
            <col width="50%">
            <col width="50%">
        </colgroup>
        <thead>
            <tr>
                <th><?= $_('Einstellung') ?></th>
                <th><?= $_('Wert') ?></th>
            </tr>
        </thead>
        <tbody>
    <? foreach ($options as $key => $option): ?>
        <? if ($option['type'] === 'textarea'): ?>
            <tr>
                <td colspan="2">
                    <label for="option-<?= md5($key) ?>">
                        <?= htmlReady($option['description']) ?>
                    </label><br>

                    <textarea name="<?= htmlReady($option['key']) ?>" class="add_toolbar"><?= htmlReady($option['value']) ?></textarea>
                </td>
            </tr>
        <? elseif ($option['type'] === 'i18n'): ?>
            <tr>
                <td colspan="2">
                    <label for="option-<?= md5($key) ?>">
                        <?= htmlReady($option['description']) ?>
                    </label><br>

                    <?= I18N::textarea($key, $option['value'], ['class' => 'add_toolbar']) ?>
                </td>
            </tr>
        <? else: ?>
            <tr>
                <td>
                    <label for="option-<?= md5($key) ?>">
                        <?= htmlReady($option['description']) ?>
                    </label>
                </td>
                <td>
                <? if ($option['type'] === 'checkbox'): ?>
                    <input type="hidden" name="<?= htmlReady($option['key']) ?>" value="0">
                    <input type="checkbox" name="<?= htmlReady($option['key']) ?>"
                           id="option-<?= md5($key) ?>" value="1"
                           class="studip-checkbox"
                           <? if ((bool)$option['value']) echo 'checked'; ?>
                           <? if ($option['activates']) printf('data-activates="#option-%s"', md5($option['activates'])); ?>>
                    <label for="option-<?= md5($key) ?>"></label>
                <? else: ?>
                    <input type="<?= $option['type'] ?>" name="<?= $option['key'] ?>"
                           id="option-<?= md5($key) ?>"
                           value="<?= htmlReady($option['value']) ?>">
                <? endif; ?>
                </td>
            </tr>
        <? endif; ?>
    <? endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2">
                    <?= Studip\Button::createAccept($_('Speichern')) ?>
                </td>
        </tfoot>
    </table>
</form>

<? if (!$visible_for_nobody): ?>
    <?= MessageBox::info($_('Hinweise'), [
        $_('Bei Aktivierung der RSS Feeds muss das Plugin für nobody freigegeben werden, damit die Feeds ohne Login abgerufen werden können.'),
    ]) ?>
<? endif; ?>
