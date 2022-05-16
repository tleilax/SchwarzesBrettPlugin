<?php
/**
 * @var Admin_DomainBlacklistController $controller
 * @var SchwarzesBrett\DomainBlacklist[] $blacklisted_domains
 */
?>
<form action="<?= $controller->delete() ?>" method="post" class="default">
    <?= CSRFProtection::tokenTag() ?>

    <table class="default" id="blacklisted-domains-table">
        <colgroup>
            <col style="width: 24px">
            <col>
            <col style="width: 24px">
        </colgroup>
        <thead>
            <tr>
                <th>
                    <input type="checkbox"
                           data-proxyfor="#blacklisted-domains-table tbody :checkbox"
                           data-activates="#blacklisted-domains-table tfoot .button">
                </th>
                <th>
                    <?= $_('Gesperrte Nuzterdomäne') ?>
                </th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        <? if (count($blacklisted_domains) === 0): ?>
            <tr>
                <td colspan="3" class="empty">
                    <?= $_('Es wurden noch keine Nutzerdomänen für den Zugriff auf das Schwarze Brett gesperrt.') ?>
                </td>
            </tr>
        <? endif; ?>
        <? foreach ($blacklisted_domains as $domain): ?>
            <tr>
                <td>
                    <input type="checkbox" name="ids[]" value="<?= htmlReady($domain->id) ?>">
                </td>
                <td>
                    <?= htmlReady($domain->name) ?>
                </td>
                <td>
                    <?= Icon::create('trash')->asInput(tooltip2($_('Diesen Eintrag löschen')) + [
                        'data-confirm' => $_('Soll dieser Eintrag wirklich gelöscht werden?'),
                        'formaction'   => $controller->delete($domain),
                    ]) ?>
                </td>
            </tr>
        <? endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3">
                    <?= Studip\Button::create(
                        $_('Alle markierten Einträge löschen'),
                        'delete',
                        ['data-confirm' => $_('Sollen die markierten Einträge wirklich gelöscht werden?')]
                    ) ?>
                </td>
            </tr>
        </tfoot>
    </table>
</form>
