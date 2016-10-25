<? if (empty($categories)): ?>
    <?= MessageBox::info(_('Sie haben sich momentan keine Anzeigen gemerkt.')) ?>
<? return; endif; ?>

<form action="<?= $controller->url_for('watchlist/bulk') ?>" method="post">

    <table class="default">
        <caption class="hide-in-dialog"><?= _('Gemerkte Anzeigen') ?></caption>
    <? foreach ($categories as $id => $category): ?>
        <tbody class="sb-articles" id="category-<?= $id ?>">
            <tr>
                <th colspan="6">
                    <a href="<?= $controller->url_for('category/' . $id) ?>">
                        <?= htmlReady($category['titel']) ?>
                    </a>
                </th>
            </tr>
        <? foreach ($category['articles'] as $article): ?>
            <?= $this->render_partial('article-tr', compact('article') + [
                    'return_to' => $controller->url_for('watchlist'),
                    'checkbox'  => true,
            ]) ?>
        <? endforeach; ?>
        </tbody>
    <? endforeach; ?>
        <tfoot>
            <tr>
                <td colspan="6">
                    <input type="checkbox" data-proxyfor="#category-<?= $id ?> td :checkbox">
                </td>
            </tr>
        </tfoot>
    </table>

</form>