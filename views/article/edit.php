<?php
$format_duration = function ($duration, $now = null) {
    $formatted  = sprintf(ngettext('%u Tag', '%u Tage', $duration), $duration);
    $formatted .= ' - ';
    $formatted .= strftime($_('bis zum %d.%m.%Y'), strtotime('+' . $duration . ' days', $now ?: time()));

    return $formatted;
};
$expired_test = function ($duration, $now = null) {
    return strtotime('+' . $duration . ' days', $now ?: time()) <= time();
};
?>
<form method="post" action="<?= $controller->url_for('article/store/' . $article->id . '?return_to=' . Request::get('return_to')) ?>" class="studip_form">
    <?= CSRFProtection::tokenTag() ?>
    <input type="hidden" name="studip_ticket" value="<?= get_ticket() ?>">

    <fieldset>
        <legend class="hide-in-dialog">
            <?= $article->isNew() ? $_('Anzeige erstellen') : $_('Anzeige bearbeiten') ?>
        </legend>

        <fieldset>
            <label for="category_id"><?= $_('Thema') ?>:</label>
            <select required name="thema_id" id="category_id" class="has-disclaimer">
                <option value="">- <?= $_('Kategorie auswählen') ?> -</option>
            <? foreach ($categories as $category): ?>
                <option value="<?= $category->id ?>" <? if ($category->id === $article->thema_id) echo 'selected'; ?>>
                    <?= htmlReady($category->titel) ?>
                </option>
            <? endforeach; ?>
            </select>
    <? foreach ($categories as $category): ?>
        <? if ($category->disclaimer): ?>
            <div class="category-disclaimer" id="disclaimer-<?= $category->id ?>"
                   <? if ($category->id !== $article->thema_id) echo 'style="display: none;"'; ?>>
                <?= formatReady($category->disclaimer) ?>
            </div>
        <? endif; ?>
    <? endforeach; ?>
        </fieldset>

        <fieldset>
            <label for="title"><?= $_('Titel') ?></label>
            <input required type="text" name="titel" id="title" value="<?= htmlready($article->titel) ?>">
        </fieldset>

        <fieldset>
            <label for="description"><?= $_('Inhalt') ?></label>
            <textarea name="beschreibung" id="description" class="add_toolbar wysiwyg"><?= htmlready($article->beschreibung) ?></textarea>
        </fieldset>

        <fieldset>
            <label for="duration">
                <?= $_('Laufzeit') ?>
                <small><?= $_('Nach Ablauf dieser Frist wird die Anzeige automatisch gelöscht.') ?></small>
            </label>
            <select name="duration" id="duration">
            <? for ($i = 1; $i <= Config::Get()->BULLETIN_BOARD_DURATION; $i += 1): ?>
                <option value="<?= $i ?>" <? if (($article->duration ?: Config::Get()->BULLETIN_BOARD_DURATION) == $i) echo 'selected'; ?>
                        <? if ($expired_test($i, $article->mkdate)) echo 'disabled'; ?>>
                    <?= $format_duration($i, $article->mkdate) ?>
                </option>
            <? endfor; ?>
            </select>
        </fieldset>

        <fieldset>
            <input type="hidden" name="visible" value="0">
            <label for="visibility">
                <input type="checkbox" name="visible" id="visibility" value="1"
                       <? if ($article->visible || $article->isNew()) echo 'checked'; ?>>
                <?= $_('Sichtbar') ?>
            </label>
        </fieldset>

    <? if ($rss_enabled): ?>
        <fieldset>
            <input type="hidden" name="publishable" value="0">
            <label for="publishable">
                <input type="checkbox" name="publishable" id="publishable" value="1"
                       <? if ($article->publishable || $article->isNew())  echo 'checked'; ?>>
                 <?= $_('Diese Anzeige darf im RSS-Feed veröffentlich werden.') ?>
            </label>
        </fieldset>
    <? endif; ?>

        <div data-dialog-button>
            <?= Studip\Button::createAccept($_('Speichern')) ?>
            <?= Studip\LinkButton::createCancel(
                $_('Abbrechen'),
                $controller->url_for('category/list')
            ) ?>
        </div>
    </fieldset>
</form>
