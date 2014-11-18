<?php
class SchwarzesBrettWidget extends StudIPPlugin implements PortalPlugin
{
    public function initialize()
    {
        if (Request::isXhr()) {
            Header('Content-Type: text/html;charset=windows-1252');
        }
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
        PageLayout::setTitle(_('Einstellung für das Schwarze Brett Widget'));
        
        if (Request::isPost()) {
            $selection = Request::getArray('categories');
            $this->storeSelection($selection);
            
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
        
        $template = $this->getTemplate('widget-settings.php');
        $template->url        = PluginEngine::getLink($this, array(), 'settings');
        $template->categories = SBCategory::findBySQL('1 ORDER BY titel COLLATE latin1_german1_ci ASC');
        $template->selected   = $this->getSelection();
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
        $template = $this->getTemplate('widget.php');
        $template->categories = $this->getSelection(true);
        return $template->render();
    }

    protected function getTemplate($template, $layout = false)
    {
        $factory  = new Flexi_TemplateFactory(__DIR__ . '/views');
        $template = $factory->open($template);
        if ($layout && !Request::isXhr()) {
            $template->set_layout($GLOBALS['template_factory']->open('layout/base.php'));
        }
        return $template;
    }
    
    protected function getSelection($as_objects = false)
    {
        $selection = isset($GLOBALS['user']->cfg->SCHWARZESBRETT_WIDGET_SELECTION)
                   ? unserialize($GLOBALS['user']->cfg->SCHWARZESBRETT_WIDGET_SELECTION)
                   : false;
        if (!$as_objects) {
            return $selection;
        }
        
        return $selection === false
             ? SBCategory::findBySQL('1 ORDER BY titel COLLATE latin1_german1_ci ASC')
             : SBCategory::findMany($selection);
    }
    
    protected function storeSelection($selection)
    {
        $GLOBALS['user']->cfg->store('SCHWARZESBRETT_WIDGET_SELECTION', serialize($selection));
    }
}