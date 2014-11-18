<h2 class="hide-in-dialog"><?= htmlReady($article->titel) ?></h2>
<div><?= formatReady($article->beschreibung) ?></div>

<div class="button-group" data-dialog-button>
<? if ($article->user->id !== $GLOBALS['user']->id): ?>
    <?= Studip\LinkButton::create(_('Antworten'),
                                  URLHelper::getURL('dispatch.php/messages/write', array(
                                      'rec_uname'       => $article->user->username,
                                      'default_subject' => 'Re: ' . $article->titel,
                                      'default_body'    => '[quote]' . $article->beschreibung . '[/quote]',
                                  )),
                                  array('data-dialog' => '')) ?>
    <? if ($blame_enabled): ?>
        <?= Studip\LinkButton::create(_('Melden'),
                                      $controller->url_for('article/blame/' . $article->id),
                                      array('data-dialog' => '')) ?>
    <? endif; ?>
<? endif; ?>
<? if ($article->user->id === $GLOBALS['user']->id || $is_admin): ?>
    <?= Studip\LinkButton::create(_('Bearbeiten'),
                                  $controller->url_for('article/edit/' . $article->id . '?return_to=' . Request::get('return_to')),
                                  array('data-dialog' => '')) ?>
    <?= Studip\LinkButton::create(_('Löschen'),
                                  $controller->url_for('article/delete/' . $article->id . '?return_to=' . Request::get('return_to')),
                                  array('data-confirm' => _('Wollen Sie diesen Artikel wirklich löschen?'))) ?>
<? endif; ?>
</div>