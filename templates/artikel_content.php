<? $usr = User::find($a->getUserId()) ?>
<a id="close_<?=$a->getArtikelId()?>" href="javascript: closeArtikel('<?=$a->getArtikelId()?>');">
    <?= Assets::img('icons/16/blue/arr_1down.png', array('id' => 'indikator_offen_' . $a->getArtikelId(), 'class' => 'text-top')) ?>
    <?= htmlReady($a->getTitel()) ?>
</a>
<? if ($a->getVisible() == 0): ?>
    <?= Assets::img('icons/16/red/exclaim-circle.png', array('class' => 'text-top', 'title' => _('Diese Anzeige ist nicht für andere sichtbar'))) ?>
<? endif; ?>
<div style="border-bottom: 1px solid #8e8e8e; padding-bottom: 3px;">
    <?= formatReady($a->getBeschreibung())?>
</div>
<div align="right" style="font-size:smaller; padding:1px 0px 5px 0px;">
    erstellt von
    <a href="<?=URLHelper::getLink('dispatch.php/profile', array('username' => $usr->username)) ?>">
        <?= $usr->getFullName() ?>
    </a>
    | gültig bis bis <?= date('d.m.Y', $a->getMkdate() + $zeit) ?>
</div>
<div align="center" style="padding-bottom: 5px;">
<? if ($antwort === true): ?>
    <?= Studip\LinkButton::create(_('Antworten'),
                                  URLHelper::getLink('sms_send.php',
                                                     array('rec_uname' => $usr->username,
                                                           'messagesubject' => rawurlencode($a->getTitel()),
                                                           'message' => '[quote] '.$a->getBeschreibung().' [/quote]')),
                                  array('title' => _('Dem Benutzer eine Email schreiben'))) ?>
<? endif; ?>
<? if ($access === true): ?>
    <?= Studip\LinkButton::create(_('Bearbeiten'), $link_edit, array('title' => _('Diese Anzeige bearbeiten'))) ?>
    <?= Studip\LinkButton::createCancel(_('Löschen'), $link_delete, array('title' => _('Diese Anzeige löschen'))) ?>
<? endif; ?>
</div>
