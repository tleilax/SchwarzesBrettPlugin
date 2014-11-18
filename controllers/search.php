<?php
class SearchController extends SchwarzesBrettController
{
    public function index_action()
    {
        Navigation::activateItem('/schwarzesbrettplugin/show/all');

        $needle   = trim(Request::get('needle'));
        $articles = SBArticle::search($needle);

        $this->needle     = $needle;
        $this->categories = SBArticle::groupByCategory($articles);
    }
}
