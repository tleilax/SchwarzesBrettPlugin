<?php
class Admin_DuplicatesController extends SchwarzesBrettController
{
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        $GLOBALS['perm']->check('root');
    }

    public function index_action()
    {
        Navigation::activateItem('/schwarzesbrettplugin/root/duplicates');

        $this->duplicates = SBArticle::findDuplicates();
    }
}
