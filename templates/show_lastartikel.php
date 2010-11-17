<div id="headlinel_<?=$a->getArtikelId()?>" style="display:block; font-size:12px; position: relative;">
	<div style="position: absolute; right:0px; bottom: 1px; font-size:smaller;"><?=date("d.m.Y",$a->getMkdate())?> | <?=$anzahl?> |</div>
	<div style="padding-right: 85px;">
	<a href="javascript:showArtikel('<?= $a->getArtikelId()?>', 'l');">
	<img id="indikator_<?= $a->getArtikelId()?>" src="<?=$GLOBALS['ASSETS_URL']?>images/<?=$pfeil?>.gif" />
	<?= htmlReady($a->getTitel()) ?></a> (<?= htmlReady($a->getThemaTitel()) ?>)
	<? if ($a->getVisible() == 0): ?>
		<img src="<?=$pluginpfad?>/images/exclamation.png" alt="nicht sichtbar" title="Diese Anzeige ist nicht für andere sichtbar" class="middle" />
	<? endif; ?>
	</div>
</div>
<div id="contentl_<?= $a->getArtikelId() ?>" style="display:none; font-size:12px;">
</div>
