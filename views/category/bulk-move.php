<form action="<?= $controller->url_for("category/bulk/{$category_id}") ?>" method="post">
<? foreach ($ids as $id): ?>
    <input type="hidden" name="ids[]" value="<?= htmlReady($id) ?>">
<? endforeach; ?>
    <ul style="list-style: none; margin: 0; padding: 0;">
    <? foreach ($categories as $category): ?>
        <li>
            <label>
                <input type="radio" name="category_id" value="<?= htmlReady($category->id) ?>" <? if ($category->id === $category_id) echo 'checked'; ?>>
                <?= htmlReady($category->titel) ?>
            </label>
        </li>
    <? endforeach; ?>
    </ul>

    <div data-dialog-button>
        <?= Studip\Button::createAccept($_('Verschieben'), 'moved') ?>
        <?= Studip\LinkButton::createCancel(
            $_('Abbrechen'),
            $controller->url_for("category/view/{$category_id}")
        ) ?>
    </div>
</form>
