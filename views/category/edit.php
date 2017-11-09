<form method="post" action="<?= $controller->url_for('category/store/' . $category->id) ?>" class="studip_form">
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

        <fieldset>
            <label for="title"><?= $_('Titel') ?></label>
            <input required type="text" name="titel" id="title" value="<?= htmlReady($category->titel) ?>">
        </fieldset>

        <fieldset>
            <label for="description"><?= $_('Beschreibung') ?></label>
            <textarea name="beschreibung" id="description"><?= htmlReady($category->beschreibung) ?></textarea>
        </fieldset>

        <fieldset>
            <label for="perm">
                <?= $_('Berechtigung') ?>
                <?= tooltipIcon($_('Diese Berechtigung bezieht sich auf die Benutzer, die einen Artikel erstellen dürfen.') . ' ' .
                                $_('Betrachten können alle Benutzer!')) ?>
            </label>
            <select name="thema_perm" id="perm">
            <? foreach (words('autor tutor dozent admin root') as $perm): ?>
                <option <? if ($category->perm === $perm) echo 'selected';?>>
                    <?= htmlReady($perm) ?>
                </option>
            <? endforeach; ?>
            </select>
        </fieldset>

        <fieldset>
            <label for="dislaimer">
                <?= $_('Kurzhinweis') ?>
                <?= tooltipIcon($_('Der Kurzhinweis wird angezeigt, wenn beim Erstellen einer Anzeige diese Kategorie ausgewählt wird.')) ?>
            </label>
            <textarea name="disclaimer" id="disclaimer" class="add_toolbar wysiwyg" style="min-height: 4em"><?= htmlReady($category->disclaimer) ?></textarea>
        </fieldset>

        <fieldset>
            <label for="terms">
                <?= $_('Regeln') ?>
                <?= tooltipIcon($_('Die Regeln werden oberhalb einer Kategorie in deren Übersicht angezeigt.')) ?>
            </label>
            <textarea class="add_toolbar wysiwyg" name="terms" id="terms"><?= htmlReady($category->terms) ?></textarea>
        </fieldset>

        <fieldset>
            <input type="hidden" name="display_terms_in_article" value="0">
            <label for="terms">
                <input type="checkbox" name="display_terms_in_article" value="1"
                        <? if ($category->display_terms_in_article) echo 'checked'; ?>>

                <?= $_('Regeln in Anzeige') ?>
                <?= tooltipIcon($_('Die Regeln werden zusätzlich unterhalb einer Anzeige angezeigt.')) ?>
            </label>
        </fieldset>

        <fieldset>
            <input type="hidden" name="visible" value="0">
            <label for="visibility">
                <input type="checkbox" name="visible" value="1" id="visibility"
                        <? if ($category->visible || $category->isNew()) echo 'checked'; ?>>
                <?= $_('Sichtbar') ?>
            </label>
        </fieldset>

    <? if ($rss_enabled): ?>
        <fieldset>
            <input type="hidden" name="publishable" value="0">
            <label for="publishable">
                <input type="checkbox" name="publishable" id="publishable" value="1" <? if ($category->publishable || $category->isNew())  echo 'checked'; ?>>
                <?= $_('Veröffentlichung') ?>
                <?= tooltipIcon($_('Anzeigen dieses Thema dürfen im RSS-Feed veröffentlich werden.')) ?>
            </label>
        </fieldset>
    <? endif; ?>

        <div data-dialog-button>
            <?= Studip\Button::createAccept($_('Speichern')) ?>
            <?= Studip\LinkButton::createCancel(
                $_('Abbrechen'),
                $controller->url_for('category/view/' . $category->id)
            ) ?>
        </div>
    </fieldset>
</form>
