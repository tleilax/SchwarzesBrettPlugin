<div id="headlinel_<?=$a->getArtikelId()?>" style="display:block; font-size:12px; position: relative;">
	<div style="position: absolute; right:0px; bottom: 1px; font-size:smaller;"><?=date("d.m.Y",$a->getMkdate())?> | <?=$anzahl?> |</div>
	<div style="padding-right: 85px;">
	<a href="javascript:showArtikel('<?=$a->getArtikelId()?>', '<?=$link_ajax?>', 'l');">
	<img id="indikator_<?=$a->getArtikelId()?>" src="<?=$GLOBALS['ASSETS_URL']?>images/<?=$pfeil?>.gif" />
	<?=htmlReady($a->getTitel())?></a> (<?=$a->getThemaTitel()?>)
	<? if ($a->getVisible() == 0): ?>
		<img src="<?=$pluginpfad?>/images/exclamation.png" alt="nicht sichtbar" title="Diese Anzeige ist nicht f�r andere sichtbar" class="middle" />
	<? endif; ?>
	</div>
</div>
<div id="contentl_<?=$a->getArtikelId()?>" style="display:none; font-size:12px;">
	<a href="javascript:closeArtikel('<?=$a->getArtikelId()?>', 'l');">
	<img id="indikator_offen_<?=$a->getArtikelId()?>" src="<?=$GLOBALS['ASSETS_URL']?>images/<?=$pfeil_runter?>.gif" />
	<?=htmlReady($a->getTitel())?></a>
	<? if ($a->getVisible() == 0): ?>
		<img src="<?=$pluginpfad?>/images/exclamation.png" alt="nicht sichtbar" title="Diese Anzeige ist nicht f�r andere sichtbar"  class="middle" />
	<? endif; ?>
	<div style="border-bottom: 1px solid #8e8e8e; padding-bottom: 3px;"><?=formatReady($a->getBeschreibung())?></div>
	<div align="right" style="font-size:smaller; padding:1px 0px 5px 0px;">
		erstellt von <a href="about.php?username=<?=get_username($a->getUserId())?>"><?=get_fullname($a->getUserId())?></a> | g�ltig bis bis <?=date("d.m.Y",$a->getMkdate()+$zeit)?>
	</div>
	<div align="center" style="padding-bottom: 5px;">
	<? if($antwort === true): ?>
		<a href="sms_send.php?rec_uname=<?=get_username($a->getUserId())?>&messagesubject=<?=rawurlencode($a->getTitel())?>&message=<?=rawurlencode('[quote] '.$a->getBeschreibung().' [/quote]')?>"><?=makeButton("antworten","img", "Dem Benutzer eine Email schreiben")?></a>
	<? endif; if($access === true): ?>
		<a href="<?=$link_edit ?>"><?=makeButton("bearbeiten","img", "Diese Anzeige bearbeiten")?></a>
		<a href="<?=$link_delete ?>"><?=makeButton("loeschen","img", "Diese Anzeige l�schen")?></a>
	<? endif; ?>
	</div>
</div>