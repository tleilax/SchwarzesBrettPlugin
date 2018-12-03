<li data-article-id="<?= htmlReady($article->id) ?>" class="article <?= $article->new ? 'unseen' : 'seen' ?> <? if ($article->watched) echo 'watched'; ?>">
    <a href="<?= $controller->url_for("article/view/{$article->id}") ?>" data-dialog>
        <?= htmlReady($article->titel) ?>
    </a>
    <a href="<?= $controller->url_for("category/view/{$article->category->id}") ?>">
        <?= htmlReady($article->category->titel) ?>
    </a>
</li>
