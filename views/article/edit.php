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
<form method="post" action="<?= $controller->store($article, ['return_to' => Request::get('return_to')]) ?>" class="default" enctype="multipart/form-data" data-secure>
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
            <? if ((string) $category->disclaimer): ?>
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

            <select name="duration" id="duration">
            <? for ($i = 1; $i <= Config::Get()->BULLETIN_BOARD_DURATION; $i += 1): ?>
                <option value="<?= $i ?>" <? if (($article->duration ?: Config::Get()->BULLETIN_BOARD_DURATION) == $i) echo 'selected'; ?>
                        <? if ($expired_test($i, $article->mkdate)) echo 'disabled class="activatable"'; ?>>
                    <?= $format_duration($i, $article->mkdate) ?>
                </option>
            <? endfor; ?>
            </select>
        </label>

    <? if (!$article->isNew() && $GLOBALS['perm']->have_perm('root')): ?>
        <input type="hidden" name="reset" value="0">
        <label>
            <input type="checkbox" name="reset" value="1" data-activates="#duration option.activatable">
            <?= $_('Anzeige verlängern') ?>
            <?= tooltipIcon($_('Hierdurch wird das Erstellungsdatum der Anzeige auf den aktuellen Zeitpunkt gesetzt und die Laufzeit zurückgesetzt')) ?>
        </label>
    <? endif; ?>

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
    </fieldset>

<? if (Config::get()->BULLETIN_BOARD_ALLOW_FILE_UPLOADS): ?>
    <fieldset>
        <legend><?= $_('Bilder') ?></legend>

        <div class="sb-file-upload" data-multiple-caption="<?= _('%u Bilder ausgewählt') ?>">
            <label for="files">
                <input type="file" id="files" name="images[]" accept="image/*"
                       multiple data-target-url="<?= $controller->link_for('files/upload') ?>">
                <?= _('Bild(er) auswählen') ?>
                <span class="drag-available"><?= _('oder hierher ziehen') ?></span>
                <span class="selected-files"></span>
            </label>
        </div>

        <table class="default sb-article-images-edit">
            <colgroup>
                <col width="100px">
                <col>
                <col width="24px">
            </colgroup>
            <thead>
                <tr>
                    <th><?= $_('Bild') ?></th>
                    <th><?= $_('Titel') ?></th>
                    <th class="actions">
                        <?= Icon::create('trash', Icon::ROLE_INFO)->asImg(tooltip2($_('Bild löschen?'))) ?>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr class="empty-placeholder">
                    <td colspan="3">
                        <?= $_('Es wurden keine Bilder hinterlegt') ?>
                    </td>
                </tr>
            <? foreach ($article->images as $image): ?>
                <tr>
                    <td>
                        <input type="hidden" name="img[<?= htmlReady($image->image->id) ?>][position]"
                               value="<?= (int) $image->position ?>">

                        <label for="imagetext-<?= htmlReady($image->image->id) ?>">
                            <?= $image->thumbnail->getImageTag(false, false) ?>
                        </label>
                    </td>
                    <td>
                        <input type="text" value="<?= htmlReady($image->image->description) ?>"
                               name="img[<?= htmlReady($image->image->id) ?>][title]"
                               id="imagetext-<?= htmlReady($image->image->id) ?>">
                    </td>
                    <td class="actions">
                        <input type="checkbox" class="studip-checkbox"
                               id="image-<?= htmlReady($image->image->id) ?>"
                               name="img[<?= htmlReady($image->image->id) ?>][delete]"
                               value="1">
                        <label for="image-<?= htmlReady($image->image->id) ?>"></label>
                    </td>
                </tr>
            <? endforeach; ?>
            </tbody>
        </table>
    </fieldset>
<? endif; ?>

    <footer data-dialog-button>
        <?= Studip\Button::createAccept($_('Speichern')) ?>
        <?= Studip\LinkButton::createCancel(
            $_('Abbrechen'),
            $controller->url_for('category/list')
        ) ?>
    </footer>
</form>
