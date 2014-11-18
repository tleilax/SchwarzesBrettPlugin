<h2 class="hide-in-dialog"><?= _('Thema anlegen/bearbeiten') ?></h2>
<form method="post" action="<?= $controller->url_for('category/store/' . $category->id) ?>">
    <?= CSRFProtection::tokenTag() ?>
    <input type="hidden" name="studip_ticket" value="<?= get_ticket() ?>">
    
    <table class="default">
        <tbody>
            <tr>
                <td>
                    <label for="title"><?= _('Titel') ?></label>
                </td>
                <td>
                    <input required type="text" name="titel" id="title" value="<?= htmlReady($category->titel) ?>">
                </td>
            </tr>
            <tr>
                <td>
                    <label for="description"><?= _('Beschreibung') ?></label>
                </td>
                <td>
                    <textarea required name="beschreibung" id="description"><?= htmlReady($category->beschreibung) ?></textarea>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="perm"><?= _('Berechtigung') ?></label>
                </td>
                <td>
                    <select name="thema_perm" id="perm">
                    <? foreach (words('autor tutor dozent admin root') as $perm): ?>
                        <option <? if ($category->perm === $perm) echo 'selected';?>>
                            <?= htmlReady($perm) ?>
                        </option>
                    <? endforeach; ?>
                    </select>
                    <?= _('Diese Berechtigung bezieht sich auf die Benutzer, die einen Artikel erstellen dürfen.') ?>
                    <?= _('Betrachten können alle Benutzer!') ?>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="visibility"><?= _('Sichtbar') ?></label>
                </td>
                <td>
                    <input type="hidden" name="visible" value="0">
                    <input type="checkbox" name="visible" value="1" id="visibility"
                            <? if ($category->visible || $category->isNew()) echo 'checked'; ?>>
                </td>
            </tr>
        <? if ($rss_enabled): ?>
            <tr>
                <td><?= _('Veröffentlichung') ?></td>
                <td>
                    <input type="hidden" name="publishable" value="0">
                    <input type="checkbox" name="publishable" value="1" <? if ($category->publishable || $category->isNew())  echo 'checked'; ?>>
                    <?= _('Anzeigen dieses Thema dürfen im RSS-Feed veröffentlich werden.') ?>
                 </td>
            </tr>
        <? endif; ?>
        </tbody>
        <tfoot data-dialog-button>
            <tr>
                <td colspan="2" align="center">
                    <?= Studip\Button::createAccept(_('Speichern')) ?>
                    <?= Studip\LinkButton::createCancel(_('Abbrechen'), $controller->url_for('category/view/' . $category->id)) ?>
                </td>
            </tr>
        </tfoot>
    </table>
</form>