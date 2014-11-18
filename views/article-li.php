<li <? if ($article->new) echo 'class="new-article"'; ?>>
    <a href="<?= $controller->url_for('article/view/' . $article->id) ?>" data-dialog>
        <?= htmlReady($article->titel) ?>
    </a>
    <a href="<?= $controller->url_for('category/view/' . $article->category->id) ?>">
        <?= htmlReady($article->category->titel) ?>
    </a>
</li>
