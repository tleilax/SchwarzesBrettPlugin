<table class="default">
    <caption><?= sprintf(_('Suchergebnisse für "%s"'), $needle) ?></caption>
<? if (!$categories): ?>
    <tbody>
        <tr>
            <td>
            <? if (strlen($needle) >= 3): ?>
                <?= MessageBox::info(_('Es wurden keine passenden Anzeigen gefunden.')) ?>
            <? else: ?>
                <?= MessageBox::info(_('Der eingegebene Suchbegriff ist zu kurz. Geben Sie bitte mindestens drei Zeichen ein.')) ?>
            <? endif; ?>
            </td>
        </tr>
    </tbody>
<? endif; ?>
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
        <?= $this->render_partial('article-tr', compact('article', 'needle')) ?>
    <? endforeach; ?>
    </tbody>
<? endforeach; ?>    
</table>