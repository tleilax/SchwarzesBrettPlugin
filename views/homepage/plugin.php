<? if (empty($categories)): ?>
    <?= MessageBox::info($_('Keine Anzeigen vorhanden.')) ?>
<? return; endif; ?>

<table class="default sb-homepage-plugin">
<? foreach ($categories as $id => $category): ?>
    <tbody class="sb-articles">
        <tr>
            <th colspan="5">
                <a href="<?= $controller->url_for("category/{$id}") ?>">
                    <?= htmlReady($category['titel']) ?>
                </a>
            </th>
        </tr>
    <? foreach ($category['articles'] as $article): ?>
        <? if ($article->category->isVisible()) : ?>
            <?= $this->render_partial('article-tr', compact('article') + ['return_to' => $controller->url_for('article/own')]) ?>
        <? endif ?>
    <? endforeach; ?>
    </tbody>
<? endforeach; ?>
</table>
