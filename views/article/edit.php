<h2 class="hide-in-dialog">
    <?= $article->isNew() ? _('Anzeige erstellen') : _('Anzeige bearbeiten') ?>
</h2>

<form method="post" action="<?= $controller->url_for('article/store/' . $article->id . '?return_to=' . Request::get('return_to')) ?>">
    <?= CSRFProtection::tokenTag() ?>
    <input type="hidden" name="studip_ticket" value="<?= get_ticket() ?>">
    
    <table class="default">
        <tbody>
            <tr>
                <td>
                    <label for="category_id"><?= _('Thema') ?></label>
                </td>
                <td>
                    <select required name="thema_id" id="category_id">
                        <option value="">- <?= _('Kategorie auswählen') ?> -</option>
                    <? foreach ($categories as $category): ?>
                        <option value="<?= $category->id ?>" <? if ($category->id === $article->thema_id) echo 'selected'; ?>>
                            <?= htmlReady($category->titel) ?>
                        </option>
                    <? endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="title"><?= _('Titel') ?></label>
                </td>
                <td>
                    <input required type="text" name="titel" id="title" value="<?= htmlready($article->titel) ?>">
                </td>
            </tr>
            <tr>
                <td>
                    <label for="description"><?= _('Inhalt') ?></label>
                </td>
                <td>
                    <textarea name="beschreibung" id="description" class="add_toolbar"><?= htmlready($article->beschreibung) ?></textarea>
                </td>
            </tr>
            <tr>
                <td><?= _('Laufzeit') ?></td>
                <td>
                    <strong><?= sprintf(_('%u Tage'), $expire_days) ?></strong>.
                    <?= _('Nach Ablauf dieser Frist wird die Anzeige automatisch gelöscht.') ?>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="visibility"><?= _('Sichtbar') ?></label>
                </td>
                <td>
                    <input type="hidden" name="visible" value="0">
                    <input type="checkbox" name="visible" id="visibility" value="1"
                           <? if($article->visible || $article->isNew()) echo 'checked'; ?>>
                </td>
            </tr>
        <? if ($rss_enabled): ?>
            <tr>
                <td>
                    <label for="publishable"><?= _('Veröffentlichung') ?></label>
                </td>
                <td>
                    <input type="hidden" name="publishable" value="0">
                    <input type="checkbox" name="publishable" id="publishable" value="1"
                           <? if ($article->publishable || $article->isNew())  echo 'checked'; ?>>
                     <?= _('Diese Anzeige darf im RSS-Feed veröffentlich werden.') ?>
                 </td>
            </tr>
        <? endif; ?>
        </tbody>
        <tfoot data-dialog-button>
            <tr>
                <td colspan="2" align="center">
                    <?= Studip\Button::createAccept(_('Speichern')) ?>
                    <?= Studip\LinkButton::createCancel(_('Abbrechen'), $controller->url_for('category/list')) ?>
                </td>
            </tr>
        </tfoot>
    </table>
</form>