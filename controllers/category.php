<?php
class CategoryController extends SchwarzesBrettController
{
    public function before_filter(&$action, &$args)
    {
        $tmp_args = array_filter($args);
        if ($action === 'view' && empty($tmp_args)) {
            $action = 'list';
        }

        if (!method_exists($this, $action . '_action')) {
            array_unshift($args, $action);
            $action = 'view';
        }

        parent::before_filter($action, $args);

        if (SBUser::Get()->isBlacklisted()) {
            PageLayout::postMessage(MessageBox::info(_('Sie wurden gesperrt und können daher keine Anzeigen erstellen. Bitte wenden Sie sich an den Systemadministrator.')));
        }
    }

    public function index_action($category_id = null)
    {
        $url = $this->url_for('category/view/' . $category_id);
        $this->redirect($url);
    }

    public function list_action()
    {
        Navigation::activateItem('/schwarzesbrettplugin/show/all');

        $this->categories = $this->is_admin
                          ? SBCategory::findBySQL('1 ORDER BY titel COLLATE latin1_german1_ci ASC')
                          : SBCategory::findByVisible(1, 'ORDER BY titel COLLATE latin1_german1_ci ASC');
        $this->newest = SBArticle::findNewest($this->newest_limit);

        $this->inject_rss();
    }

    public function view_action($category_id)
    {
        Navigation::activateItem('/schwarzesbrettplugin/show/all');

        $this->inject_rss($category_id);

        $this->category = SBCategory::find($category_id);
        $this->articles = $this->is_admin
                        ? $this->category->articles
                        : $this->category->visible_articles;
    }

    public function choose_action()
    {
        $id = Request::option('id');
        if (!$id) {
            $this->redirect('category/list');
        } else {
            $this->redirect('category/view/' . $id);
        }
    }

    public function visit_action($id = null)
    {
        SBCategory::visitAll($id);

        if ($id) {
            $category = SBCategory::find($id);
            $message = sprintf(_('Thema "%s" wurde als besucht markiert.'), $category->titel);
        } else {
            $message = _('Alle Themen wurden als besucht markiert');
        }
        if (Request::isXhr()) {
            $this->response->add_header('X-Dialog-Close', 1);
            $this->render_nothing();
        } else {
            PageLayout::postMessage(MessageBox::success($message));
            $this->redirect('category/view/' . $id);
        }
    }

    public function create_action()
    {
        PageLayout::setTitle(_('Thema erstellen'));

        $this->category = new SBCategory();

        $this->render_action('edit');
    }

    public function edit_action($id)
    {
        PageLayout::setTitle(_('Thema bearbeiten'));

        $this->category = SBCategory::find($id);
    }

    public function store_action($id = null)
    {
        CSRFProtection::verifyUnsafeRequest();

        if (Request::isPost() && check_ticket(Request::get('studip_ticket'))) {
            $category = $id
                      ? SBCategory::find($id)
                      : new SBCategory();

            $category->titel        = trim(Request::get('titel'));
            $category->beschreibung = trim(Request::get('beschreibung'));
            $category->perm         = Request::option('thema_perm');
            $category->visible      = Request::int('visible', 0);
            $category->publishable  = Request::int('publishable', 0);
            $category->terms        = trim(Request::get('terms'));
            $category->disclaimer   = trim(Request::get('disclaimer'));
            $category->user_id      = $category->user_id ?: $GLOBALS['user']->id;
            $category->display_terms_in_article =
                Request::int('display_terms_in_article');
            $category->store();

            $message = $id === null
                     ? _('Das Thema wurde angelegt.')
                     : _('Das Thema wurde gespeichert.');
            PageLayout::postMessage(MessageBox::success($message));
        }

        $this->redirect('category/view/' . $id);
    }

    public function delete_action($id)
    {
        $category = SBCategory::find($id);
        $title = $category->titel;
        $count = count($category->articles);
        $category->delete();

        $message = sprintf(_('Das Thema "%s" und alle %u darin enthaltenen Anzeigen wurde gelöscht.'),
                           $title, $count);
        PageLayout::postMessage(MessageBox::success($message));

        $this->redirect('category/list');
    }

    public function bulk_action($id)
    {
        if (!Request::isPost()) {
            throw new MethodNotAllowedException();
        }

        $ids = Request::optionArray('ids');

        if (Request::submitted('move')) {
            PageLayout::setTitle(_('Neue Kategorie'));

            $this->ids         = $ids;
            $this->category_id = $id;
            $this->categories  = SBCategory::findByVisible(1, 'ORDER BY titel COLLATE latin1_german1_ci');
            $this->render_template('category/bulk-move.php', $this->layout);
        } elseif (Request::submitted('moved')) {
            $category_id = Request::option('category_id');
            
            if ($category_id !== $id) {
                $articles = SBArticle::findMany($ids);
                foreach ($articles as $article) {
                    $article->thema_id = $category_id;
                    $article->store();
                }

                $message = sprintf(_('%u Artikel wurde(n) verschoben.'), count($ids));
                PageLayout::postMessage(MessageBox::success($message));
            }

            $this->redirect('category/view/' . $id);
        } elseif (Request::submitted('delete')) {
            $deleted = 0;

            $articles = SBArticle::findMany($ids);
            foreach ($articles as $article) {
                $deleted += (int)($article->delete() !== false);
            }

            $message = sprintf(_('%u Artikel wurde(n) gelöscht.'), $deleted);
            PageLayout::postMessage(MessageBox::success($message));

            $this->redirect('category/view/' . $id);
        }
    }
}
