<li <? if ($article->new) echo 'class="new-article"'; ?>>
    <span class="link">
        <a href="<?= $controller->url_for('article/view/' . $article->id) ?>" data-dialog>
            <?= htmlReady($article->titel) ?>
        </a>
        (<?= htmlReady($article->category->titel) ?>)
    </span>
</li>
