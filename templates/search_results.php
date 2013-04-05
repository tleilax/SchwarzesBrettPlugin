<form name="search_form" method="post" action="<?=$link_search?>">
    <table class="default">
        <thead>
            <tr>
                <th class="table_header_bold">
                    <?= _('Allgemeine Suche nach Anzeigen:') ?>
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <label>
                        <?= _('Nach Anzeigen suchen:') ?>
                        <input type="text" style="width: 200px;" name="search_text"
                               value="<?= htmlready(Request::get('search_text')) ?>">
                    </label>
                    <?= Studip\Button::create('Nach Anzeigen suchen', 'submit') ?>
                    <?= Studip\LinkButton::create(_('Zurücksetzen'), $link_back) ?>
                </td>
            </tr>
        </tbody>
    </table>

</form>

<br>

<table class="default" style="margin-bottom:3px;">
    <thead>
        <tr>
            <th class="table_header_bold">
                <?= _('Ergebnisse alphabetisch sortiert, gruppiert nach Themen:') ?>
            </th>
        </tr>
    </thead>
<? foreach ($results as $result): ?>
    <tbody>
        <tr>
            <td style="border-bottom: 1px solid #8e8e8e; font-weight: bold;">
                <?= htmlReady($result['thema_titel']) ?>
            </td>
        </tr>
    <? foreach (array_values($result['artikel']) as $index => $a): ?>
        <tr class="<?= $index % 2 ? 'table_row_even' : 'table_row_odd' ?>">
            <td><?= $a ?></td>
        </tr>
    <? endforeach; ?>
    </tbody>
<? endforeach ?>
</table>
