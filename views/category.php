<li>
    <a href="<?= $controller->view($category) ?>" class="category <?= $category->new ? 'unseen' : 'seen' ?>">
        <?= htmlReady($category->titel) ?>
    <? if ($count = count($category->articles)): ?>
        (<?= number_format($count, 0, ',', '.') ?>)
    <? endif; ?>
    <? if ($category->beschreibung): ?>
        <?= tooltipIcon(htmlReady($category->beschreibung)) ?>
    <? endif; ?>
    </a>
<? if ($rss_enabled || $is_admin): ?>
    <div class="options">
    <? if ($is_admin): ?>
        <a href="<?= $controller->edit($category) ?>" data-dialog>
            <?= Icon::create('edit')->asImg(tooltip2($_('Thema bearbeiten'))) ?>
        </a>
        <a href="<?= $controller->delete($category) ?>" data-confirm="<?= $_('Wollen Sie dieses Thema wirklich löschen?') ?>">
            <?= Icon::create('trash')->asImg(tooltip2($_('Thema löschen'))) ?>
        </a>
    <? endif; ?>
    <? if ($rss_enabled): ?>
        <a href="<?= $controller->url_for('rss', $category) ?>">
            <?= Icon::create('rss')->asImg(tooltip2($_('RSS-Feed zu dieser Kategorie abrufen'))) ?>
        </a>
    <? endif; ?>
    </div>
<? endif; ?>
</li>
