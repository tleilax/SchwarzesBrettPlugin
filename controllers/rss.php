<?php
use SchwarzesBrett\Article;
use SchwarzesBrett\Category;

class RssController extends SchwarzesBrett\Controller
{
    protected $allow_nobody = true;

    public function before_filter(&$action, &$args)
    {
        if (!method_exists($this, $action . '_action')) {
            array_unshift($args, $action);
            $action = 'index';
        }

        parent::before_filter($action, $args);

        if (!$this->rss_enabled) {
            throw new Exception($this->_('RSS-Exporte sind nicht aktiviert.'));
        }

        $this->set_layout(null);
    }

    public function index_action($category_id = null, $limit = 50)
    {
        $this->articles = Article::findPublishable($category_id);
        $this->articles = array_slice($this->articles, 0, $limit);

        $this->title       = $this->_('Stud.IP Schwarzes Brett');
        $this->description = '';
        $this->link        = $this->url_for('category');
        if ($category_id !== null) {
            $category = Category::find($category_id);
            $this->title       .= ' - ' . $category->titel;
            $this->description  = $category->beschreibung;
            $this->link         = $this->absolute_url_for('category/' . $category->id);
        }

        $this->set_content_type('application/rss+xml;charset=utf-8');
    }
}
