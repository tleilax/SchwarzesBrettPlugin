<?php
class ConfigController extends SchwarzesBrett\Controller
{
    public function toggle_badge_action()
    {
        $user_config = $GLOBALS['user']->cfg;
        $user_config->store('BULLETIN_BOARD_SHOW_BADGE', !$user_config->BULLETIN_BOARD_SHOW_BADGE);

        $this->redirect('category/view');
    }
}
