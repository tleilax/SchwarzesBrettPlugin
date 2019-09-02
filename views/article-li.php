<li data-article-id="<?= htmlReady($article->id) ?>" class="article <?= $article->new ? 'unseen' : 'seen' ?> <? if ($article->watched) echo 'watched'; ?>">
    <a href="<?= $controller->link_for("article/view", $article) ?>" data-dialog>
        <?= htmlReady($article->titel) ?>
    </a>
    <a href="<?= $controller->link_for("category/view", $article->category) ?>">
        <?= htmlReady($article->category->titel) ?>
    </a>
</li>
