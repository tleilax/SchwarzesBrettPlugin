<?php
class WatchlistController extends SchwarzesBrettController
{
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        
        Navigation::activateItem('/schwarzesbrettplugin/show/watchlist');
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
    }

    public function remove_action($article_id)
    {
        $entry = new SBWatchlist([$GLOBALS['user']->id, $article_id]);
        $entry->delete();
    }
}
