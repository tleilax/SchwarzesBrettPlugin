<div id="headline_<?=$a->getArtikelId()?>" style="display:block;">
	<div style="float:left;">
		<a href="javascript:showArtikel('<?=$a->getArtikelId()?>');">
		<img id="indikator_<?=$a->getArtikelId()?>" src="<?=Assets::url()?>images/<?=$pfeil?>.gif" />
		<?=htmlReady($a->getTitel())?></a>
		<? if ($a->getVisible() == 0): ?>
			<img src="<?=$pluginpfad?>/images/exclamation.png" alt="nicht sichtbar" title="Diese Anzeige ist nicht für andere sichtbar" align="absmiddle" />
		<? endif; ?>
	</div>
	<div style="float:right; font-size:smaller;"><?=date("d.m.Y",$a->getMkdate())?> | <?=$anzahl?> |</div>
	<div style="clear:both;"></div>
</div>
<div id="content_<?=$a->getArtikelId()?>" style="display:none;">
	<div style="float:left;">
		<a href="javascript:closeArtikel('<?=$a->getArtikelId()?>');">
		<img id="indikator_<?=$a->getArtikelId()?>" src="<?=Assets::url()?>images/<?=$pfeil_runter?>.gif" />
		<?=htmlReady($a->getTitel())?></a>
		<? if ($a->getVisible() == 0): ?>
			<img src="<?=$pluginpfad?>/images/exclamation.png" alt="nicht sichtbar" title="Diese Anzeige ist nicht für andere sichtbar" align="absmiddle" />
		<? endif; ?>
	</div>
	<div style="float:right; font-size:smaller;"><?=date("d.m.Y",$a->getMkdate())?> | <?=$anzahl?> |</div>
	<div style="clear:both;"></div>
	<div style="border-bottom: 1px solid #8e8e8e;"><?=formatReady($a->getBeschreibung())?></div>
	<div align="right" style="font-size:smaller;">
		erstellt von <a href="about.php?username=<?=get_username($a->getUserId())?>"><?=get_fullname($a->getUserId())?></a> | gültig bis bis <?=date("d.m.Y",$a->getMkdate()+$zeit)?>
	</div>
	<div align="center" style="padding-bottom: 10px;">
	<? if($antwort === true): ?>
		<a href="sms_send.php?rec_uname=<?=get_username($a->getUserId())?>&messagesubject=<?=rawurlencode($a->getTitel())?>&message=<?=rawurlencode('[quote] '.$a->getBeschreibung().' [/quote]')?>"><?=makeButton("antworten","img", "Dem Benutzer eine Email schreiben")?></a>
	<? endif; if($access === true): ?>
		<a href="<?=$link_edit ?>"><?=makeButton("bearbeiten","img", "Diese Anzeige bearbeiten")?></a>
		<a href="<?=$link_delete ?>"><?=makeButton("loeschen","img", "Diese Anzeige löschen")?></a>
	<? endif; ?>
	</div>
</div>