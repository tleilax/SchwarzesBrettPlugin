<? if (count($results) > 0) : ?>
<table class="default zebra">
    <colgroup>
        <col width="1%">
        <col>
        <col>
    </colgroup>
    <thead>
        <tr>
            <th colspan="3" class="table_header_bold">
                <?= _('Benutzer mit mehr als einem Eintrag') ?>
            </th>
        </tr>
        <tr>
            <th>&nbsp;</th>
            <th>Benutzer</th>
            <th>Anzeigen</th>
        </tr>
    </thead>
    <tbody style="vertical-align: top;">
    <? foreach ($results as $i => $result):
         if (!$usr = User::find($result['user_id'])) {
             continue;
         }
    ?>
        <tr>
            <td style="text-align: right;"><?= $i + 1 ?>.</td>
            <td>
                <a href="<?= URLHelper::getLink('dispatch.php/profile', array('username' => $usr->username)) ?>">
                    <?= Avatar::getAvatar($usr->id)->getImageTag(Avatar::SMALL) ?>
                    <?= htmlReady($usr->getFullName()) ?>
                </a>
            </td>
            <td>
                <table class="default zebra-hover">
                <? foreach ($result['artikel'] as $artikel) : ?>
                    <tbody>
                        <tr>
                            <td>
                                <?= date('d.m.Y', $artikel['mkdate']) ?>:
                                <a href="<?= URLHelper::getLink($link, array('modus' => 'show_search_results', 'search_user' => $usr->id)) ?>">
                                    <?= htmlReady($artikel['titel']) ?> (<?= htmlReady($artikel['thema']) ?>)
                                </a>
                            </td>
                            <td style="text-align: right;">
                                <a href="<?= URLHelper::getLink($link_edit, array('artikel_id' => $artikel['artikel_id'], 'thema_id' => $artikel['thema_id'])) ?>">
                                    <?= Assets::img('icons/16/blue/edit.png', array('title' => 'Anzeige bearbeiten')) ?>
                                </a>
                                <a href="<?= URLHelper::getLink($link_delete, array('artikel_id' => $artikel['artikel_id'])) ?>">
                                    <?= Assets::img('icons/16/blue/trash.png', array('title' => 'Anzeige löschen')) ?>
                                </a>
                            </td>
                        </tr>
                    </tbody>
                <? endforeach ?>
                </table>
            </td>
        </tr>
    <? endforeach ?>
    </tbody>
</table>
<? else : ?>
<?= MessageBox::info(_('Es sind keine doppelten Einträge vorhanden.')) ?>
<? endif ?>