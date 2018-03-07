<?php
use SchwarzesBrett\Article;

class Admin_DuplicatesController extends SchwarzesBrett\Controller
{
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        $GLOBALS['perm']->check('root');
    }

    public function index_action()
    {
        PageLayout::setTitle("{$this->_('Schwarzes Brett')} - {$this->_('Doppelte Einträge')}");
        Navigation::activateItem('/schwarzesbrettplugin/root/duplicates');

        $this->duplicates = Article::findDuplicates();
    }
}
