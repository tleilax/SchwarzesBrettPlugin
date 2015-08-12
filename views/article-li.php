<li>
    <a href="<?= $controller->url_for('article/view/' . $article->id) ?>"
         class="article <?= $article->new ? 'unseen' : 'seen' ?>" data-dialog>
        <?= htmlReady($article->titel) ?>
    </a>
    <a href="<?= $controller->url_for('category/view/' . $article->category->id) ?>">
        <?= htmlReady($article->category->titel) ?>
    </a>
</li>
