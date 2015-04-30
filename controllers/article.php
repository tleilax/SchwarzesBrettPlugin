<?php
class ArticleController extends SchwarzesBrettController
{
    public function view_action($id)
    {
        $this->article = SBArticle::find($id);
        $this->article->visit();

        PageLayout::setTitle($this->article->titel . ' (' . $this->article->category->titel . ')');
    }

    public function own_action()
    {
        try {
            Navigation::activateItem('/schwarzesbrettplugin/show/own');
        } catch (Exception $e) {
            $this->redirect('category/view');
            return;
        }

        PageLayout::setTitle(_('Meine Anzeigen'));

        $articles = SBUser::get()->articles;
        $this->categories = SBArticle::groupByCategory($articles);
    }
    
    public function create_action($category_id = null)
    {
        PageLayout::setTitle(_('Anzeige erstellen'));
        
        $this->article           = new SBArticle();
        $this->article->thema_id = $category_id;
        $this->categories        = $this->getCategories();
        
        $this->render_action('edit');
    }
    
    public function edit_action($id = null)
    {
        PageLayout::setTitle(_('Anzeige bearbeiten'));
        
        $this->id         = $id;
        $this->article    = SBArticle::find($id);
        $this->categories = $this->getCategories();
    }
    
    public function store_action($id = null)
    {
        CSRFProtection::verifyUnsafeRequest();
        
        if (Request::isPost() && check_ticket(Request::get('studip_ticket'))) {
            $article = $id
                     ? SBArticle::find($id)
                     : new SBArticle();

            $article->thema_id     = Request::option('thema_id');
            $article->titel        = Request::get('titel');
            $article->beschreibung = transformBeforeSave(Request::get('beschreibung'));
            $article->visible      = Request::int('visible', 0);
            $article->publishable  = Request::int('publishable', 1);
            $article->user_id      = $article->user_id ?: $GLOBALS['user']->id;
            $article->expires      = time() + Config::get()->BULLETIN_BOARD_DURATION * 24 * 60 * 60;
            $article->store();
        
            $message = $id === null
                     ? _('Die Anzeige wurde erstellt.')
                     : _('Die Anzeige wurde gespeichert.');
            PageLayout::postMessage(MessageBox::success($message));
        }

        $this->redirect(Request::get('return_to') ?: $this->url_for('category/view/' . $article->thema_id . '#sb-article-' . $article->id));
    }
    
    private function getCategories()
    {
        return SBCategory::findByVisible(1, 'ORDER BY titel COLLATE latin1_german1_ci');
    }

    public function delete_action($id)
    {
        $article = SBArticle::find($id);
        if (!$this->is_admin && $article->user_id !== $GLOBALS['user']->id) {
            throw new AccessDeniedException(_('Sie dürfen diese Anzeige nicht löschen.'));
        }

        $article->delete();

        PageLayout::postMessage(MessageBox::success(_('Die Anzeige wurde gelöscht.')));

        $this->redirect(Request::get('return_to') ?: $this->url_for('category'));
    }

    public function blame_action($id)
    {
        if (!$this->blame_enabled) {
            throw new Exception(_('Die Funktionen zum Anzeigen melden sind nicht aktiviert.'));
        }

        $this->article = SBArticle::find($id);

        if (Request::isPost()) {
            $reason = Request::get('reason');

            $template = $this->get_template_factory()->open('article/blame-mail.php');
            $template->controller = $this;
            $template->article    = $this->article;
            $template->reason     = $reason;
            $mailbody = $template->render();

            $mail = new StudipMail();
            $mail->addRecipient(Config::get()->BULLETIN_BOARD_BLAME_RECIPIENTS)
                 ->setSubject(_('Anzeige wurde gemeldet'))
                 ->setBodyText($mailbody)
                 ->send();

            PageLayout::postMessage(MessageBox::info(_('Die Anzeige wurde den Administratoren gemeldet.')));

            $this->redirect('category/' . $this->article->category->id);
            return;
        }

        PageLayout::setTitle(sprintf(_('Anzeige "%s" von %s melden'),
                                     $this->article->titel,
                                     $this->article->user->getFullname()));
    }
    
    public function purge_action()
    {
        $GLOBALS['perm']->check('root');

        $articles = SBArticle::findBySQL('expires < UNIX_TIMESTAMP()');
        foreach ($articles as $article) {
            $article->delete();
        }
        
        $message = count($articles) > 0
                 ? MessageBox::success(sprintf(_('Es wurden %u Anzeigen aus der Datenbank gelöscht.'), count($articles)))
                 : MessageBox::info(_('Es gibt keine Artikel in der Datenbank, die gelöscht werden können.'));
    
        PageLayout::postMessage($message);
        
        $this->redirect('admin/settings');
    }
}