<?php
$format_duration = function ($duration, $now = null) use ($_, $_n) {
    $formatted  = sprintf($_n('%u Tag', '%u Tage', $duration), $duration);
    $formatted .= ' - ';
    $formatted .= strftime($_('bis zum %d.%m.%Y'), strtotime('+' . $duration . ' days', $now ?: time()));

    return $formatted;
};
$expired_test = function ($duration, $now = null) {
    return strtotime("+{$duration} days", $now ?: time()) <= time();
};
?>
<form method="post" action="<?= $controller->url_for("article/store/{$article->id}", ['return_to' => Request::get('return_to')]) ?>" class="default">
    <?= CSRFProtection::tokenTag() ?>
    <input type="hidden" name="studip_ticket" value="<?= get_ticket() ?>">

    <fieldset>
        <legend class="hide-in-dialog">
            <?= $article->isNew() ? $_('Anzeige erstellen') : $_('Anzeige bearbeiten') ?>
        </legend>

        <label>
            <?= $_('Thema') ?>

            <select required name="thema_id" class="has-disclaimer">
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
        </label>

        <label>
            <?= $_('Titel') ?>
            <input required type="text" name="titel"
                   value="<?= htmlready($article->titel) ?>">
        </label>

        <label>
            <?= $_('Inhalt') ?>
            <textarea name="beschreibung" class="add_toolbar wysiwyg"><?= htmlready($article->beschreibung) ?></textarea>
        </label>

        <label>
            <?= $_('Laufzeit') ?>
            <small><?= $_('Nach Ablauf dieser Frist wird die Anzeige automatisch gelöscht.') ?></small>

            <select name="duration">
            <? for ($i = 1; $i <= Config::Get()->BULLETIN_BOARD_DURATION; $i += 1): ?>
                <option value="<?= $i ?>" <? if (($article->duration ?: Config::Get()->BULLETIN_BOARD_DURATION) == $i) echo 'selected'; ?>
                        <? if ($expired_test($i, $article->mkdate)) echo 'disabled'; ?>>
                    <?= $format_duration($i, $article->mkdate) ?>
                </option>
            <? endfor; ?>
            </select>
        </label>

        <input type="hidden" name="visible" value="0">
        <label>
            <input type="checkbox" name="visible" value="1"
                   <? if ($article->visible || $article->isNew()) echo 'checked'; ?>>
            <?= $_('Sichtbar') ?>
        </label>

    <? if ($rss_enabled): ?>
        <input type="hidden" name="publishable" value="0">
        <label>
            <input type="checkbox" name="publishable" value="1"
                   <? if ($article->publishable || $article->isNew())  echo 'checked'; ?>>
             <?= $_('Diese Anzeige darf im RSS-Feed veröffentlich werden.') ?>
        </label>
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
