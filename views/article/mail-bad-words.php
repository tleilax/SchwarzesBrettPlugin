<?= _('Folgende Anzeige enthält diese unzulässigen Begriffe') ?>:
- <?= implode(PHP_EOL . '- ', $bad_words) ?>

<?= _('Autor') ?>: [<?= $article->user->getFullname('no_title') ?>]<?= URLHelper::getURL('dispatch.php/profile?username=' . $article->user->username, array(), true) . PHP_EOL ?>
<?= _('Titel') ?>: **<?= $article->titel ?>** <?= PHP_EOL ?>
<?= _('Text') ?>:
[quote]<?= $article->beschreibung . PHP_EOL ?>[/quote]

--
[<?= _('Anzeige anzeigen') ?>]<?= $controller->absolute_url_for('article/view/' . $article->id) ?>
 | [<?= _('Anzeige bearbeiten') ?>]<?= $controller->absolute_url_for('article/edit/' . $article->id) ?>
 | [<?= _('Anzeige löschen') ?>]<?= $controller->absolute_url_for('article/delete/' . $article->id) ?>
