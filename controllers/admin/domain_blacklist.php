<?php
final class Admin_DomainBlacklistController extends SchwarzesBrett\Controller
{
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        PageLayout::setTitle($this->_('Domänen-Blacklist'));
        Navigation::activateItem('/schwarzesbrettplugin/root/domain_blacklist');

        $actions = new ActionsWidget();
        $actions->addLink(
            $this->_('Nutzerdomäne sperren'),
            $this->addURL(),
            Icon::create('add')
        )->asDialog('size=auto');
        Sidebar::get()->addWidget($actions);
    }

    public function index_action()
    {
        $this->blacklisted_domains = SchwarzesBrett\DomainBlacklist::findBySQL('1');
    }

    public function add_action()
    {
        $this->domains = UserDomain::findBySQL('1 ORDER BY name');
        $this->blacklisted_domains = SchwarzesBrett\DomainBlacklist::findAndMapBySQL(
            function (SchwarzesBrett\DomainBlacklist $domain) {
                return $domain->id;
            },
            '1'
        );
    }

    public function store_action()
    {
        CSRFProtection::verifyUnsafeRequest();

        $added = 0;
        foreach (Request::getArray('domain_ids') as $id) {
            $blacklist = new SchwarzesBrett\DomainBlacklist($id);
            $added += $blacklist->store();
        }

        if ($added > 0) {
            $message = $added === 1
                     ? $this->_('Die Nutzerdomäne wurde gesperrt.')
                     : sprintf(
                         $this->_('%u Nutzerdomänen wurden gesperrt.'),
                         $added
                     );
            PageLayout::postSuccess($message);
        }

        $this->redirect($this->indexURL());
    }

    public function delete_action(SchwarzesBrett\DomainBlacklist $domain = null)
    {
        CSRFProtection::verifyUnsafeRequest();

        $deleted = 0;
        if ($domain->isNew()) {
            SchwarzesBrett\DomainBlacklist::findEachMany(
                function (SchwarzesBrett\DomainBlacklist $domain) use (&$deleted) {
                    $deleted += $domain->delete();
                },
                Request::getArray('ids')
            );
        } else {
            $deleted = $domain->delete();
        }

        if ($deleted > 0) {
            $message = $deleted === 1
                     ? $this->_('Die Nutzerdomäne wurde entsperrt.')
                     : sprintf(
                         $this->_('%u Nutzerdomänen wurden entsperrt.'),
                         $deleted
                     );
            PageLayout::postSuccess($message);
        }

        $this->relocate($this->indexURL());
    }
}
