<form name="add" method="post" action="<?= $link ?>">
    <input type="hidden" name="modus" value="save_thema">
    <input type="hidden" name="thema_id" value="<?= $t->getthemaid() ?>">
    <table class="default">
        <thead>
            <tr>
                <td colspan="2" class="table_header_bold">
                    <?= _('Thema anlegen/bearbeiten:') ?>
                </td>
            </tr>
        </thead>
        <tbody style="vertical-align: top;">
            <tr>
                <td>
                    <label for="titel"><?= _('Titel:') ?></label>
                </td>
                <td>
                    <input type="text" id="titel" name="titel"
                           value="<?= htmlready($t->gettitel()) ?>" style="width:500px;">
                </td>
            </tr>
            <tr>
                <td>
                    <label for="beschreibung"><?= _('Beschreibung:') ?></label>
                </td>
                <td>
                    <textarea name="beschreibung" id="beschreibung" style="width:500px; height:150px;"><?= htmlready($t->getbeschreibung()) ?></textarea>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="thema_perm"><?= _('Berechtigung:') ?></label>
                </td>
                <td>
                    <select name="thema_perm" id="thema_perm">
                    <? foreach (words('autor tutor dozent admin root') as $p): ?>
                        <option <? if ($t->getperm() == $p) echo 'selected'; ?>><?= $p ?></option>
                    <? endforeach; ?>
                    </select>
                    <small>
                        <?= _('Diese Berechtigung bezieht sich auf die Benutzer, die einen Artikel '
                             .'erstellen dürfen. Betrachten können alle Benutzer!') ?>
                    </small>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="visible"><?= _('sichtbar:') ?></label>
                </td>
                <td>
                    <input type="checkbox" id="visible" name="visible" value="1" <? if ($t->getvisible()) echo'checked';?>>
                </td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" align="center">
                    <?= Studip\Button::createAccept(_('Speichern'), 'submit', array('title' => _('Das Thema speichern'))) ?>
                    <?= Studip\LinkButton::createCancel(_('Abbrechen'), $link_exit, array('title' => _('Die Änderungen verwerfen'))) ?>
                </td>
            </tr>
        </tfoot>
    </table>
</form>