<section class="contentbox schwarzesbrett-widget">
    <section>
        <ul class="sb-articles">
        <? foreach ($articles as $article): ?>
            <?= $this->render_partial('article-li.php', compact('article')) ?>
        <? endforeach; ?>
        </ul>
    </section>
</section>

