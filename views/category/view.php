<? if ($GLOBALS['user']->perms === 'root'): ?>
<form action="<?= $controller->url_for("category/bulk/{$category->id}") ?>" method="post">
<? endif; ?>
<? if ($category->terms): ?>
    <div class="category-disclaimer">
        <?= formatReady($category->terms) ?>
    </div>
<? endif; ?>
<table class="default sb-category">
    <caption>
        <div class="caption-container">
            <div class="caption-content">
                <?= htmlReady($category->titel) ?>
            <? if (trim($category->beschreibung)): ?>
                <br>
                <small><?= formatReady($category->beschreibung) ?></small>
            <? endif; ?>
            </div>
            <div class="caption-actions">
                <?= sprintf($_('%u Anzeigen'), count($articles)) ?>
            </div>
        </div>
    </caption>
    <colgroup>
    </colgroup>
    <thead>
        <tr>
        <? if ($GLOBALS['user']->perms === 'root'): ?>
            <th>
                <input type="checkbox" data-proxyfor=".sb-category tbody :checkbox" data-activates=".sb-category tfoot button">
            </th>
        <? endif; ?>
            <th><?= $_('Titel') ?></th>
            <th><?= $_('Datum') ?></th>
            <th>
                <abbr title="<?= $_('Besucher') ?>">
                    #
                </abbr>
            </th>
            <th><?= $_('Autor') ?></th>
            <th><?= $_('Aktionen') ?></th>
        </tr>
    </thead>
    <tbody class="sb-articles">
<? if (count($articles) === 0): ?>
        <tr class="nohover">
            <td colspan="<?= 5 + (int)($GLOBALS['user']->perms === 'root') ?>" style="text-align: center;">
                <?= $_('Dieses Thema enthält noch keine Anzeigen.') ?><br>
                <?= Studip\LinkButton::create(
                    $_('Anzeige erstellen'),
                    $controller->url_for("article/create/{$category->id}"),
                    ['data-dialog' => '']
                ) ?>
            </td>
        </tr>
<? else: ?>
    <? foreach ($articles as $article): ?>
        <?= $this->render_partial('article-tr.php', compact('article') + [
            'return_to' => $controller->url_for("category/view/{$category->id}"),
            'checkbox'  => $GLOBALS['user']->perms === 'root',
        ]) ?>
    <? endforeach; ?>
<? endif; ?>
    </tbody>
<? if ($GLOBALS['user']->perms === 'root'): ?>
    <tfoot>
        <tr>
            <td colspan="6">
                <?= $_('Alle markierten') ?>
                <?= Studip\Button::create($_('Verschieben'), 'move', [
                    'data-dialog' => 'size=auto',
                ]) ?>
                <?= Studip\Button::create($_('Löschen'), 'delete', [
                        'onclick' => "return confirm('" . $_('Sollen die Anzeigen wirklich gelöscht werden?') . "');",
                ]) ?>
            </td>
        </tr>
    </tfoot>
<? endif; ?>
</table>
<? if ($GLOBALS['user']->perms === 'root'): ?>
</form>
<? endif; ?>
