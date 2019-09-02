<? if (empty($categories)): ?>
    <?= MessageBox::info($_('Keine Anzeigen vorhanden.')) ?>
<? return; endif; ?>

<table class="default">
    <caption class="hide-in-dialog">
        <?= sprintf($_('Alle Anzeigen von %s'), $user->getFullname()) ?>
    </caption>
<? foreach ($categories as $id => $category): ?>
    <tbody class="sb-articles">
        <tr>
            <th colspan="5">
                <a href="<?= $controller->link_for("category/{$id}") ?>">
                    <?= htmlReady($category['titel']) ?>
                </a>
            </th>
        </tr>
    <? foreach ($category['articles'] as $article): ?>
        <?= $this->render_partial('article-tr', compact('article') + ['return_to' => $controller->url_for('article/own')]) ?>
    <? endforeach; ?>
    </tbody>
<? endforeach; ?>
</table>
