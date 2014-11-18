<form action="<?= $url ?>" method="post" data-dialog data-shiftcheck>
    <fieldset>
        <legend><?= _('Folgende Kategorieren anzeigen') ?></legend>
        
    <? foreach ($categories as $category): ?>
        <div class="type-checkbox">
            <input type="checkbox" name="categories[]" value="<?= $category->id ?>"
                   id="category-<?= $category->id ?>"
                   <? if ($selected === false || in_array($category->id, $selected)) echo 'checked'; ?>>
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
