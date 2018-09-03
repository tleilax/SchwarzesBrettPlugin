<article class="sb-article">
    <section>
        <div class="sb-content">
        <? if ($article->category->terms && $article->category->display_terms_in_article): ?>
            <div class="category-disclaimer">
                <?= formatReady($article->category->terms) ?>
            </div>
        <? endif; ?>

            <?= formatReady($article->beschreibung) ?>

            <?= OpenGraph::extract($article->beschreibung)->render(true) ?>
        </div>

    <? if (count($article->images) > 0): ?>
        <div class="sb-article-images">
            <strong><?= sprintf(ngettext(
                '%u Bild',
                '%u Bilder',
                count($article->images)
            ), count($article->images)) ?></strong>
            <ul>
            <? foreach ($article->images as $image): ?>
                <li>
                    <?= $image->thumbnail->getImageTag(true) ?>
                </li>
            <? endforeach; ?>
            </ul>
        </div>
    <? endif; ?>
    </section>

    <footer class="meta">
        <address>
            <a href="<?= URLHelper::getLink('dispatch.php/profile', ['username' => $article->user->username]) ?>">
                <?= Avatar::getAvatar($article->user_id)->getImageTag(Avatar::SMALL) ?>
                <?= htmlReady($article->user->getFullname()) ?>
            </a>
        <? if ($more = count($article->user->articles) - 1): ?>
            <a href="<?= $controller->url_for('article/user', ['username' => $article->user->username]) ?>">
                (<?= sprintf(ngettext('%u weitere Anzeige', '%u weitere Anzeigen', $more), $more) ?>)
            </a>
        <? endif; ?>
        </address>
        <time title="<?= strftime('%x %X', $article->mkdate) ?>">
            <?= reltime($article->mkdate) ?>
        </time>
        <span>
            <?= sprintf($_('%u mal gesehen'), $article->views) ?>
        </span>
    </footer>

    <footer class="button-group" data-dialog-button>
    <? if ($article->user->id !== $GLOBALS['user']->id): ?>
        <?= Studip\LinkButton::create(
            $_('Antworten'),
            URLHelper::getURL('dispatch.php/messages/write', [
                'rec_uname'       => $article->user->username,
                'default_subject' => "Re: {$article->titel}",
                'default_body'    => "[quote]{$article->beschreibung}[/quote]",
            ]),
            ['data-dialog' => '']
        ) ?>
        <? if ($article->watched): ?>
            <?= Studip\LinkButton::create(
                $_('Nicht merken'),
                $controller->url_for("watchlist/remove/{$article->id}", ['return_to' => Request::get('return_to')]),
                ['data-dialog' => '']
            ) ?>
        <? else: ?>
            <?= Studip\LinkButton::create(
                $_('Merken'),
                $controller->url_for("watchlist/add/{$article->id}", ['return_to' => Request::get('return_to')]),
                ['data-dialog' => '']
            ) ?>
        <? endif; ?>

        <? if ($blame_enabled): ?>
            <?= Studip\LinkButton::create(
                $_('Verstoß melden'),
                $controller->url_for("article/blame/{$article->id}"),
                ['data-dialog' => '']
            ) ?>
        <? endif; ?>
    <? endif; ?>
    <? if ($article->user->id === $GLOBALS['user']->id || $is_admin): ?>
        <?= Studip\LinkButton::create(
            $_('Bearbeiten'),
            $controller->url_for("article/edit/{$article->id}", ['return_to' => Request::get('return_to')]),
            ['data-dialog' => '']
        ) ?>
        <?= Studip\LinkButton::create(
            $_('Löschen'),
            $controller->url_for("article/delete/{$article->id}", ['return_to' => Request::get('return_to')]),
            ['data-confirm' => $_('Wollen Sie diesen Artikel wirklich löschen?')]
        ) ?>
    <? endif; ?>
    </footer>
</article>
