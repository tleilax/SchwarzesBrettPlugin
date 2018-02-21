<section class="contentbox schwarzesbrett-widget">
    <section>
    <? if (empty($articles)): ?>
        <p style="text-align: center;"><?= $_('Momentan liegen keine Anzeigen vor.') ?></p>
    <? else: ?>
        <ul class="sb-articles">
        <? foreach ($articles as $article): ?>
            <? if ($article->category->isVisible()): ?>
                <?= $this->render_partial('article-li.php', compact('article')) ?>
            <? endif ?>
        <? endforeach; ?>
        </ul>
    <? endif; ?>
    </section>
</section>
