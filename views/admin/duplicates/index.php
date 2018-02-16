<? if (empty($duplicates)): ?>
    <?= MessageBox::info($_('Es scheint keine doppelten Einträge zu geben.')) ?>
<? return; endif; ?>

<table class="default" id="duplicates">
<? foreach ($duplicates as $user_id => $articles): ?>
    <tbody>
        <tr>
            <th colspan="3">
                <a href="<?= URLHelper::getLink('dispatch.php/profile', ['username' => User::find($user_id)->username]) ?>">
                    <?= Avatar::getAvatar($user_id)->getImageTag(Avatar::SMALL) ?>
                    <?= User::find($user_id)->getFullname() ?>
                </a>
            </th>
        </tr>
    <? $last = ['desc' => $articles[0]->beschreibung, 'title' => $articles[0]->titel]; ?>
    <? foreach ($articles as $article): ?>
        <tr <? if ($last['desc'] !== $article->beschreibung && $last['title'] !== $article->titel) echo 'class="divider"'; ?>>
            <td>
                <a href="<?= $controller->url_for("article/view/{$article->id}", ['return_to' => $controller->url_for('admin/duplicates')]) ?>" data-dialog>
                    <?= htmlReady($article->titel) ?>
                </a>
            </td>
            <td>
                <a href="<?= $controller->url_for("article/view/{$article->id}", ['return_to' => $controller->url_for('admin/duplicates')]) ?>" data-dialog>
                    <?= htmlReady($article->category->titel) ?>
                </a>
            </td>
            <td><?= strftime('%x %X', $article->mkdate) ?></td>
        </tr>
        <? $last = ['desc' => $article->beschreibung, 'title' => $article->titel]; ?>
    <? endforeach; ?>
    </tbody>
<? endforeach; ?>
</table>
