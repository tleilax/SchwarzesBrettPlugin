<li>
    <a href="<?= $controller->url_for('category/view/' . $category->id) ?>" class="category <?= $category->new ? 'unseen' : 'seen' ?>">
        <?= htmlReady($category->titel) ?>
    <? if ($count = count($category->articles)): ?>
        (<?= number_format($count, 0, ',', '.') ?>)
    <? endif; ?>
    <? if ($category->beschreibung): ?>
        <?= tooltipIcon($category->beschreibung) ?>
    <? endif; ?>
    </a>
<? if ($rss_enabled || $is_admin): ?>
    <div class="options">
    <? if ($is_admin): ?>
        <a href="<?= $controller->url_for('category/edit/' . $category->id) ?>" data-dialog>
            <?= Assets::img('icons/16/blue/edit.png', tooltip2(_('Thema bearbeiten'))) ?>
        </a>
        <a href="<?= $controller->url_for('category/delete/' . $category->id) ?>" data-confirm="<?= _('Wollen Sie dieses Thema wirklich löschen?') ?>">
            <?= Assets::img('icons/16/blue/trash.png', tooltip2(_('Thema löschen'))) ?>
        </a>
    <? endif; ?>
    <? if ($rss_enabled): ?>
        <a href="<?= $controller->url_for('rss/' . $category->id) ?>">
            <?= Assets::img('icons/16/blue/rss.png', tooltip2(_('RSS-Feed zu dieser Kategorie abrufen'))) ?>
        </a>
    <? endif; ?>
    </div>
<? endif; ?>
</li>
