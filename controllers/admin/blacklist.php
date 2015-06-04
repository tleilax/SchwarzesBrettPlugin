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
    }

    public function remove_action($id)
    {
        CSRFProtection::verifyUnsafeRequest();

        if ($this->checkTicket()) {
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

        if ($this->checkTicket()) {
            // Find user
            $user_id = Request::option('user_id');
            $user    = SBUser::find($user_id);

            // Add user to blacklist
            $item = new SBBlacklist();
            $item->user_id = $user->id;
            $item->store();

            // Hide all user's article
            foreach ($user->articles as $article) {
                $article->visible = false;
                $article->store();
            }

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
