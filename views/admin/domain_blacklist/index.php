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
            <col style="width: 10%">
            <col style="width: 24px">
        </colgroup>
        <thead>
            <tr>
                <th>
                    <input type="checkbox"
                           data-proxyfor="#blacklisted-domains-table tbody :checkbox"
                           data-activates="#blacklisted-domains-table tfoot .button">
                </th>
                <th><?= $_('Gesperrte Nuzterdomäne') ?></th>
                <th>
                    <abbr title="<?= $_('Ist der Zugang komplett gesperrt oder nur das Erstellen eigener Anzeigen untersagt?') ?>">
                        <?= $_('Komplett?') ?>
                    </abbr>
                </th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        <? if (count($blacklisted_domains) === 0): ?>
            <tr>
                <td colspan="4" class="empty">
                    <?= $_('Es wurden noch keine Nutzerdomänen für den Zugriff auf das Schwarze Brett gesperrt.') ?>
                </td>
            </tr>
        <? endif; ?>
        <? foreach ($blacklisted_domains as $domain): ?>
            <tr>
                <td>
                    <input type="checkbox" name="ids[]" value="<?= htmlReady($domain->id) ?>">
                </td>
                <td><?= htmlReady($domain->name) ?></td>
                <td>
                    <a href="<?= $controller->toggle($domain) ?>">
                    <? if ($domain->restriction === SchwarzesBrett\DomainBlacklist::RESTRICTION_COMPLETE): ?>
                        <?= Icon::create('checkbox-checked')->asImg(tooltip2($_('Zugriff komplett verboten'))) ?>
                    <? else: ?>
                        <?= Icon::create('checkbox-unchecked')->asImg(tooltip2($_('Erstellen von Anzeigen untersagt'))) ?>
                    <? endif; ?>
                    </a>
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
                <td colspan="4">
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
