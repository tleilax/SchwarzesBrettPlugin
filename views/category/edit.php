<form method="post" action="<?= $controller->url_for("category/store/{$category->id}") ?>" class="default">
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
            <input required type="text" name="titel"
                   value="<?= htmlReady($category->titel) ?>">
        </label>

        <label>
            <?= $_('Beschreibung') ?>
            <textarea required name="beschreibung" id="description"><?= htmlReady($category->beschreibung) ?></textarea>
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

            <textarea name="disclaimer" class="add_toolbar wysiwyg" style="min-height: 4em"><?= htmlReady($category->disclaimer) ?></textarea>
        </label>

        <label>
            <?= $_('Regeln') ?>
            <?= tooltipIcon($_('Die Regeln werden oberhalb einer Kategorie in deren Übersicht angezeigt.')) ?>

            <textarea class="add_toolbar wysiwyg" name="terms" id="terms"><?= htmlReady($category->terms) ?></textarea>
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

    <? if (count(UserDomain::getUserDomains()) > 0): ?>
        <label>
            <?= $_('Nutzerdomänen') ?>
            <?= tooltipIcon($_('Wählen Sie keine Domäne aus, ist diese Kategorie für alle Nutzer sichtbar')) ?>
            <div>
                <select multiple name="domains[]" class="select2_multiple" data-placeholder="<?= $_('Bitte wählen Sie ggf. die Domänen aus, für die diese Kategorie sichtbar sein soll') ?>">
                    <option value="null" <? if (in_array('null', $category->domains)) echo 'selected'; ?>>
                        <?= $_('Nulldomäne') ?>
                    </option>
                <? foreach (UserDomain::getUserDomains() as $domain): ?>
                    <option value="<?= $domain->getID() ?>" <? if (in_array($domain->getID(), $category->domains)) echo 'selected'; ?>>
                        <?= htmlReady($domain->getName()) ?>
                    </option>
                <? endforeach; ?>
                </select>
            </div>
        </label>
    <? endif; ?>

        <div data-dialog-button>
            <?= Studip\Button::createAccept($_('Speichern')) ?>
            <?= Studip\LinkButton::createCancel(
                $_('Abbrechen'),
                $controller->url_for("category/view/{$category->id}")
            ) ?>
        </div>
    </fieldset>
</form>
