<!-- plugin: schwarzesbrett, template: list_themen -->
<br/>
<table border="0" cellpadding="2" cellspacing="0" width="100%">
	<tr>
		<td class="topic"><b>Schwarzes Brett - Themenübersicht:</b></td>
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
		<td class="topic"><b>Schwarzes Brett - Administration:</b></td>
	</tr>
</table>
<div class="steel1" style="padding:5px;">
	<a href="<?=$rootlink?>"><?=makeButton("neuanlegen", "img", "Neues Thema anlegen")?></a>
</div>
<? endif; ?>
