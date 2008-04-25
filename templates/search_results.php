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
<table border="0" cellpadding="2" cellspacing="0" width="100%">
	<tr>
		<th><?=$result['thema_titel']?></th>
	</tr>
</table>
<table border="0" cellpadding="5" cellspacing="0" width="100%">
	<? foreach ($result['artikel'] as $index=>$a): ?>
	<tr>
		<td class="<?=($index%2==0)?'steel1':'steelgraulight'?>">
		<?=$a ?>
		</td>
	</tr>
	<? endforeach; ?>
</table>
<? endforeach; ?>

<pre>
<? #print_r($results); ?>
</pre>