<?= $message ?>

<form method="post" action="<?= $link ?>">
<input type="hidden" name="action" value="save">
<table class="default">
    <tbody>
        <tr class="cycle_odd">
            <td><?=$descDuration?></td>
            <td><input type="text" class="allow-only-numbers" name="duration" value="<?=$duration ?>"></td>
        </tr>
        <tr class="cycle_even">
            <td><?=$descAnnouncements?></td>
            <td><input type="text" class="allow-only-numbers" name="announcements" value="<?=$announcements ?>"></td>
        </tr>
        <tr class="cycle_odd">
            <td><?=$descEnableBlame?></td>
            <td><input type="checkbox" value="1" name="enableBlame" <? if ($enableBlame == 1) echo 'checked="checked"'; ?>></td>
        </tr>
        <tr class="cycle_even">
            <td><?=$descBlameRecipients?></td>
            <td><input type="text" name="blameRecipients" value="<?=$blameRecipients ?>"></td>
        </tr>
        <tr class="cycle_odd">
            <td><?=$descEnableRss?></td>
            <td><input type="checkbox" value="1" name="enableRss" <? if ($enableRss == 1) echo 'checked="checked"'; ?>></td>
        </tr>
        <tr class="steel2">
            <td colspan="2" style="text-align: center;">
                <?= Studip\Button::createAccept(_('Speichern'), 'speichern') ?>
            </td>
        </tr>
    </tbody>
</table>
</form>

<?= MessageBox::info(_('Hinweise'), array('Bei Aktivierung der RSS Feeds muss das Plugin für nobody freigegeben werden, damit die Feeds ohne Login abgerufen werden können.')) ?>
