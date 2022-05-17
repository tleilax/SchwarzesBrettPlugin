<?php
namespace SchwarzesBrett;

/**
 * @property string $id
 * @property string $userdomain_id
 * @property string $restriction
 * @property int    $mkdate
 */
final class DomainBlacklist extends \SimpleORMap
{
    const RESTRICTION_COMPLETE = 'complete';
    const RESTRICTION_USAGE = 'usage';

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

    public static function isUserBlacklisted(?\User $user, string $restriction = self::RESTRICTION_COMPLETE): bool
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

        $matched = false;
        foreach ($blacklisted_domains as $domain) {
            $userdomain = $user->domains->findOneBy('id', $domain->id);
            if (!$userdomain) {
                continue;
            }

            if (!$userdomain->restricted_access) {
                return false;
            }

            if ($domain->restriction === self::RESTRICTION_COMPLETE) {
                $matched = true;
            } else {
                $matched = $domain->restriction === $restriction;
            }

        }

        return $matched;
    }
}
