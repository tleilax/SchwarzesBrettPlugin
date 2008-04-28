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
		<td class="topic"><b>Ergebnisse alphabetisch sortiert, gruppiert nach Themen:</b></td>
	</tr>
</table>
<? foreach ($results as $result): ?>
<table border="0" cellpadding="2" cellspacing="0" width="100%" style="margin-bottom:3px;">
	<tr class="steel1">
		<td>
		<div style="float:left">
			<b><?=$result['thema_titel']?></b><br/>
		</div>
		<div style="float:right">
				<a href="javascript:toogleThema('<?=$result['thema_id']?>');"><img src="<?=$pluginpfad ?>/images/table_refresh.png" alt="Artikel auf/zuklappen" title="Alle Artikel anzeigen oder verstecken (auf/zuklappen)" /></a>
		<? if($rootaccess): ?>
				<a href="<?=$link_edit ?>&thema_id=<?=$result['thema_id']?>"><img src="<?=$pluginpfad ?>/images/table_edit.png" alt="Thema bearbeiten" title="Thema bearbeiten" /></a>
				<a href="<?=$link_delete ?>&thema_id=<?=$result['thema_id']?>"><img src="<?=$pluginpfad ?>/images/cross.png" alt="Thema löschen" title="Thema inkl. aller Anzeigen löschen" /></a>
		<? endif; ?>
		</div>
		<div style="clear:both; border-bottom: 1px solid #8e8e8e;"></div>
		<div id="list_<?=$result['thema_id']?>">
		<table border="0" cellpadding="5" cellspacing="0" width="100%">
			<? foreach ($result['artikel'] as $index=>$a): ?>
			<tr>
				<td class="<?=($index%2==0)?'steel1':'steelgraulight'?>">
				<?=$a ?>
				</td>
			</tr>
			<? endforeach; ?>
		</table>
		</div>		
		</td> 
	</tr>
</table>

<? endforeach; ?>

<pre>
<? #print_r($results); ?>
</pre>