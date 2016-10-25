<?php
class WatchlistController extends SchwarzesBrettController
{
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        Navigation::activateItem('/schwarzesbrettplugin/show/watchlist');
        PageLayout::setTitle(_('Gemerkte Anzeigen'));
    }

    public function index_action()
    {
        $articles = SBUser::get()->watched_articles;
        $this->categories = SBArticle::groupByCategory($articles);
    }

    public function add_action($article_id)
    {
        $entry = new SBWatchlist([$GLOBALS['user']->id, $article_id]);
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

        $entry = SBWatchlist::deleteBySQL('user_id = ? AND artikel_id IN (?)', [
            $GLOBALS['user']->id,
            (array)$article_id
        ]);

        $this->flash['send_headers'] = ['X-Article-Unwatched', $article_id];
        $this->redirect($bulk ? 'watchlist' : ('article/view/' . $article_id));
    }
}
