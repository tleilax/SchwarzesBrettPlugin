<?php
class Admin_SettingsController extends SchwarzesBrettController
{
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        $GLOBALS['perm']->check('root');
    }

    public function index_action()
    {
        Navigation::activateItem('/schwarzesbrettplugin/root/settings');

        $this->options            = $this->getOptions();
        $this->visible_for_nobody = $this->getVisibility('Nobody');
    }

    public function store_action()
    {
        CSRFProtection::verifyUnsafeRequest();

        if (($ticket = Request::get('studip_ticket')) && check_ticket($ticket)) {
            $config  = Config::get();
            $options = $this->getOptions();
            
            foreach ($options as $key => $option) {
                if (in_array($option['type'], words('number checkbox'))) {
                    $value = Request::int($option['key']);
                } else {
                    $value = Request::get($option['key']);
                }

                $config->store($key, $value);
            }
            
            PageLayout::postMessage(MessageBox::success(_('Die Einstellungen wurden gespeichert.')));
        }

        $this->redirect('admin/settings');
    }

    protected function getConfig($key, $type)
    {
        static $config = null;
        if ($config === null) {
            $config = Config::getInstance();
        }

        if ($type === 'value') {
            return $config[$key];
        }

        $field = $config->getMetadata($key);
        return $field[$type];
    }

    protected function getOptions()
    {
        $options = array();

        $options['BULLETIN_BOARD_DURATION'] = array(
            'key'  => 'duration',
            'type' => 'number',
        );

        $options['BULLETIN_BOARD_DISPLAY_BADGE'] = array(
            'key'  => 'displayBadge',
            'type' => 'checkbox',
        );

        $options['BULLETIN_BOARD_ANNOUNCEMENTS'] = array(
            'key'  => 'announcements',
            'type' => 'number',
        );

        $options['BULLETIN_BOARD_ENABLE_BLAME'] = array(
            'key'       => 'enableBlame',
            'type'      => 'checkbox',
            'activates' => 'BULLETIN_BOARD_BLAME_RECIPIENTS',
        );

        $options['BULLETIN_BOARD_BLAME_RECIPIENTS'] = array(
            'key'  => 'blameRecipients',
            'type' => 'text',
        );

        $options['BULLETIN_BOARD_BAD_WORDS'] = array(
            'key'  => 'badWords',
            'type' => 'text',
        );

        $options['BULLETIN_BOARD_ENABLE_RSS'] = array(
            'key'  => 'enableRss',
            'type' => 'checkbox',
        );

        $options['BULLETIN_BOARD_MEDIA_PROXY'] = array(
            'key'  => 'enableMediaProxy',
            'type' => 'checkbox',
        );
        $options['BULLETIN_BOARD_MEDIA_PROXY_CACHED'] = array(
            'key'  => 'cacheMediaProxy',
            'type' => 'checkbox',
        );

        $options['BULLETIN_BOARD_RULES'] = array(
            'key'  => 'rules',
            'type' => 'textarea',
        );
        
        foreach ($options as $key => $data) {
            $options[$key]['description'] = $this->getConfig($key, 'description');
            $options[$key]['value']       = $this->getConfig($key, 'value');
        }

        return $options;
    }
    
    protected function getVisibility($requested_role)
    {
        $plugin_id = $this->dispatcher->plugin->getPluginId();
        
        $role_persistence = new RolePersistence();
        $plugin_roles     = $role_persistence->getAssignedPluginRoles($plugin_id);
        $role_test        = array_filter($plugin_roles, function ($role) use ($requested_role) {
            return $role->getRolename() === $requested_role;
        });
        
        return count($role_test) > 0;
    }
}
