<form method="post" action="<?= $controller->url_for('article/store/' . $article->id . '?return_to=' . Request::get('return_to')) ?>" class="studip_form">
    <?= CSRFProtection::tokenTag() ?>
    <input type="hidden" name="studip_ticket" value="<?= get_ticket() ?>">
    
    <fieldset>
        <legend class="hide-in-dialog">
            <?= $article->isNew() ? _('Anzeige erstellen') : _('Anzeige bearbeiten') ?>
        </legend>

        <fieldset>
            <label for="category_id"><?= _('Thema') ?>:</label>
            <select required name="thema_id" id="category_id">
                <option value="">- <?= _('Kategorie auswählen') ?> -</option>
            <? foreach ($categories as $category): ?>
                <option value="<?= $category->id ?>" <? if ($category->id === $article->thema_id) echo 'selected'; ?>>
                    <?= htmlReady($category->titel) ?>
                </option>
            <? endforeach; ?>
            </select>
        </fieldset>
    
        <fieldset>
            <label for="title"><?= _('Titel') ?></label>
            <input required type="text" name="titel" id="title" value="<?= htmlready($article->titel) ?>">
        </fieldset>

        <fieldset>
            <label for="description"><?= _('Inhalt') ?></label>
            <textarea name="beschreibung" id="description" class="add_toolbar"><?= htmlready($article->beschreibung) ?></textarea>
        </fieldset>

        <fieldset>
            <label>
                <?= _('Laufzeit') ?>:
                <strong><?= sprintf(_('%u Tage'), $expire_days) ?></strong>.
                <?= _('Nach Ablauf dieser Frist wird die Anzeige automatisch gelöscht.') ?>
            </label>
        </fieldset>

        <fieldset>
            <input type="hidden" name="visible" value="0">
            <label for="visibility">
                <input type="checkbox" name="visible" id="visibility" value="1"
                       <? if($article->visible || $article->isNew()) echo 'checked'; ?>>
                <?= _('Sichtbar') ?>
            </label>
        </fieldset>

    <? if ($rss_enabled): ?>
        <fieldset>
            <input type="hidden" name="publishable" value="0">
            <label for="publishable">
                <input type="checkbox" name="publishable" id="publishable" value="1"
                       <? if ($article->publishable || $article->isNew())  echo 'checked'; ?>>
                 <?= _('Diese Anzeige darf im RSS-Feed veröffentlich werden.') ?>
            </label>
        </fieldset>
    <? endif; ?>

        <div data-dialog-button>
            <?= Studip\Button::createAccept(_('Speichern')) ?>
            <?= Studip\LinkButton::createCancel(_('Abbrechen'),
                                                $controller->url_for('category/list')) ?>
        </div>
    </fieldset>
</form>
