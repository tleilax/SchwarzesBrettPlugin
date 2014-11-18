<form action="<?= $url ?>" method="post" data-dialog data-shiftcheck>
    <fieldset>
        <legend><?= _('Zeige mir') ?></legend>
        
        <label>
            <select name="count" id="count">
            <? foreach (range(5, 30, 5) as $count): ?>
                <option <? if ($count === $config['count']) echo 'selected'; ?>>
                    <?= $count ?>
                </option>
            <? endforeach; ?>
            </select>
            <?= _('Anzeigen') ?>
        </label>
    </fieldset>

    <fieldset>
        <legend><?= _('Aus den folgenden Themen') ?>:</legend>
        
    <? foreach ($categories as $category): ?>
        <div class="type-checkbox">
            <input type="checkbox" name="categories[]" value="<?= $category->id ?>"
                   id="category-<?= $category->id ?>"
                   <? if ($config['selected'] === false || in_array($category->id, $config['selected'])) echo 'checked'; ?>>
            <label for="category-<?= $category->id ?>">
                <?= htmlReady($category->titel) ?>
            </label>
        </div>
    <? endforeach; ?>
    </fieldset>
    
    <div data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern')) ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen'), URLHelper::getLink('dispatch.php/start')) ?>
    </div>
</form>
