<form name="add" method="post" action="<?= $link_thema ?>">
    <input type="hidden" name="artikel_id" value="<?=$a->getartikelid()?>" />
    <table border="0" cellpadding="5" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th colspan="2" class="table_header_bold">
                    <?= _('Anzeige anlegen/bearbeiten:') ?>
                </th>
            </tr>
        </thead>
        <tbody>
            <tr class="steel1">
                <td>Thema:</td>
                <td>
                <select name="thema_id" style="width:500px;">
                    <option value="nix">-- Kategorie auswählen --</option>
                <? foreach ($themen as $thema): ?>
                    <option value="<?=$thema->getThemaId() ?>" <?= ($thema->getThemaId() == $thema_id)? 'selected="selected"':''?>> <?= htmlReady($thema->getTitel()) ?></option>
                <? endforeach; ?>
                </select>
            </td>
            </tr>
            <tr class="steelgraulight">
                <td>Titel:</td>
                <td><input type="text" name="titel" value="<?= htmlready($a->gettitel()) ?>" style="width:500px;" maxlength="80" /></td>
            </tr>
            <tr class="steel1">
                <td valign="top">Beschreibung:</td>
                <td><textarea name="beschreibung" class="add_toolbar" style="width:500px; height:300px;"><?= htmlready($a->getbeschreibung()) ?></textarea></td>
            </tr>
            <tr class="steelgraulight">
                <td>Laufzeit:</td>
                <td><b><?= ($zeit/24/60/60) ?> Tage</b>. Nach Ablauf dieser Frist wird die Anzeige automatisch nicht mehr angezeigt.</td>
            </tr>
            <tr class="steel1">
                <td>sichtbar:</td>
                <td><input type="checkbox" name="visible" value="1" <? if($a->getvisible()) echo'checked="checked"';?> /></td>
            </tr>
            <tr>
                <td colspan="2" align="center">
                <!-- Laufzeit bis zum <?= date("d.m.y",($a->getmkdate() ? $a->getmkdate() : time()) + $zeit )?><br/> -->
                <?= Studip\Button::createAccept(_('Speichern'), 'speichern', array('title' => _('Die Anzeige speichern'))) ?>
                <?= Studip\LinkButton::createCancel(_('Abbrechen'), $link, array('title' => _('Abbrechen und zurück zur Übersicht'))) ?>
            </tr>
        </tbody>
    </table>
</form>