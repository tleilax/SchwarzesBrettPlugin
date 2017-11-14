<form method="post" action="<?= $controller->url_for('admin/blacklist/add') ?>" class="sb-search-form">
    <?= CSRFProtection::tokenTag() ?>
    <input type="hidden" name="studip_ticket" value="<?= get_ticket() ?>">
    <?= QuickSearch::get('user_id', new StandardSearch('user_id'))->withButton()->render() ?>
    <?= Studip\Button::create($_('Benutzer auf die schwarze Liste setzen')) ?>
</form>

<? if (count($users) === 0) : ?>
<?= MessageBox::info($_('Es befinden sich keine Benutzer auf der schwarzen Liste.')) ?>
<? else: ?>
<form action="<?= $controller->url_for('admin/blacklist/remove/bulk') ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>
    <input type="hidden" name="studip_ticket" value="<?= get_ticket() ?>">

    <table class="default" id="blacklist">
        <caption>
            <div class="actions">
                <?= sprintf($_('%u Nutzer'), count($users)) ?>
            </div>
            <?= $_('Personen auf der schwarzen Liste') ?>
        </caption>
        <colgroup>
            <col width="20px">
            <col>
            <col width="24px">
        </colgroup>
        <thead>
            <tr>
                <th>
                    <input type="checkbox"
                           data-proxyfor="#blacklist tbody :checkbox"
                           data-activates="#blacklist tfoot button">
                </th>
                <th><?= $_('Benutzer') ?></th>
                <th class="actions">&nbsp;</th>
            </tr>
        </thead>
        <tbody>
        <? foreach ($users as $i => $user) : ?>
            <tr>
                <td>
                    <input type="checkbox" name="user_id[]" value="<?= $user->id ?>">
                </td>
                <td>
                    <a href="<?= URLHelper::getLink('dispatch.php/profile', ['username' => $user->user->username]) ?>">
                        <?= Avatar::getAvatar($user->id)->getImageTag(Avatar::SMALL) ?>
                        <?= htmlReady($user->user->getFullname()) ?>
                    </a>
                    (<?= htmlReady($user->user->username) ?> / <?= htmlReady($user->user->perms) ?>)
                </td>
                <td class="actions">
                    <?= Icon::create('trash')->asInput(
                        tooltip2($_('Benutzer von der schwarzen Liste entfernen')) +
                        ['formaction' => $controller->url_for("admin/blacklist/remove/{$user->id}")]
                    ) ?>
                </td>
            </tr>
        <? endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3">
                    <?= _('Alle markierten') ?>
                    <?= Studip\Button::create(_('Entfernen')) ?>
                </td>
            </tr>
        </tfoot>
    </table>
</form>
<? endif; ?>
