<div id="headline_<?=$a->getArtikelId()?>" style="display:block; font-size:12px; position: relative;">
    <div style="position: absolute; right:0px; bottom: 1px; font-size:smaller;">
        <?= date("d.m.Y",$a->getMkdate())?> | <?=$anzahl?> |
    </div>
    <div style="padding-right: 85px;">
        <a href="javascript: showArtikel('<?=$a->getArtikelId()?>');">
            <?= Assets::img('icons/16/' . $pfeil . '/arr_1right.png', array('id' => 'indikator_' . $a->getArtikelId(), 'class' => 'text-top')) ?>
            <?= htmlReady($a->getTitel())?>
        </a>
        <? if ($a->getVisible() == 0): ?>
            <?= Assets::img('icons/16/red/exclaim-circle.png', array('class' => 'text-top', 'title' => _('Diese Anzeige ist nicht für andere sichtbar'))) ?>
        <? endif; ?>
    </div>
</div>
<div id="content_<?=$a->getArtikelId()?>" style="display:none; font-size:12px;"></div>
