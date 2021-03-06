<form method="post" action="<?= $controller->store($category) ?>" class="default">
    <?= CSRFProtection::tokenTag() ?>
    <input type="hidden" name="studip_ticket" value="<?= get_ticket() ?>">

    <fieldset>
        <legend class="hide-in-dialog">
        <? if ($category->isNew()): ?>
            <?= $_('Thema anlegen') ?>
        <? else: ?>
            <?= $_('Thema bearbeiten') ?>
        <? endif; ?>
        </legend>

        <label>
            <?= $_('Titel') ?>
            <?= I18n::input('titel', $category->titel, ['required' => '']) ?>
        </label>

        <label>
            <?= $_('Beschreibung') ?>
            <?= I18n::textarea('beschreibung', $category->beschreibung) ?>
        </label>

        <label>
            <?= $_('Berechtigung') ?>
            <?= tooltipIcon($_('Diese Berechtigung bezieht sich auf die Benutzer, die einen Artikel erstellen dürfen.') . ' ' .
                                $_('Betrachten können alle Benutzer!')) ?>

            <select name="thema_perm" id="perm">
            <? foreach (['autor', 'tutor', 'dozent', 'admin', 'root'] as $perm): ?>
                <option <? if ($category->perm === $perm) echo 'selected';?>>
                    <?= htmlReady($perm) ?>
                </option>
            <? endforeach; ?>
            </select>
        </label>

        <label>
            <?= $_('Kurzhinweis') ?>
            <?= tooltipIcon($_('Der Kurzhinweis wird angezeigt, wenn beim Erstellen einer Anzeige diese Kategorie ausgewählt wird.')) ?>

            <?= I18N::textarea('disclaimer', $category->disclaimer, [
                'class' => 'add_toolbar wysiwyg',
                'id'    => 'disclaimer',
            ]) ?>
        </label>

        <label>
            <?= $_('Regeln') ?>
            <?= tooltipIcon($_('Die Regeln werden oberhalb einer Kategorie in deren Übersicht angezeigt.')) ?>

            <?= I18N::textarea('terms', $category->terms, [
                'class' => 'add_toolbar wysiwyg',
                'id'    => 'terms',
                ]) ?>
        </label>

        <input type="hidden" name="display_terms_in_article" value="0">
        <label>
            <input type="checkbox" name="display_terms_in_article" value="1"
                   <? if ($category->display_terms_in_article) echo 'checked'; ?>>

            <?= $_('Regeln in Anzeige') ?>
            <?= tooltipIcon($_('Die Regeln werden zusätzlich unterhalb einer Anzeige angezeigt.')) ?>
        </label>

        <input type="hidden" name="visible" value="0">
        <label for="visibility">
            <input type="checkbox" name="visible" value="1"
                   <? if ($category->visible || $category->isNew()) echo 'checked'; ?>>
            <?= $_('Sichtbar') ?>
        </label>

    <? if ($rss_enabled): ?>
        <input type="hidden" name="publishable" value="0">
        <label>
            <input type="checkbox" name="publishable" id="publishable" value="1" <? if ($category->publishable || $category->isNew())  echo 'checked'; ?>>
            <?= $_('Veröffentlichung') ?>
            <?= tooltipIcon($_('Anzeigen dieses Thema dürfen im RSS-Feed veröffentlich werden.')) ?>
        </label>
    <? endif; ?>

        <div data-dialog-button>
            <?= Studip\Button::createAccept($_('Speichern')) ?>
            <?= Studip\LinkButton::createCancel(
                $_('Abbrechen'),
                $controller->viewURL($category)
            ) ?>
        </div>
    </fieldset>
</form>
