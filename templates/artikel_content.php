<a href="#" onClick="closeArtikel(this);return false;">
	<img id="indikator_offen_<?=$a->getArtikelId()?>" src="<?=$GLOBALS['ASSETS_URL']?>images/<?=$pfeil_runter?>.gif" />
	<?=htmlReady($a->getTitel())?></a>
	<? if ($a->getVisible() == 0): ?>
		<img src="<?=$pluginpfad?>/images/exclamation.png" alt="nicht sichtbar" title="Diese Anzeige ist nicht f�r andere sichtbar"  class="middle" />
	<? endif; ?>
	<div style="border-bottom: 1px solid #8e8e8e; padding-bottom: 3px;"><?=formatReady($a->getBeschreibung())?></div>
	<div align="right" style="font-size:smaller; padding:1px 0px 5px 0px;">
		erstellt von <a href="<?=URLHelper::getLink('about.php')?>&username=<?=get_username($a->getUserId())?>"><?=get_fullname($a->getUserId())?></a> | g�ltig bis bis <?=date("d.m.Y",$a->getMkdate()+$zeit)?>
	</div>
	<div align="center" style="padding-bottom: 5px;">
	<? if($antwort === true): ?>
		<a href="<?=URLHelper::getLink('sms_send.php')?>&rec_uname=<?=get_username($a->getUserId())?>&messagesubject=<?=rawurlencode($a->getTitel())?>&message=<?=rawurlencode('[quote] '.$a->getBeschreibung().' [/quote]')?>"><?=makeButton("antworten","img", "Dem Benutzer eine Email schreiben")?></a>
	<? endif; if($access === true): ?>
		<a href="<?=$link_edit ?>"><?=makeButton("bearbeiten","img", "Diese Anzeige bearbeiten")?></a>
		<a href="<?=$link_delete ?>"><?=makeButton("loeschen","img", "Diese Anzeige l�schen")?></a>
	<? endif; ?>
	</div>