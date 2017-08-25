<? if (empty($categories)): ?>
    <?= MessageBox::info($_('Sie haben sich momentan keine Anzeigen gemerkt.')) ?>
<? return; endif; ?>

<form action="<?= $controller->url_for('watchlist/remove/bulk') ?>" method="post">

    <table class="default" id="watchlist">
        <caption class="hide-in-dialog"><?= $_('Gemerkte Anzeigen') ?></caption>
    <? foreach ($categories as $id => $category): ?>
        <tbody class="sb-articles">
            <tr>
                <th colspan="6">
                    <a href="<?= $controller->url_for('category/' . $id) ?>">
                        <?= htmlReady($category['titel']) ?>
                    </a>
                </th>
            </tr>
        <? foreach ($category['articles'] as $article): ?>
            <?= $this->render_partial('article-tr', compact('article') + ['checkbox'  => true]) ?>
        <? endforeach; ?>
        </tbody>
    <? endforeach; ?>
        <tfoot>
            <tr>
                <td colspan="6">
                    <input type="checkbox"
                           data-proxyfor="#watchlist tbody :checkbox"
                           data-activates="#watchlist tfoot button">
                    <?= Studip\Button::create($_('Markierte Einträge entfernen'), 'delete', [
                        'data-confirm' => $_('Möchten Sie die markierten Einträge wirklich von der Merkliste löschen?'),
                    ]) ?>
                </td>
            </tr>
        </tfoot>
    </table>

</form>
