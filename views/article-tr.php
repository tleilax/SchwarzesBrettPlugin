<tr class="<? if (!$article->visible) echo 'hidden'; ?> <? if ($article->new) echo 'new-article'; ?>" id="sb-article-<?= $article->id ?>">
    <td>
        <a href="<?= $controller->url_for('article/view/' . $article->id) ?>" data-dialog>
            <?= htmlReady($article->titel) ?>
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
            <?= Assets::img('icons/16/blue/chat.png', tooltip2(_('Antworten'))) ?>
        </a>
        <? if ($blame_enabled): ?>
            <a href="<?= $controller->url_for('article/blame/' . $article->id) ?>" data-dialog>
                <?= Assets::img('icons/16/blue/exclaim.png', tooltip2(_('Anzeige melden'))) ?>
            </a>
        <? endif; ?>
    <? endif; ?>
    <? if ($article->user_id === $GLOBALS['user']->id || $is_admin): ?>
        <a href="<?= $controller->url_for('article/edit/' . $article->id) ?>" data-dialog>
            <?= Assets::img('icons/16/blue/edit.png', tooltip2(_('Anzeige bearbeiten'))) ?>
        </a>
        <a href="<?= $controller->url_for('article/delete/' . $article->id, $return_to ? compact('return_to') : array()) ?>" data-confirm="<?= _('Wollen Sie diese Anzeige wirklich löschen?') ?>">
            <?= Assets::img('icons/16/blue/trash.png', tooltip2(_('Anzeige löschen'))) ?>
        </a>
    <? endif; ?>
    </td>
</tr>
