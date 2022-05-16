<?php
namespace SchwarzesBrett;

final class DomainBlacklist extends \SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'sb_domain_blacklist';

        $config['belongs_to'] = [
            'domain' => [
                'class_name'  => \UserDomain::class,
                'foreign_key' => 'userdomain_id',
            ]
        ];

        $config['additional_fields'] = [
            'name' => [
                'get' => function (DomainBlacklist $domain) {
                    return $domain->domain->name;
                },
            ],
        ];

        parent::configure($config);
    }

    public static function isUserBlacklisted(?\User $user): bool
    {
        if ($user === null) {
            return true;
        }

        if ($user->perms === 'root') {
            return false;
        }

        $blacklisted_domains = self::findBySQL('1');

        if (count($blacklisted_domains) === 0) {
            return false;
        }

        foreach ($blacklisted_domains as $domain) {
            $userdomain = $user->domains->findOneBy('id', $domain->id);
            if ($userdomain && $userdomain->restricted_access) {
                return true;
            }
        }

        return false;
    }
}
