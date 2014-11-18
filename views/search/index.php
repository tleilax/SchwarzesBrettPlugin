<table class="default">
    <caption><?= sprintf(_('Suchergebnisse für "%s"'), $needle) ?></caption>
<? foreach ($categories as $id => $category): ?>
    <tbody>
        <tr>
            <th colspan="5">
                <a href="<?= $controller->url_for('category/' . $id) ?>">
                    <?= htmlReady($category['titel']) ?>
                </a>
            </th>
        </tr>
    <? foreach ($category['articles'] as $article): ?>
        <?= $this->render_partial('article-tr', compact('article')) ?>
    <? endforeach; ?>
    </tbody>
<? endforeach; ?>    
</table>