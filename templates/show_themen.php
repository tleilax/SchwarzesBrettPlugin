<!-- plugin: schwarzesbrett, template: show_themen -->
<table border="0" cellpadding="2" cellspacing="2" width="100%">
	<tr>
	<td>
		<form name="search_form" method="post" action="<?=$link_search?>">
		<table border="0" cellpadding="2" cellspacing="0" width="100%">
			<tr>
				<td class="topic"><b>Allgemeine Suche nach Anzeigen:</b></td>
			</tr>
			<tr>
				<td class="steel1" style="padding:5px;">
				Nach Anzeigen suchen:
				<input type="text" style="width:200px;" name="search_text" value="<?=htmlready($_REQUEST['search_text'])?>" />
				<?=makebutton("suchen","input", "nach Anzeigen suchen", "submit")?>
				<a href="<?=$link_back?>"><?=makebutton("zuruecksetzen","img", "zur�cksetzen")?></a>
				</td>
			</tr>
		</table>
		</form>
	</td>
	<td>
		<table border="0" cellpadding="2" cellspacing="0" width="100%">
			<tr>
				<td class="topic"><b>Neue Anzeige erstellen</b></td>
			</tr>
			<tr>
				<td class="steel1" align="center" style="padding:5px;">
				<a href="<?=$link_artikel?>"><img class="button" src="<?=$pluginpfad ?>/images/anzeige-button.png" alt="Eine neue Anzeige erstellen" title="Eine neue Anzeige erstellen" /></a></td>
			</tr>
		</table>
	</td>
	</tr>
</table>
<br/>
<? if(count($lastArtikel) > 0): $last=count($lastArtikel); ?>
<table border="0" cellpadding="2" cellspacing="0" width="100%">
	<tr>
		<td class="topic"><b>Die <?=$last<10? $last:'10'; ?> neusten Anzeigen:</b></td>
	</tr>
</table>
<table border="0" cellpadding="2" cellspacing="0" width="100%">
	<tr class="steel1">
		<td valign="top" width="50%">
			<table border="0" cellpadding="5" cellspacing="0" width="100%">
				<? for ($i=0; $i<ceil($last/2); $i++):
				$a = $lastArtikel[$i]; ?>
				<tr>
					<td class="<?=($i%2==0)?'steel1':'steelgraulight'?>">
					<?=$a ?>
					</td>
				</tr>
				<? endfor; ?>
			</table>
		</td>
		<td valign="top" width="50%">
			<table border="0" cellpadding="5" cellspacing="0" width="100%">
				<? for ($i=ceil($last/2); $i<($last); $i++):
				$a = $lastArtikel[$i]; ?>
				<tr>
					<td class="<?=($i%2==0)?'steel1':'steelgraulight'?>">
					<?=$a ?>
					</td>
				</tr>
				<? endfor; ?>
			</table>
		</td>
	</tr>
</table>
<br/>
<? endif; ?>
<table border="0" cellpadding="2" cellspacing="0" width="100%">
	<tr>
		<td class="topic"><b>Themen�bersicht:</b></td>
	</tr>
</table>
<? if($keinethemen): ?>
<div class="steel1" style="padding:5px;">
	Zur Zeit sind keine Themengebiete vorhanden!
</div>
<? else: ?>
<table border="0" cellpadding="0" cellspacing="0" width="100%">
	<tr>
<? 	$tindex = 0; foreach ($results as $result): ?>
	<? if($tindex%$themen_rows == 0): ?>
	<td width="33%" valign="top">
	<? endif; $tindex++; ?>
	<div class="steel1" style="padding:2px; margin:3px">
		<div style="float:left">
			<a href="javascript:toogleThema('<?=$result['thema']->getThemaId() ?>');"><b><?=htmlReady($result['thema']->getTitel()) ?> <?=($result['countArtikel'] != 0)? '('.$result['countArtikel'].')':''?></b></a><br/>
			<span style="font-size: smaller"><?=htmlReady($result['thema']->getBeschreibung()) ?></span>
		</div>
		<div style="float:right">
				<a href="javascript:toogleThema('<?=$result['thema']->getThemaId() ?>');"><img src="<?=$pluginpfad ?>/images/table_refresh.png" alt="Artikel auf/zuklappen" title="Alle Artikel anzeigen oder verstecken (auf/zuklappen)" /></a>
		<? if($rootaccess): ?>
			<? if($result['thema']->getVisible() == 0): ?>
				<img src="<?=$pluginpfad ?>/images/exclamation.png" alt="nicht sichtbar" title="Dieses Thema ist f�r Benutzer nicht sichtbar" />
			<? endif; ?>
				<a href="<?=$link_edit ?>&thema_id=<?=$result['thema']->getThemaId() ?>"><img src="<?=$pluginpfad ?>/images/table_edit.png" alt="Thema bearbeiten" title="Thema bearbeiten" /></a>
				<a href="<?=$link_delete ?>&thema_id=<?=$result['thema']->getThemaId() ?>"><img src="<?=$pluginpfad ?>/images/cross.png" alt="Thema l�schen" title="Thema inkl. aller Anzeigen l�schen" /></a>
		<? endif; ?>
		</div>
		<div style="clear:both; border-bottom: 1px solid #8e8e8e;"></div>
		<div id="list_<?=$result['thema']->getThemaId() ?>" style="display: none;">
		<table border="0" cellpadding="5" cellspacing="0" width="100%">
			<? foreach ($result['artikel'] as $index=>$a): ?>
			<tr>
				<td class="<?=($index%2==0)?'steel1':'steelgraulight'?>">
				<?=$a ?>
				</td>
			</tr>
			<? endforeach; ?>
			<? if($result['thema']->getArtikelCount() == 0): ?>
			<tr>
				<td><span style="font-size: smaller;">keine Anzeigen vorhanden</span></td>
			</tr>
			<? endif; ?>
		</table>
		<? if($result['permission'] === true): ?>
		<div align="center" style="padding: 3px;">
		<a href="<?=$link_artikel?>&thema_id=<?=$result['thema']->getThemaId()?>"><img class="button" src="<?=$pluginpfad ?>/images/anzeige-button.png" alt="Eine neue Anzeige erstellen" title="Eine neue Anzeige erstellen" /></a>
		</div>
		<? endif; ?>
		</div>
	</div>
	<? if($tindex%$themen_rows == 0): ?>
	</td>
	<? endif; ?>
<?  endforeach; ?>
	</tr>
</table>
<br/>

<? endif; if($rootaccess): ?>
<table border="0" cellpadding="2" cellspacing="0" width="100%">
	<tr>
		<td class="topic" colspan="2"><b>Administration:</b></td>
	</tr>
	<tr class="steel1">
		<td width="150">Thema<br/><a href="<?=$rootlinknew?>"><?=makeButton("neuanlegen", "img", "Neues Thema anlegen")?></a></td>
		<td>Alle abgelaufene Artikel<br/><a href="<?=$rootlinkdelete?>"><?=makeButton("loeschen", "img", "Alle alten Artikel l�schen")?></a></td>
	</tr>
</table>
<br/>
<? endif; ?>
<table border="0" cellpadding="2" cellspacing="0" width="100%">
	<tr>
		<td class="topic"><b>Allgemeine Hinweise:</b></td>
	</tr>
</table>
<div class="steel1" style="padding:5px;">
	<ul>
		<li>Eine Anzeige hat zur Zeit eine Laufzeit von <b><?=($zeit/24/60/60)?> Tagen</b>. Nach Ablauf dieser Frist wird die Anzeige automatisch nicht mehr angezeigt.</li>
		<li>Sie k�nnen nur in Themen eine Anzeige erstellen, in denen Sie die n�tigen Rechte haben.</li>
		<li>Mit der Suche werden sowohl Titel, als auch Beschreibung aller Anzeigen durchsucht.</li>
		<li>Sie k�nnen Ihre eigenen Anzeigen jederzeit nachtr�glich <em>bearbeiten</em> oder <em>l�schen</em>. Die Buttons befinden sich unter dem Text.</li>
		<li>Bitte stellen Sie Ihre Anzeigen in die richtigen Kategorien ein. Damit das Schwarze Brett �bersichtlich bleibt, <em>l�schen</em> Sie bitte Ihre Anzeigen umgehend nach Abschluss/Verkauf.</li>
	</ul>
</div>
<br/>