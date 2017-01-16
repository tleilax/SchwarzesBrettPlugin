<tr class="<? if (!$article->visible) echo 'hidden'; ?>  <? if ($article->watched) echo 'watched'; ?>" id="sb-article-<?= $article->id ?>" data-article-id="<?= htmlReady($article->id) ?>">
<? if (!empty($checkbox)): ?>
    <td>
        <input type="checkbox" name="ids[]" value="<?= htmlReady($article->id) ?>">
    </td>
<? endif; ?>
    <td>
        <a href="<?= $controller->url_for('article/view/' . $article->id, compact('needle', 'return_to')) ?>"
            class="article <?= $article->new ? 'unseen' : 'seen' ?>" data-dialog>
            <?= SchwarzesBrett\Article::markup($needle, htmlReady($article->titel)) ?>
        </a>
    </td>
    <td>
        <?= strftime('%x', $article->mkdate) ?>
    </td>
    <td><?= $article->views ?></td>
    <td>
        <a href="<?= URLHelper::getLink('dispatch.php/profile?username=' . $article->user->username) ?>">
            <?= Avatar::getAvatar($article->user->id)->getImageTag(Avatar::SMALL) ?>
            <?= $article->user->getFullname() ?>
        </a>
    </td>
    <td class="actions">
    <? if ($article->user_id !== $GLOBALS['user']->id): ?>
        <a href="<?= URLHelper::getURL('dispatch.php/messages/write', array(
                              'rec_uname'       => $article->user->username,
                              'default_subject' => 'Re: ' . $article->titel,
                              'default_body'    => '[quote]' . $article->beschreibung . '[/quote]',
                          )) ?>" data-dialog>
            <?= Icon::create('chat', 'clickable', tooltip2(_('Antworten'))) ?>
        </a>
        <? if ($blame_enabled): ?>
            <a href="<?= $controller->url_for('article/blame/' . $article->id) ?>" data-dialog>
                <?= Icon::create('exclaim', 'clickable', tooltip2(_('Anzeige melden'))) ?>
            </a>
        <? endif; ?>
    <? endif; ?>
    <? if ($article->user_id === $GLOBALS['user']->id || $is_admin): ?>
        <a href="<?= $controller->url_for('article/edit/' . $article->id) ?>" data-dialog>
            <?= Icon::create('edit', 'clickable', tooltip2(_('Anzeige bearbeiten'))) ?>
        </a>
        <a href="<?= $controller->url_for('article/delete/' . $article->id, $return_to ? compact('return_to') : array()) ?>" data-confirm="<?= _('Wollen Sie diese Anzeige wirklich löschen?') ?>">
            <?= Icon::create('trash', 'clickable', tooltip2(_('Anzeige löschen'))) ?>
        </a>
    <? endif; ?>
    </td>
</tr>
