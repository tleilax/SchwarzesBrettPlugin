<?php
class Admin_BlacklistController extends SchwarzesBrettController
{
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        $GLOBALS['perm']->check('root');
    }

    public function index_action()
    {
        Navigation::activateItem('/schwarzesbrettplugin/root/blacklist');

        if (Request::isPost()) {
            CSRFProtection::verifyUnsafeRequest();
        }

        $this->users = SBBlacklist::findBySQL('1');
/*
            if (Request::get('action') == 'delete'){
                $db = DBManager::get()->prepare("DELETE FROM sb_blacklist WHERE user_id = ?");
                $db->execute(array(Request::option('user_id')));

                $template->message = MessageBox::success(_('Der Benutzer wurde erfolgreich von der Blacklist entfernt und kann nun wieder Anzeigen erstellen.'));
            } elseif (Request::get('action') == 'add' && Request::option('user_id')) {
                //datenbank
                $db = DBManager::get()->prepare("REPLACE INTO sb_blacklist SET user_id = ?, mkdate = UNIX_TIMESTAMP()");
                $db->execute(array(Request::option('user_id')));

                                //nachricht an den benutzer
                $messaging = new messaging();
                $msg = _("Aufgrund von wiederholten Verstößen gegen die Nutzungsordnung wurde Ihr Zugang zum Schwarzen Brett gesperrt. Sie können keine weiteren Anzeigen erstellen.\n\n Bei Fragen wenden Sie sich bitte an die Systemadministratoren.");
                $messaging->insert_message($msg, get_username(Request::option('user_id')), "____%system%____", FALSE, FALSE, 1, FALSE, "Schwarzes Brett: Sie wurden gesperrt.");

                $template->message = MessageBox::success(_('Der Benutzer wurde erfolgreich auf die Blacklist gesetzt.'));
            }

            $users = DBManager::get()
                   ->query("SELECT * FROM sb_blacklist")
                   ->fetchAll(PDO::FETCH_ASSOC);

            $template->users = $users;
            $template->link  = PluginEngine::getURL($this, array(), 'blacklist');
            echo $template->render();
*/
    }

    public function remove_action($id = null)
    {
        CSRFProtection::verifyUnsafeRequest();

        if (($ticket = Request::get('studip_ticket')) && check_ticket($ticket)) {
            if ($id === 'bulk') {
                $ids = Request::optionArray('user_id');
            } else {
                $ids = array($id);
            }

            $users = SBBlacklist::findMany($ids);
            foreach ($users as $user) {
                $user->delete();
            }

            $message = count($ids) === 1
                     ? _('Der Nutzer wurde von der schwarzen Liste entfernt.')
                     : sprintf(_('%u Nutzer wurden von der schwarzen Liste entfernt.'), count($ids));
            PageLayout::postMessage(MessageBox::success($message));
        }

        $this->redirect('admin/blacklist');
    }

    public function add_action()
    {
        CSRFProtection::verifyUnsafeRequest();

        if (($ticket = Request::get('studip_ticket')) && check_ticket($ticket)) {
            $item = new SBBlacklist();
            $item->user_id = Request::option('user_id');
            $item->store();

            $msg = _('Aufgrund von wiederholten Verstößen gegen die Nutzungsordnung wurde '
                    .'Ihr Zugang zum Schwarzen Brett gesperrt.');
            $msg .= ' ';
            $msg .= _('Sie können keine weiteren Anzeigen erstellen.');
            $msg .= PHP_EOL . PHP_EOL;
            $msg .= _('Bei Fragen wenden Sie sich bitte an die Systemadministratoren.');

            $subject = _('Schwarzes Brett: Sie wurden gesperrt.');

            $messaging = new messaging();
            $messaging->insert_message($msg, $item->user->username, '____%system%____', false, false, 1, false, $subject);

            PageLayout::postMessage(MessageBox::success(_('Der Nutzer wurde auf die schwarze Liste gesetzt.')));
        }

        $this->redirect('admin/blacklist');
    }
}
