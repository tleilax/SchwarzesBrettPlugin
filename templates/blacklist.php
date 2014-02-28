<?= $message ?>

<? if (count($users) > 0) : ?>
<h3><?= _('Personen auf der schwarzen Liste') ?></h3>
<table class="default zebra">
    <thead>
        <tr>
            <th></th>
            <th><?= _('Benutzer') ?></th>
            <th></th>
        </tr>
    </thead>
    <tbody>
    <? foreach ($users as $i => $user) : ?>
        <tr>
            <td align="right" width="1%"><?= $i+1 ?>.</td>
            <td>
                <a href="<?= URLHelper::getLink('about.php', array('username' => get_username($user['user_id']))) ?>">
                    <?= get_fullname($user['user_id']) ?>
                </a>
            </td>
            <td align="right">
                <a href="<?= URLHelper::getLink($link, array('user_id' => $user['user_id'], 'action' => 'delete')) ?>">
                    <?= Assets::img('icons/16/blue/trash.png', array('title' => _('Benutzer von der schwarzen Liste entfernen'), 'class' => 'text-top')) ?>
                </a>
            </td>
        </tr>
<? endforeach ?>
    </tbody>
</table>
<? else : ?>
<?= MessageBox::info(_('Es befinden sich keine Benutzer auf der schwarzen Liste.')) ?>
<? endif ?>

<br>
<h3><?= _('Benutzer auf die schwarze Liste setzen') ?></h3>
<form method="post" action="<?= $link ?>">
    <input type="hidden" name="action" value="add">
    <?= QuickSearch::get('user_id', new StandardSearch('user_id'))->withButton()->render() ?>
    <?= Studip\Button::create(_('Hinzufügen'), 'hinzufuegen') ?>
</form>