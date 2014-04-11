
<!-- <p align="right">
    <a href="http://www.itdienste.uni-oldenburg.de/aoc" target="_blank"><img src="<?=$pluginpfad ?>/images/AppleOnCampus01.png" alt="Apple on Campus" title="Apple for education" /></a>
</p>
-->
<?= $message ?>
<form name="search_form" method="post" action="<?=$link_search?>">
<h2 class="caption"><?= _('Allgemeine Suche nach Anzeigen') ?></h2>
<table class="default">
    <tr>
        <td class="steel1" style="padding:5px;">
            Nach Anzeigen suchen:
            <input type="text" style="width:300px;" name="search_text" value="<?=htmlready(Request::get('search_text'))?>" />
            <?= Studip\Button::create(_('Suchen'), 'suchen') ?>
            <?= Studip\LinkButton::create(_('Zur�cksetzen'), $link_back) ?>
        </td>
    </tr>
</table>
</form>
<br/>

<? if(count($lastArtikel) > 0): $last=count($lastArtikel); ?>
<h2 class="caption"><?= sprintf(_('Die %u neusten Anzeigen'), $last) ?></h2>
<table class="default">
    <tr class="steel1">
        <td valign="top" width="50%">
            <table class="default zebra-hover">
            <? for ($i=0; $i<ceil($last/2); $i++):
                $a = $lastArtikel[$i]; ?>
                <tr>
                    <td>
                        <?= $a ?>
                    </td>
                </tr>
            <? endfor; ?>
            </table>
        </td>
        <td valign="top" width="50%">
            <table class="default zebra-hover">
            <? for ($i=ceil($last/2); $i<($last); $i++):
                $a = $lastArtikel[$i]; ?>
                <tr>
                    <td>
                        <?= $a ?>
                    </td>
                </tr>
            <? endfor; ?>
            </table>
        </td>
    </tr>
</table>
<br/>
<? endif ?>

<? if($keinethemen): ?>
<?= MessageBox::info(_('Zur Zeit sind keine Themen vorhanden!')) ?>
<? else: ?>
<h2 class="caption">
    <?= _('Themen�bersicht') ?>
<? if ($enableRss): ?>
    <div style="float: right;">
    <? if ($newArticles): ?>
        <a href="<?= URLHelper::getLink($link_visit) ?>">
            <?= Assets::img('icons/16/white/refresh.png') ?>
            <?= _('Alle Themen als besucht markieren') ?>
        </a>
	|
    <? endif; ?>
        <a href="<?= URLHelper::getLink($link_rss, array('thema_id' => 'all')) ?>">
            <?= Assets::img('icons/16/white/rss.png', array('class' => 'text-top', 'title' => _('RSS Feed'))) ?></a>
    </div>
<? endif ?>
</h2>
<table class="blank" border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr>
<?  $tindex = 0; foreach ($results as $result): ?>
    <? if($tindex%$themen_rows == 0): ?>
    <td width="33%" valign="top">
    <? endif; $tindex++; ?>
    <div class="steel1" style="padding:2px; margin:3px">
        <div style="float:left">
            <a title="Klicken, um die Kategorie aufzuklappen" href="javascript: toggleThema('<?=$result['thema']->getThemaId() ?>');" <? if($result['newArticles']): ?> style="color: red !important;"<? endif ?>><b<? if($result['newArticles']): ?> style="color: red !important;"<? endif ?>><?=htmlReady($result['thema']->getTitel()) ?> <?=($result['countArtikel'] != 0)? '('.$result['countArtikel'].')':''?></b></a><br/>
            <span style="font-size: smaller"><?=htmlReady($result['thema']->getBeschreibung()) ?></span>
        </div>
        <div style="float:right">
                <a href="javascript: toggleThema('<?=$result['thema']->getThemaId() ?>');">
                    <?= Assets::img('icons/16/blue/arr_eol-down.png', array('id' => 'show_'.$result['thema']->getThemaId(), 'class' => 'text-top', 'title' => _('Alle Artikel anzeigen'))) ?>
                    <?= Assets::img('icons/16/blue/arr_eol-up.png', array('id' => 'hide_'.$result['thema']->getThemaId(), 'class' => 'text-top', 'title' => _('Alle Artikel verstecken'), 'style' => 'display:none;')) ?>
                </a>
        <? if($root): ?>
            <? if($result['thema']->getVisible() == 0): ?>
                 <?= Assets::img('icons/16/red/exclaim-circle.png', array('class' => 'text-top', 'title' => _('Dieses Thema ist f�r Benutzer nicht sichtbar'))) ?>
            <? endif ?>
                <a href="<?= URLHelper::getLink($link_edit, array('thema_id' => $result['thema']->getThemaId())) ?>">
                    <?= Assets::img('icons/16/blue/edit.png', array('class' => 'text-top', 'title' => _('Thema bearbeiten'))) ?>
                </a>
                <a href="<?= URLHelper::getLink($link_delete, array('thema_id' => $result['thema']->getThemaId())) ?>">
                    <?= Assets::img('icons/16/blue/trash.png', array('class' => 'text-top', 'title' => _('Thema inkl. aller Anzeigen l�schen'))) ?>
                </a>
        <? endif ?>
        <? if($enableRss): ?>
                <a href="<?= URLHelper::getLink($link_rss, array('thema_id' => $result['thema']->getThemaId())) ?>">
                    <?= Assets::img('icons/16/blue/rss.png', array('class' => 'text-top', 'title' => _('RSS Feed'))) ?>
                </a>
        <? endif ?>
        </div>
        <div style="clear:both; border-bottom: 1px solid #8e8e8e;"></div>
        <div id="list_<?=$result['thema']->getThemaId() ?>" style="display: none;">
        </div>
    </div>
    <? if($tindex%$themen_rows == 0): ?>
    </td>
    <? endif ?>
<? endforeach; ?>
    </tr>
</table>
<? endif ?>

<br>

<h3>Allgemeine Hinweise:</h3>
<ul>
    <li>
        Eine Anzeige hat zur Zeit eine Laufzeit von <b><?= floor($zeit / (24 * 60 * 60)) ?> Tagen</b>.
        Nach Ablauf dieser Frist wird die Anzeige automatisch nicht mehr angezeigt.
    </li>
    <li>Sie k�nnen nur in Themen eine Anzeige erstellen, in denen Sie die n�tigen Rechte haben.</li>
    <li>Mit der Suche werden sowohl Titel als auch Beschreibung aller Anzeigen durchsucht.</li>
    <li>
        Sie k�nnen Ihre eigenen Anzeigen jederzeit nachtr�glich <em>bearbeiten</em>
        oder <em>l�schen</em>. Die Buttons befinden sich unter dem Text.
    </li>
    <li>
        Bitte stellen Sie Ihre Anzeigen in die richtigen Kategorien ein.
        Damit das Schwarze Brett �bersichtlich bleibt, <em>l�schen</em> Sie
        bitte Ihre Anzeigen umgehend nach Abschluss/Verkauf.
    </li>
    <li><b>Bitte Artikel nur in <em>eine</em> Kategorie einstellen!</b></li>
    <li><b>Bitte keine kommerziellen Angebote einstellen. Sie werden gel�scht!</b></li>
</ul>
<br/>
