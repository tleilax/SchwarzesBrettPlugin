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

    </fieldset>

    <fieldset>
        <legend><?= $_('Berechtigungen') ?></legend>

        <label>
            <?= $_('Thema betrachten ab Berechtigung') ?>
            <?= tooltipIcon(implode(' ', [
                $_('Diese Berechtigung bezieht sich auf die Personen, die dieses Thema sehen dürfen.'),
                $_('Eine Person muss mindestens diese Berechtigung haben, um das Thema sehen zu können.'),
                $_('Wird kein Wert angegeben, gibt es keine Anforderung.'),
            ])) ?>

            <select name="permissions[access_min]">
                <option value="">- <?= $_('Keine Angabe') ?> -</option>
            <? foreach (['autor', 'tutor', 'dozent', 'admin'] as $perm): ?>
                <option <? if ($category->perm_access_min === $perm) echo 'selected';?>>
                    <?= htmlReady($perm) ?>
                </option>
            <? endforeach; ?>
            </select>
        </label>

        <label>
            <?= $_('Thema nicht mehr betrachten ab Berechtigung') ?>
            <?= tooltipIcon(implode(' ', [
                $_('Diese Berechtigung bezieht sich auf die Personen, die dieses Thema sehen dürfen.'),
                $_('Eine Person darf diese Berechtigung nicht haben, um das Thema sehen zu können.'),
                $_('Wird kein Wert angegeben, gibt es keine Anforderung.'),
            ])) ?>

            <select name="permissions[access_max]">
                <option value="">- <?= $_('Keine Angabe') ?> -</option>
            <? foreach (['autor', 'tutor', 'dozent', 'admin'] as $perm): ?>
                <option <? if ($category->perm_access_max === $perm) echo 'selected';?>>
                    <?= htmlReady($perm) ?>
                </option>
            <? endforeach; ?>
            </select>
        </label>

        <label>
            <?= $_('Anzeigen erstellen') ?>
            <?= tooltipIcon($_('Diese Berechtigung bezieht sich auf die Personen, die einen Artikel erstellen dürfen.')) ?>

            <select name="permissions[create]" required>
            <? foreach (['autor', 'tutor', 'dozent', 'admin', 'root'] as $perm): ?>
                <option <? if ($category->perm_create === $perm) echo 'selected';?>>
                    <?= htmlReady($perm) ?>
                </option>
            <? endforeach; ?>
            </select>
        </label>
    </fieldset>

    <footer data-dialog-button>
        <?= Studip\Button::createAccept($_('Speichern')) ?>
        <?= Studip\LinkButton::createCancel(
            $_('Abbrechen'),
            $controller->viewURL($category)
        ) ?>
    </footer>
</form>
