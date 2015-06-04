<? if (empty($categories)): ?>
    <?= MessageBox::info(_('Sie haben momentan keine eigenen Anzeigen.')) ?>
<? return; endif; ?>

<table class="default">
    <caption class="hide-in-dialog"><?= _('Meine Anzeigen') ?></caption>
<? foreach ($categories as $id => $category): ?>
    <tbody class="sb-articles">
        <tr>
            <th colspan="5">
                <a href="<?= $controller->url_for('category/' . $id) ?>">
                    <?= htmlReady($category['titel']) ?>
                </a>
            </th>
        </tr>
    <? foreach ($category['articles'] as $article): ?>
        <?= $this->render_partial('article-tr', compact('article') + array('return_to' => $controller->url_for('article/own'))) ?>
    <? endforeach; ?>
    </tbody>
<? endforeach; ?>
</table>
