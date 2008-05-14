<!-- plugin: schwarzesbrett, template: edit_thema -->
<br/>
<table border="0" cellpadding="2" cellspacing="0" width="100%">
	<tr>
		<td class="topic"><b>Anzeige anlegen/bearbeiten:</b></td>
	</tr>
</table>

<form name="add" method="post" action="<?=$link_thema?>">
	<input type="hidden" name="modus" value="add_artikel" />
	<input type="hidden" name="artikel_id" value="<?=$a->getartikelid()?>" />
	<table border="0" cellpadding="5" cellspacing="0" width="100%">
		<tr class="steel1">
			<td>Thema:</td>
			<td>
			<select name="thema_id" style="width:500px;">
			<? foreach ($themen as $thema): ?>
				<option value="<?=$thema->getThemaId() ?>" <?= ($thema->getThemaId() == $thema_id)? 'selected="selected"':''?>>	<?=$thema->getTitel() ?></option>
			<? endforeach; ?>
			</select>
		</td>
		</tr>
		<tr class="steelgraulight">
			<td>Titel:</td>
			<td><input type="text" name="titel" value="<?=htmlready($a->gettitel())?>" style="width:500px;" /></td>
		</tr>
		<tr class="steel1">
			<td valign="top">Beschreibung:</td>
			<td><textarea name="beschreibung" style="width:500px; height:150px;"><?=htmlready($a->getbeschreibung())?></textarea></td>
		</tr>
		<tr class="steelgraulight">
			<td>sichtbar:</td>
			<td><input type="checkbox" name="visible" value="1" <? if($a->getvisible()) echo'checked="checked"';?> /></td>
		</tr>
		<tr class="steel1">
			<td colspan="2" align="center">
			<!-- Laufzeit bis zum <?=date("d.m.y",($a->getmkdate()?$a->getmkdate():time())+$zeit)?><br/> -->
			<?=makebutton("speichern", "input", "Die Anzeige speichern")?>
			<a href="<?=$link?>"><?=makebutton("abbrechen","img", "abbrechen und zurück zur Übersicht")?></a>
			<a href="show_smiley.php" target="_blank">Smileys</a>
			<a href="http://hilfe.studip.de/index.php/Basis.VerschiedenesFormat?setstudipview=dozent&setstudiplocationid=default" target="_blank">Formatierungshilfen</a>
			</td>
		</tr>
	</table>
</form>

<br/>
<table border="0" cellpadding="2" cellspacing="0" width="100%">
	<tr>
		<td class="topic"><b>Hinweise zur Anzeigenerstellung:</b></td>
	</tr>
</table>
<div class="steel1" style="padding:5px;">
	<ul>
		<li>Jede Anzeige <b>sollte</b> einen <b>universitären Bezug</b> haben, alle anderen Anzeigen werden entfernt.</li>
		<li>Eine Anzeige hat zur Zeit eine Laufzeit von <b><?=($zeit/24/60/60)?> Tagen</b>. Nach Ablauf dieser Frist wird die Anzeige automatisch nicht mehr angezeigt.</li>
		<li>Sobald eine Anzeige nicht mehr aktuell ist (z.b. in dem Fall, dass ein Buch verkauft oder eine Mitfahrgelegenheit gefunden wurde), sollte die Anzeige durch den Autor entfernt werden.</li>
		<li>Unter der Beschreibung wird automatisch ein Link zu Ihrer Benutzerhomepage eingebunden. <br />Außerdem können andere Nutzer direkt über einen Button antworten. Diese Nachrichten erhalten Sie als Stud.IP interne Post!</li>
		<li>Bitte die Anzeigen in die dafür vorgesehenen Themen einstellen, damit dieses schwarze Brett so übersichtlich wie möglich bleibt.</li>
		<li>Wird ein Gegenstand oder eine Dienstleistung gegen Bezahlung angeboten, sollte der Betrag genannt werden, um unnötige Nachfragen zu vermeiden.</li>
		<li>Jede Anzeige, die gegen diese Nutzungsordnung verstößt, wird umgehend entfernt.</li>
	</ul>
</div>
<br/>