<?= $_('Folgende Anzeige wurde gemeldet') ?>:

<?= $_('Gemeldet von') ?>: [<?= $GLOBALS['user']->getFullname('no_title') ?>]<?= URLHelper::getURL('dispatch.php/profile', ['username' => $GLOBALS['user']->username], true) . PHP_EOL ?>
<?= $_('Grund') ?>: **<?= $reason ?>**<?= PHP_EOL ?>

<?= $_('Autor') ?>: [<?= $article->user->getFullname('no_title') ?>]<?= URLHelper::getURL('dispatch.php/profile', ['username' => $article->user->username]) . PHP_EOL ?>
<?= $_('Titel') ?>: **<?= $article->titel ?>** <?= PHP_EOL ?>
<?= $_('Text') ?>:
[quote]<?= $article->beschreibung . PHP_EOL ?>[/quote]

--
[<?= $_('Anzeige anzeigen') ?>]<?= $controller->absolute_url_for("article/view/{$article->id}") ?>
 | [<?= $_('Anzeige bearbeiten') ?>]<?= $controller->absolute_url_for("article/edit/{$article->id}") ?>
 | [<?= $_('Anzeige löschen') ?>]<?= $controller->absolute_url_for("article/delete/{$article->id}") ?>
 | [<?= $_('Antworten') ?>]<?= $controller->absolute_url_for('dispatch.php/messages/write', [
    'rec_uname'      => $GLOBALS['user']->username,
    'messagesubject' => rawurlencode($article->titel),
    'message'        => "[quote]{$article->beschreibung}[/quote]",
]) ?>
