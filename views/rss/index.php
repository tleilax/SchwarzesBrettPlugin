<?= '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL ?>
<rss version="2.0">
    <channel>
        <title><?= htmlspecialchars(studip_utf8encode($title)) ?></title>
        <link><?= $link ?></link>
        <image>
            <url><?= Assets::image_path('logos/logoklein.png') ?></url>
            <title><?= htmlspecialchars(studip_utf8encode($title)) ?></title>
            <link><?= $link ?></link>
        </image>
        <description><![CDATA[<?= studip_utf8encode(formatReady($description, true, true)) ?>]]></description>
        <lastBuildDate><?= date('r') ?></lastBuildDate>
        <generator><?= htmlspecialchars(studip_utf8encode('Stud.IP - ' . $GLOBALS['SOFTWARE_VERSION'])) ?></generator>

<? foreach ($articles as $article): ?>
        <item>
            <title><?= htmlspecialchars(studip_utf8encode($article->titel)) ?></title>
            <link><?= $controller->absolute_url_for('article/view/' . $article->id) ?></link>
            <description><![CDATA[<?= studip_utf8encode(formatReady($article->beschreibung)) ?>]]></description>
            <pubDate><?= date('r', $article->mkdate) ?></pubDate>
            <guid isPermaLink="false"><?= $article->id ?></guid>
        </item>
<? endforeach; ?>
    </channel>
</rss>
