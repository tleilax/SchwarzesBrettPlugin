<form method="post" action="<?= $controller->blame($article) ?>" class="default" data-dialog>
    <fieldset>
        <legend class="hide-in-dialog">
            <?= htmlReady(sprintf(
                $_('Anzeige "%s" von %s melden'),
                $article->titel,
                $article->user->getFullname()
            )) ?>
        </legend>

        <label>
            <?= $_('Grund') ?>

            <textarea required name="reason" placeholder="<?= $_('Bitte geben Sie einen aussagekräftigen Grund ein, weshalb diese Anzeige nicht den Regeln entspricht.') ?>"></textarea>
        </label>
    </fieldset>

    <footer data-dialog-button>
        <?= Studip\Button::createAccept($_('Anzeige melden'), 'submit') ?>
        <?= Studip\LinkButton::createCancel(
            $_('Abbrechen'),
            $controller->url_for("article/{$article->id}")
        ) ?>
    </footer>
</form>
