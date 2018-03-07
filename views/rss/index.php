<?= '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL ?>
<rss version="2.0">
    <channel>
        <title><?= htmlspecialchars($title) ?></title>
        <link><?= $link ?></link>
        <image>
            <url><?= Assets::image_path('logos/logoklein.png') ?></url>
            <title><?= htmlspecialchars($title) ?></title>
            <link><?= $link ?></link>
        </image>
        <description><![CDATA[<?= formatReady($description, true, true) ?>]]></description>
        <lastBuildDate><?= date('r') ?></lastBuildDate>
        <generator><?= htmlspecialchars("Stud.IP - {$GLOBALS['SOFTWARE_VERSION']}") ?></generator>

<? foreach ($articles as $article): ?>
        <item>
            <title><?= htmlspecialchars($article->titel) ?></title>
            <link><?= $controller->absolute_url_for("article/view/{$article->id}") ?></link>
            <description><![CDATA[<?= formatReady($article->beschreibung) ?>]]></description>
            <pubDate><?= date('r', $article->mkdate) ?></pubDate>
            <guid isPermaLink="false"><?= $article->id ?></guid>
        </item>
<? endforeach; ?>
    </channel>
</rss>
