<?php
use SchwarzesBrett\Article;

class SearchController extends SchwarzesBrett\Controller
{
    public function index_action()
    {
        Navigation::activateItem('/schwarzesbrettplugin/show/all');

        $needle   = trim(Request::get('needle'));
        if (strlen($needle) >= 3) {
            $articles = Article::search($needle);
        } else {
            $articles = array();
        }

        $this->needle     = $needle;
        $this->categories = Article::groupByCategory($articles);
    }
}
