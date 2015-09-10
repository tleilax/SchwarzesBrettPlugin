<article class="sb-article">
    <header>
        <h2 class="hide-in-dialog"><?= htmlReady($article->titel) ?></h2>
        <address>
            <a href="<?= URLHelper::getLink('dispatch.php/profile?username=' . $article->user->username) ?>">
                <?= Avatar::getAvatar($article->user_id)->getImageTag(Avatar::SMALL) ?>
                <?= htmlReady($article->user->getFullname()) ?>
            </a>
        <? if ($more = count($article->user->articles) - 1): ?>
            <a href="<?= $controller->url_for('article/user?username=' . $article->user->username) ?>">
                (<?= sprintf(ngettext('%u weitere Anzeige', '%u weitere Anzeigen', $more), $more) ?>)
            </a>
        <? endif; ?>
        </address>
        <time title="<?= strftime('%x %X', $article->mkdate) ?>">
            <?= reltime($article->mkdate) ?>
        </time>
        <span>
            <?= sprintf(_('%u mal gesehen'), $article->views) ?>
        </span>
    </header>

    <section>
        <?= formatReady($article->beschreibung) ?>
    <? if (count(OpenGraphURL::$tempURLStorage)): ?>
        <div class="opengraph-area">
        <? foreach (OpenGraphURL::$tempURLStorage as $url):
            $og = new SBOpenGraphURL($url);
            if (!$og->isNew()) {
                echo $og->render();
            }
        endforeach; ?>
        </div>
    <? endif; ?>
    </section>

    <footer class="button-group" data-dialog-button>
    <? if ($article->user->id !== $GLOBALS['user']->id): ?>
        <?= Studip\LinkButton::create(_('Antworten'),
                                      URLHelper::getURL('dispatch.php/messages/write', array(
                                          'rec_uname'       => $article->user->username,
                                          'default_subject' => 'Re: ' . $article->titel,
                                          'default_body'    => '[quote]' . $article->beschreibung . '[/quote]',
                                      )),
                                      array('data-dialog' => '',
                                            'autofocus'   => '')) ?>
        <? if ($blame_enabled): ?>
            <?= Studip\LinkButton::create(_('Verstoß melden'),
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
</article>