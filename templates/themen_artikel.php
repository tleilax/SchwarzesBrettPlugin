<? if ($result['thema']->getArtikelCount() == 0): ?>
<p style="text-align: center;"><?= _('Keine Anzeigen vorhanden') ?></p>
<? else: ?>
<table class="default zebra">
<? foreach ($result['artikel'] as $a): ?>
    <tr>
        <td><?= $a ?></td>
    </tr>
<? endforeach; ?>
</table>
<? endif; ?>

<? if ($result['permission'] === true && !$blacklisted): ?>
<div style="text-align: center;">
    <?= Studip\LinkButton::create(_('Eine neue Anzeige erstellen'),
                                  URLHelper::getLink($link_artikel, array('thema_id' => $result['thema']->getThemaId()))) ?>
</div>
<? endif; ?>