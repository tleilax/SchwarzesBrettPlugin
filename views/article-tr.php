<tr class="<? if (!$article->visible) echo 'article-hidden'; ?>  <? if ($article->watched) echo 'watched'; ?>" id="sb-article-<?= $article->id ?>" data-article-id="<?= htmlReady($article->id) ?>">
<? if (!empty($checkbox)): ?>
    <td>
        <input type="checkbox" name="ids[]" value="<?= htmlReady($article->id) ?>">
    </td>
<? endif; ?>
    <td>
        <a href="<?= $controller->link_for("article/view", $article, compact('needle', 'return_to')) ?>"
            class="article <?= $article->new ? 'unseen' : 'seen' ?>" data-dialog>
            <?= SchwarzesBrett\Article::markup($needle, htmlReady($article->titel)) ?>
        </a>
    </td>
    <td>
        <?= strftime('%x', $article->mkdate) ?>
    </td>
    <td><?= $article->views ?></td>
    <td>
        <a href="<?= URLHelper::getLink('dispatch.php/profile', ['username' => $article->user->username]) ?>">
            <?= Avatar::getAvatar($article->user->id)->getImageTag(Avatar::SMALL) ?>
            <?= htmlReady($article->user->getFullname()) ?>
        </a>
    </td>
    <td class="actions">
    <? if ($article->user_id !== $GLOBALS['user']->id): ?>
        <a href="<?= URLHelper::getLink('dispatch.php/messages/write', [
            'rec_uname'       => $article->user->username,
            'default_subject' => "Re: {$article->titel}",
            'default_body'    => "[quote]{$article->beschreibung}[/quote]",
        ]) ?>" data-dialog>
            <?= Icon::create('chat')->asImg(tooltip2($_('Antworten'))) ?>
        </a>
        <? if ($blame_enabled): ?>
            <a href="<?= $controller->link_for("article/blame", $article) ?>" data-dialog>
                <?= Icon::create('exclaim')->asImg(tooltip2($_('Anzeige melden'))) ?>
            </a>
        <? endif; ?>
    <? endif; ?>
    <? if ($article->user_id === $GLOBALS['user']->id || $is_admin): ?>
        <a href="<?= $controller->link_for("article/edit", $article) ?>" data-dialog>
            <?= Icon::create('edit')->asImg(tooltip2($_('Anzeige bearbeiten'))) ?>
        </a>
        <a href="<?= $controller->link_for("article/delete", $article, $return_to ? compact('return_to') : []) ?>" data-confirm="<?= $_('Wollen Sie diese Anzeige wirklich löschen?') ?>">
            <?= Icon::create('trash')->asImg(tooltip2($_('Anzeige löschen'))) ?>
        </a>
    <? endif; ?>
    </td>
</tr>
