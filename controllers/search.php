<?php
class SearchController extends SchwarzesBrettController
{
    public function index_action()
    {
        Navigation::activateItem('/schwarzesbrettplugin/show/all');

        $needle   = trim(Request::get('needle'));
        if (strlen($needle) >= 3) {
            $articles = SBArticle::search($needle);
        } else {
            $articles = array();
        }

        $this->needle     = $needle;
        $this->categories = SBArticle::groupByCategory($articles);
    }
}
