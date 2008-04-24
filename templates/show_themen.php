<!-- plugin: schwarzesbrett, template: show_themen -->
<form name="search_form" method="post" action="<?=$link_search?>">
<table border="0" cellpadding="2" cellspacing="0" width="100%">
	<tr>
		<td class="topic"><b>Allgemeine Suche nach Anzeigen:</b></td>
	</tr>
</table>
<div class="steel1" style="padding:5px;">Nach Anzeigen suchen:
	<input type="text" style="width:200px;" name="search_text" value="<?=htmlready($_REQUEST['search_text'])?>" />
	<?=makebutton("suchen","input", "nach Anzeigen suchen", "submit")?>
	<a href="<?=$link_back?>"><?=makebutton("zuruecksetzen","img", "zurücksetzen")?></a>
</div>
</form>
<br/>
<table border="0" cellpadding="2" cellspacing="0" width="100%">
	<tr>
		<td class="topic"><b>Themenübersicht:</b></td>
	</tr>
</table>
<? if($keinethemen): ?>
<div class="steel1" style="padding:5px;">
	Zur Zeit sind keine Themengebiete vorhanden!
</div>
<? else: ?>








<? endif; if($rootaccess): ?>
<br/>
<table border="0" cellpadding="2" cellspacing="0" width="100%">
	<tr>
		<td class="topic"><b>Administration:</b></td>
	</tr>
</table>
<div class="steel1" style="padding:5px;">
	<a href="<?=$rootlink?>"><?=makeButton("neuanlegen", "img", "Neues Thema anlegen")?></a>
</div>
<? endif; ?>
