<table border="0" cellpadding="5" cellspacing="0" width="100%" class="zebra-hover">
<? foreach ($result['artikel'] as $index => $a): ?>
    <tr>
        <td>
            <?= $a ?>
        </td>
    </tr>
<? endforeach; ?>
<? if($result['thema']->getArtikelCount() == 0): ?>
    <tr>
        <td><span style="font-size: smaller;">keine Anzeigen vorhanden</span></td>
    </tr>
<? endif; ?>
</table>

<? if ($result['permission'] === true && !$blacklisted): ?>
<div align="center" style="padding: 3px;">
    <?= Studip\LinkButton::create(_('Anzeige erstellen'),
                                  URLHelper::getLink($link_artikel, array('thema_id' => $result['thema']->getThemaId()))) ?>
</div>
<? endif; ?>