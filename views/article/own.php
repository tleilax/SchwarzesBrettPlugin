<h2 class="hide-in-dialog"><?= _('Meine Anzeigen') ?></h2>

<? if (empty($categories)): ?>
    <?= MessageBox::info(_('Sie haben momentan keine eigenen Anzeigen.')) ?>
<? return; endif; ?>

<table class="default">
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
