<?= _('Folgende Anzeige wurde gemeldet') ?>:

<?= _('Titel') ?>: <?= $article->titel . PHP_EOL ?>
<?= _('Text') ?>: <?= $article->beschreibung . PHP_EOL ?>
<?= _('ID') ?>: <?= $article->id . PHP_EOL ?>
<?= _('Autor') ?>: <?= $article->user->getFullname() ?> (<?= $article->user->username ?>, <?= $article->user->email ?>)
<?= _('Gemeldet von') ?>: <?= $GLOBALS['user']->getFullname() ?> (<?= $GLOBALS['user']->username ?>, <?= $GLOBALS['user']->email ?>)
<?= _('Grund') ?>: <?= $reason . PHP_EOL ?>
<?= _('Anzeige löschen') ?>: <?= $controller->absolute_url_for('article/delete/' . $article->id) . PHP_EOL ?>
<?= _('Anzeige bearbeiten') ?>: <?= $controller->absolute_url_for('article/edit/' . $article->id) . PHP_EOL ?>
<?= _('Antworten') ?>: <?= $controller->absolute_url_for('dispatch.php/messages/write', array(
                            'rec_uname'      => $GLOBALS['user']->username,
                            'messagesubject' => rawurlencode($article->titel),
                            'message'        => '[quote] ' . $article->beschreibung . ' [/quote]',    
                        )) ?>
