<?php
/**
 * @var Admin_DomainBlacklistController $controller
 * @var callable $_
 * @var UserDomain[] $domains
 * @var string[] $blacklisted_domains
 */
?>
<form action="<?= $controller->store() ?>" class="default" method="post">
    <?= CSRFProtection::tokenTag() ?>

    <fieldset>
        <legend><?= $_('Nutzerdomäne für das Schwarze Brett sperren') ?></legend>

        <label>
            <?= $_('Nutzerdomäne(n) auswählen') ?>
            <select name="domain_ids[]" multiple class="nested-select">
            <? foreach ($domains as $domain): ?>
                <option value="<?= htmlReady($domain->id) ?>"
                        <? if (in_array($domain->id, $blacklisted_domains)) echo 'disabled'; ?>>
                    <?= htmlReady($domain->name) ?>
                </option>
            <? endforeach; ?>
            </select>
        </label>
    </fieldset>

    <footer data-dialog-button>
        <?= Studip\Button::createAccept($_('Speichern')) ?>
        <?= Studip\LinkButton::createCancel(
            $_('Abbrechen'),
            $controller->indexURL()
        ) ?>
    </footer>
</form>
