
<!-- <p align="right">
    <a href="http://www.itdienste.uni-oldenburg.de/aoc" target="_blank"><img src="<?=$pluginpfad ?>/images/AppleOnCampus01.png" alt="Apple on Campus" title="Apple for education" /></a>
</p>
-->

<?= $question ?>

<form name="search_form" method="post" action="<?=$link_search?>">
    <table class="default">
        <thead>
            <tr>
                <th class="table_header_bold">
                    <?= _('Allgemeine Suche nach Anzeigen:') ?>
                </th>
            </tr>
        </thead>
        <tbody>        
            <tr>
                <td style="padding:5px;">
                    Nach Anzeigen suchen:
                    <input type="text" style="width:300px;" name="search_text" value="<?=htmlready(Request::get('search_text'))?>">
                    <?= Studip\Button::create(_('Nach Anzeigen suchen'), 'submit') ?>
                    <?= Studip\LinkButton::create(_('Zurücksetzen'), $link_back) ?>
                </td>
            </tr>
        </tbody>
    </table>
</form>
<br/>

<? if(count($lastArtikel) > 0): $last=count($lastArtikel); ?>
<table class="default">
    <colgroup>
        <col width="50%">
        <col width="50%">
    </colgroup>
    <thead>
        <tr>
            <th class="table_header_bold" colspan="2">
                <?= sprintf(_('Die %u neusten Anzeigen'), $last) ?>
            </th>
        </tr>
    </thead>
    <tbody style="vertical-align: top;">
        <tr>
            <td>
                <table class="default zebra-hover">
                <? foreach (array_slice($lastArtikel, 0, ceil($last / 2)) as $article): ?>
                    <tr>
                        <td><?= $article ?></td>
                    </tr>
                <? endforeach; ?>
                </table>
            </td>
            <td>
                <table class="default zebra-hover">
                    <? foreach (array_slice($lastArtikel, ceil($last / 2)) as $article): ?>
                        <tr>
                            <td><?= $article ?></td>
                        </tr>
                    <? endforeach; ?>
                </table>
            </td>
        </tr>
    </tbody>
</table>
<br/>
<? endif ?>

<? if($keinethemen): ?>
<?= MessageBox::info(_('Zur Zeit sind keine Themen vorhanden!')) ?>
<? else: ?>
<table class="default">
    <colgroup>
    <? for ($i = 0; $i < $themen_rows; $i++): ?>
        <col width="<?= round(100 / $themen_rows, 2) ?>%">
    <? endfor; ?>
    </colgroup>
    <thead>
        <tr>
            <th class="table_header_bold" colspan="<?= $themen_rows ?>">
                <?= _('Themenübersicht') ?>
            </th>
        </tr>
    </thead>
    <tbody style="vertical-align: top;">
        <tr>
        <? foreach (array_chunk($results, ceil(count($results) / $themen_rows)) as $items): ?>
            <td>
            <? foreach ($items as $result): ?>
                <div style="float:right">
                    <a href="javascript: toggleThema('<?=$result['thema']->getThemaId() ?>');">
                        <?= Assets::img('icons/16/blue/arr_eol-down.png', array('id' => 'show_'.$result['thema']->getThemaId(), 'title' => _('Alle Artikel anzeigen'))) ?>
                        <?= Assets::img('icons/16/blue/arr_eol-up.png', array('id' => 'hide_'.$result['thema']->getThemaId(), 'title' => _('Alle Artikel verstecken'), 'style' => 'display:none;')) ?>
                    </a>
            <? if ($GLOBALS['perm']->have_perm('root')): ?>
                <? if($result['thema']->getVisible() == 0): ?>
                    <?= Assets::img('icons/16/red/exclaim-circle.png', array('class' => 'text-top', 'title' => _('Dieses Thema ist für Benutzer nicht sichtbar'))) ?>
                <? endif ?>
                    <a href="<?= URLHelper::getLink($link_edit, array('thema_id' => $result['thema']->getThemaId())) ?>">
                        <?= Assets::img('icons/16/blue/edit.png', array('title' => _('Thema bearbeiten'))) ?>
                    </a>
                    <a href="<?= URLHelper::getLink($link_delete, array('thema_id' => $result['thema']->getThemaId())) ?>">
                        <?= Assets::img('icons/16/blue/trash.png', array('title' => _('Thema inkl. aller Anzeigen    löschen'))) ?>
                    </a>
            <? endif ?>
                </div>
                <a title="Klicken, um die Kategorie aufzuklappen" href="javascript: toggleThema('<?=$result['thema']->getThemaId() ?>');" <? if($result['thema']->getLastArtikelDate() > $result['last_thema_user_date']): ?> style="color: red !important;"<? endif ?>><b<? if($result['thema']->getLastArtikelDate() > $result['last_thema_user_date']): ?> style="color: red !important;"<? endif ?>><?=htmlReady($result['thema']->getTitel()) ?> <?=($result['countArtikel'] != 0)? '('.$result['countArtikel'].')':''?></b></a><br/>
                <span style="font-size: smaller"><?=htmlReady($result['thema']->getBeschreibung()) ?></span>
                <div style="clear:both; border-bottom: 1px solid #8e8e8e; margin-bottom: 3px;"></div>
                <div id="list_<?=$result['thema']->getThemaId() ?>" style="display: none;"></div>
            <? endforeach; ?>
            </td>
        <? endforeach; ?>
        </tr>
    </tbody>
</table>
<? endif ?>

<br>

<h3>Allgemeine Hinweise:</h3>
<ul>
    <li>
        Eine Anzeige hat zur Zeit eine Laufzeit von <b><?= floor($zeit / (24 * 60 * 60)) ?> Tagen</b>.
        Nach Ablauf dieser Frist wird die Anzeige automatisch nicht mehr angezeigt.
    </li>
    <li>Sie können nur in Themen eine Anzeige erstellen, in denen Sie die nötigen Rechte haben.</li>
    <li>Mit der Suche werden sowohl Titel als auch Beschreibung aller Anzeigen durchsucht.</li>
    <li>
        Sie können Ihre eigenen Anzeigen jederzeit nachträglich <em>bearbeiten</em>
        oder <em>löschen</em>. Die Buttons befinden sich unter dem Text.
    </li>
    <li>
        Bitte stellen Sie Ihre Anzeigen in die richtigen Kategorien ein.
        Damit das Schwarze Brett übersichtlich bleibt, <em>löschen</em> Sie
        bitte Ihre Anzeigen umgehend nach Abschluss/Verkauf.
    </li>
    <li><strong>Bitte Artikel nur in <em>eine</em> Kategorie einstellen!</strong></li>
    <li><strong>Bitte keine kommerziellen Angebote einstellen. Sie werden gelöscht!</strong></li>
</ul>
<br/>
