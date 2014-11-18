<?php
class RssController extends SchwarzesBrettController
{
    public function before_filter(&$action, &$args)
    {
        if (!method_exists($this, $action . '_action')) {
            array_unshift($args, $action);
            $action = 'index';
        }

        parent::before_filter($action, $args);

        if (!$this->rss_enabled) {
            throw new Exception(_('RSS-Exporte sind nicht aktiviert.'));
        }

        $this->set_layout(null);
    }

    public function index_action($category_id = null)
    {
        $this->articles = SBArticle::findPublishable($category_id);

        $this->title       = _('Stud.IP Schwarzes Brett');
        $this->description = '';
        $this->link        = $this->url_for('category');
        if ($category_id !== null) {
            $category = SBCategory::find($category_id);
            $this->title       .= ' - ' . $category->titel;
            $this->description  = $category->beschreibung;
            $this->link         = $this->absolute_url_for('category/' . $category->id);
        }

        $this->response->add_header('Content-Type', 'application/rss+xml;charset=utf-8');
    }
}
