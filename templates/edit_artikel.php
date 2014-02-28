<?= $message ?>

<h3>Anzeige anlegen/bearbeiten:</h3>

<form name="add" method="post" action="<?= $link_thema ?>">
    <input type="hidden" name="artikel_id" value="<?=$a->getartikelid()?>" />
    <table border="0" cellpadding="5" cellspacing="0" width="100%">
        <tr class="steel1">
            <td>Thema:</td>
            <td>
            <select name="thema_id" style="width:500px;">
                <option value="nix">-- Kategorie ausw�hlen --</option>
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
            <td>
                <textarea name="beschreibung" style="width:500px; height:300px;" class="add_toolbar"><?= htmlready($a->getbeschreibung()) ?></textarea>
            </td>
        </tr>
        <tr class="steelgraulight">
            <td>Laufzeit:</td>
            <td><b><?= ($zeit/24/60/60) ?> Tage</b>. Nach Ablauf dieser Frist wird die Anzeige automatisch nicht mehr angezeigt.</td>
        </tr>
        <tr class="steel1">
            <td>sichtbar:</td>
            <td><input type="checkbox" name="visible" value="1" <? if($a->getvisible()) echo'checked="checked"';?> /></td>
        </tr>
        <tr class="steelgraulight">
            <td>Ver&ouml;ffentlichung:</td>
            <td><input type="checkbox" name="publishable" value="1" <? if($a->getPublishable()) echo'checked="checked"';?> />Diese Anzeige darf im RSS Feed und anderen Portalen ver�ffentlich werden (z.B. ich-will-wissen.de).</td>
        </tr>
        <tr>
            <td colspan="2" align="center">
            <!-- Laufzeit bis zum <?= date("d.m.y",($a->getmkdate() ? $a->getmkdate() : time()) + $zeit )?><br/> -->
                <?= Studip\Button::createAccept(_('Speichern'), 'speichern') ?>
                <?= Studip\LinkButton::createCancel(_('Abbrechen'), $link) ?>
                <a href="http://hilfe.studip.de/index.php/Basis.VerschiedenesFormat?setstudipview=dozent&setstudiplocationid=default" target="_blank">Formatierungshilfen</a>
            </td>
        </tr>
    </table>
</form>