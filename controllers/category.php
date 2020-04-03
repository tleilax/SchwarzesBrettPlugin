<?php
use SchwarzesBrett\Article;
use SchwarzesBrett\Category;
use SchwarzesBrett\User;

class CategoryController extends SchwarzesBrett\Controller
{
    protected $_autobind = true;

    public function before_filter(&$action, &$args)
    {
        $tmp_args = array_filter($args);
        if ($action === 'view' && !$tmp_args) {
            $action = 'list';
        }

        if (!method_exists($this, $action . '_action')) {
            array_unshift($args, $action);
            $action = 'view';
        }

        parent::before_filter($action, $args);

        if (User::Get()->isBlacklisted()) {
            PageLayout::postInfo(
                $this->_('Sie wurden gesperrt und können daher keine Anzeigen erstellen. Bitte wenden Sie sich an den Systemadministrator.')
            );
        }
    }

    public function index_action($category_id = null)
    {
        $url = $this->url_for("category/view/{$category_id}");
        $this->redirect($url);
    }

    public function list_action()
    {
        Navigation::activateItem('/schwarzesbrettplugin/show/all');

        $this->categories = $this->is_admin
                          ? Category::findBySQL('1 ORDER BY titel ASC')
                          : Category::findByVisible(1, 'ORDER BY titel ASC');
        $this->newest = Article::findNewest($this->newest_limit);

        $this->inject_rss();
    }

    public function view_action(SchwarzesBrett\Category $category)
    {
        Navigation::activateItem('/schwarzesbrettplugin/show/all');

        $this->inject_rss($category->id);

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
            $this->redirect("category/view/{$id}");
        }
    }

    public function visit_action($id = null)
    {
        Category::visitAll($id);

        if ($id) {
            $category = Category::find($id);
            $message = sprintf(
                $this->_('Thema "%s" wurde als besucht markiert.'),
                $category->titel
            );
        } else {
            $message = $this->_('Alle Themen wurden als besucht markiert');
        }
        if (Request::isXhr()) {
            $this->response->add_header('X-Dialog-Close', 1);
            $this->render_nothing();
        } else {
            PageLayout::postSuccess($message);
            $this->redirect("category/view/{$id}");
        }
    }

    public function create_action()
    {
        PageLayout::setTitle($this->_('Thema erstellen'));

        $this->category = new Category();

        $this->render_action('edit');
    }

    public function edit_action(SchwarzesBrett\Category $category)
    {
        if (!$category->mayEdit()) {
            throw new AccessDeniedException($this->_('Sie dürfen dieses Thema nicht bearbeiten.'));
        }

        PageLayout::setTitle($this->_('Thema bearbeiten'));
    }

    public function store_action(SchwarzesBrett\Category $category = null)
    {
        CSRFProtection::verifyUnsafeRequest();

        if (Request::isPost() && check_ticket(Request::get('studip_ticket'))) {
            if (!$category->mayEdit()) {
                throw new AccessDeniedException($this->_('Sie dürfen dieses Thema nicht bearbeiten.'));
            }

            $category->titel        = Request::i18n('titel');
            $category->beschreibung = Request::i18n('beschreibung');
            $category->perm         = Request::option('thema_perm');
            $category->visible      = Request::int('visible', 0);
            $category->publishable  = Request::int('publishable', 0);
            $category->terms        = Request::i18n('terms');
            $category->disclaimer   = Request::i18n('disclaimer');
            $category->display_terms_in_article =
                Request::int('display_terms_in_article');
            $category->store();

            $message = $id === null
                     ? $this->_('Das Thema wurde angelegt.')
                     : $this->_('Das Thema wurde gespeichert.');
            PageLayout::postSuccess($message);
        }

        $this->redirect("category/view/{$id}");
    }

    public function delete_action(SchwarzesBrett\Category $category)
    {
        $title = $category->titel;
        $count = count($category->articles);
        $category->delete();

        if (!$category->mayEdit()) {
            throw new AccessDeniedException($this->_('Sie dürfen dieses Thema nicht löschen.'));
        }


        $message = sprintf($this->_('Das Thema "%s" und alle %u darin enthaltenen Anzeigen wurde gelöscht.'),
                           $title, $count);
        PageLayout::postSuccess($message);

        $this->redirect('category/list');
    }

    public function bulk_action($id)
    {
        if (!Request::isPost()) {
            throw new MethodNotAllowedException();
        }

        $ids = Request::optionArray('ids');

        if (Request::submitted('move')) {
            PageLayout::setTitle($this->_('Neue Kategorie'));

            $this->ids         = $ids;
            $this->category_id = $id;
            $this->categories  = Category::findByVisible(1, 'ORDER BY titel ASC');
            $this->render_template('category/bulk-move.php', $this->layout);
        } elseif (Request::submitted('moved')) {
            $category_id = Request::option('category_id');

            if ($category_id !== $id) {
                $articles = Article::findMany($ids);
                foreach ($articles as $article) {
                    $article->thema_id = $category_id;
                    $article->store();
                }

                $message = sprintf($this->_('%u Artikel wurde(n) verschoben.'), count($ids));
                PageLayout::postSuccess($message);
            }

            $this->redirect("category/view/{$id}");
        } elseif (Request::submitted('delete')) {
            $deleted = 0;

            $articles = Article::findMany($ids);
            foreach ($articles as $article) {
                $deleted += (int)($article->delete() !== false);
            }

            $message = sprintf($this->_('%u Artikel wurde(n) gelöscht.'), $deleted);
            PageLayout::postSuccess($message);

            $this->redirect("category/view/{$id}");
        }
    }
}
