<h2><?= $_('Themenübersicht') ?></h2>
<ul class="sb-categories">
<? foreach ($categories as $category): ?>
    <?= $this->render_partial('category.php', compact('category')) ?>
<? endforeach; ?>
</ul>


<? if (!empty($newest)): ?>
<h2><?= sprintf($_('Die %u neuesten Anzeigen'), count($newest)) ?></h2>
<ul class="sb-articles">
<? foreach ($newest as $article): ?>
    <?= $this->render_partial('article-li.php', compact('article')) ?>
<? endforeach; ?>
</ul>
<? endif; ?>

<?= formatReady(Config::get()->BULLETIN_BOARD_RULES) ?>
