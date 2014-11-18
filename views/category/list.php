<h2><?= _('Themen�bersicht') ?></h2>
<ul class="sb-categories">
<? foreach ($categories as $category): ?>
    <?= $this->render_partial('category.php', compact('category')) ?>
<? endforeach; ?>
</ul>


<? if (!empty($newest)): ?>
<h2><?= sprintf(_('Die %u neuesten Anzeigen'), count($newest)) ?></h2>
<ul class="sb-articles">
<? foreach ($newest as $article): ?>
    <?= $this->render_partial('article-li.php', compact('article')) ?>
<? endforeach; ?>
</ul>
<? endif; ?>

<h3>Allgemeine Hinweise:</h3>
<ul>
    <li>
        Eine Anzeige hat zur Zeit eine Laufzeit von <b><?= $expire_days ?> Tagen</b>.
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
