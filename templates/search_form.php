<!-- plugin: schwarzesbrett, template: search_form -->
<form name="search_form" method="post" action="<?=$link?>">
<table border="0" cellpadding="2" cellspacing="0" width="100%">
	<tr>
		<td class="topic"><b>Schwarzes Brett - Allgemeine Suche nach Anzeigen</b></td>
	</tr>
</table>
<div class="steel1" style="padding:5px;">Nach Anzeigen suchen:
	<input type="text" style="width:200px;" name="search_text" value="<?=htmlready($_REQUEST['search_text'])?>" />
	<?=makebutton("suchen","input", "nach Anzeigen suchen", "submit")?>
	<a href="<?=$link_back?>"><?=makebutton("zuruecksetzen","img", "zurücksetzen")?></a>
</div>
<div class="steelgraulight" style="padding:5px;">
	Aktuelle Laufzeit von Anzeigen: max. <b><?=($zeit/(24 * 60 * 60))?></b> Tage
</div>
</form>

