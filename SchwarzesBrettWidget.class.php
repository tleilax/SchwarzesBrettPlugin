<?php
class SchwarzesBrettWidget extends StudIPPlugin implements PortalPlugin
{
    public function __construct()
    {
        parent::__construct();
        
        if (Request::isXhr()) {
            Header('Content-Type: text/html;charset=windows-1252');
        }

        $this->addStylesheet('assets/schwarzesbrett.less');
        PageLayout::addScript($this->getPluginURL() . '/assets/schwarzesbrett.js');
    }
    
    public function getPluginName()
    {
        return _('Schwarzes Brett');
    }

    public function getPortalTemplate()
    {
        $widget = $GLOBALS['template_factory']->open('shared/string');
        $widget->content = $this->getContent();
        $widget->icons   = $this->getNavigation();
        $widget->title   = $this->getPluginName();
        return $widget;
    }

    public function settings_action()
    {
        PageLayout::setTitle(_('Einstellung f�r das Schwarze Brett Widget'));
        
        if (Request::isPost()) {
            $selection = Request::getArray('categories');
            $count     = Request::int('count');
            $this->storeConfig($selection, $count);
            
            if (Request::isXhr()) {
                header('X-Location: ' . URLHelper::getLink('dispatch.php/start'));
            } else {
                header('Location: ' . URLHelper::getLink('dispatch.php/start'));
            }
            return;
        }
        
        if (Request::isXhr()) {
            header('X-Title: ' . PageLayout::getTitle());
        }
        
        $template = $this->getTemplate('widget/settings.php', true);
        $template->url        = PluginEngine::getLink($this, array(), 'settings');
        $template->categories = SBCategory::findBySQL('1 ORDER BY titel COLLATE latin1_german1_ci ASC');
        $template->config   = $this->getConfig();
        echo $template->render();
    }

    protected function getNavigation()
    {
        $navigation = array();

        $nav = new Navigation('', PluginEngine::getLink($this, array(), 'settings'));
        $nav->setImage('icons/16/blue/admin.png', tooltip2(_('Einstellungen')) + array('data-dialog' => ''));
        $navigation[] = $nav;

        return $navigation;
    }

    protected function getContent()
    {
        $config = $this->getConfig();

        $template = $this->getTemplate('widget/index.php');
        $template->articles = SBArticle::findNewest($config['count'], $config['selected']);
        return $template->render();
    }

    protected function getTemplate($template, $layout = false)
    {
        $factory  = new Flexi_TemplateFactory(__DIR__ . '/views');
        $template = $factory->open($template);
        $template->controller = PluginEngine::getPlugin('SchwarzesBrettPlugin');
        if ($layout && !Request::isXhr()) {
            $template->set_layout($GLOBALS['template_factory']->open('layouts/base.php'));
        }
        return $template;
    }
    
    protected function getConfig()
    {
        return isset($GLOBALS['user']->cfg->SCHWARZESBRETT_WIDGET_SETTINGS)
             ? unserialize($GLOBALS['user']->cfg->SCHWARZESBRETT_WIDGET_SETTINGS)
             : array('selected' => false, 'count' => Config::get()->ENTRIES_PER_PAGE);
    }

    protected function storeConfig($selected, $count)
    {
        $GLOBALS['user']->cfg->store('SCHWARZESBRETT_WIDGET_SETTINGS', serialize(compact(words('selected count'))));
    }
}