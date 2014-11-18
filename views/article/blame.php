<form method="post" action="<?= $controller->url_for('article/blame/' . $article->id) ?>" class="studip_form" data-dialog>
    <fieldset>
        <legend class="hide-in-dialog">
            <?= sprintf(_('Anzeige "%s" von %s melden'),
                        $article->titel,
                        $article->user->getFullname()) ?>
        </legend>
    
        <fieldset>
            <label for="reason"><?= _('Grund') ?>:</label>
            <textarea required name="reason" id="reason" placeholder="<?= _('Bitte geben Sie einen aussagekrätigen Grund ein, weshalb diese Anzeige nicht den Regeln entspricht.') ?>"></textarea>
        </fieldset>
        
        <?= Studip\Button::createAccept(_('Anzeige melden'), 'submit', array('data-dialog-button' => '')) ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen'), $controller->url_for('article/' . $article->id), array('data-dialog-button' => '')) ?>
    </fieldset>
</form>
