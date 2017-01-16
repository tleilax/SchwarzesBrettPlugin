<?php
use SchwarzesBrett\Article;
use SchwarzesBrett\User;
use SchwarzesBrett\Watchlist;

class WatchlistController extends SchwarzesBrett\Controller
{
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        Navigation::activateItem('/schwarzesbrettplugin/show/watchlist');
        PageLayout::setTitle(_('Gemerkte Anzeigen'));
    }

    public function index_action()
    {
        $articles = User::get()->watched_articles;
        $this->categories = Article::groupByCategory($articles);
    }

    public function add_action($article_id)
    {
        $entry = new Watchlist([$GLOBALS['user']->id, $article_id]);
        $entry->store();

        $this->flash['send_headers'] = ['X-Article-Watched', $article_id];
        $this->redirect('article/view/' . $article_id);
    }

    public function remove_action($article_id)
    {
        $bulk = $article_id === 'bulk';
        if ($bulk) {
            $article_id = Request::optionArray('ids') ?: '';
        }

        $entry = Watchlist::deleteBySQL('user_id = ? AND artikel_id IN (?)', [
            $GLOBALS['user']->id,
            (array)$article_id
        ]);

        $this->flash['send_headers'] = ['X-Article-Unwatched', $article_id];
        $this->redirect($bulk ? 'watchlist' : ('article/view/' . $article_id));
    }
}
