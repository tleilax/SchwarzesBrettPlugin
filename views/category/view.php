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
                <?= sprintf(_('%u Anzeigen'), count($articles)) ?>
            </div>
        </div>
    </caption>
    <colgroup>
    </colgroup>
    <thead>
        <tr>
            <th><?= _('Titel') ?></th>
            <th><?= _('Datum') ?></th>
            <th>
                <abbr title="<?= _('Besucher') ?>">
                    #
                </abbr>
            </th>
            <th><?= _('Autor') ?></th>
            <th><?= _('Aktionen') ?></th>
        </tr>
    </thead>
    <tbody>
<? if (count($articles) === 0): ?>
        <tr class="nohover">
            <td colspan="5" style="text-align: center;">
                <?= _('Dieses Thema enthält noch keine Anzeigen.') ?><br>
                <?= Studip\LinkButton::create(_('Anzeige erstellen'),
                                              $controller->url_for('article/create'),
                                              array('data-dialog' => '')) ?>
            </td>
        </tr>
<? else: ?>
    <? foreach ($articles as $article): ?>
        <?= $this->render_partial('article-tr.php', compact('article')) ?>
    <? endforeach; ?>
<? endif; ?>
    </tbody>
</table>