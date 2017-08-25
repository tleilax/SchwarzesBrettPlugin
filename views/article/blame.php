<form method="post" action="<?= $controller->url_for('article/blame/' . $article->id) ?>" class="studip_form" data-dialog>
    <fieldset>
        <legend class="hide-in-dialog">
            <?= htmlReady(sprintf(
                $_('Anzeige "%s" von %s melden'),
                $article->titel,
                $article->user->getFullname()
            )) ?>
        </legend>

        <fieldset>
            <label for="reason"><?= $_('Grund') ?>:</label>
            <textarea required name="reason" id="reason" placeholder="<?= $_('Bitte geben Sie einen aussagekräftigen Grund ein, weshalb diese Anzeige nicht den Regeln entspricht.') ?>"></textarea>
        </fieldset>

    </fieldset>
    <footer data-dialog-button>
        <?= Studip\Button::createAccept($_('Anzeige melden'), 'submit') ?>
        <?= Studip\LinkButton::createCancel(
            $_('Abbrechen'),
            $controller->url_for('article/' . $article->id)
        ) ?>
    </footer>
</form>
