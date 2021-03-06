<?php
namespace SchwarzesBrett;

use NotificationCenter;
use PageLayout;
use StudIPPlugin;

abstract class Plugin extends StudIPPlugin
{
    const GETTEXT_DOMAIN = 'schwarzes-brett';

    public function __construct()
    {
        parent::__construct();

        bindtextdomain(static::GETTEXT_DOMAIN, $this->getPluginPath() . '/locale');
        bind_textdomain_codeset(static::GETTEXT_DOMAIN, 'UTF-8');
    }

    /**
     * Plugin localization for a single string.
     * This method supports sprintf()-like execution if you pass additional
     * parameters.
     *
     * @param String $string String to translate
     * @return string
     */
    public function _($string)
    {
        $result = dgettext(static::GETTEXT_DOMAIN, $string);

        if ($result === $string) {
            $result = _($string);
        }

        return $result;
    }

    /**
     * Plugin localization for plural strings.
     * This method supports sprintf()-like execution if you pass additional
     * parameters.
     *
     * @param String $string0 String to translate (singular)
     * @param String $string1 String to translate (plural)
     * @param mixed  $n       Quantity factor (may be an array or array-like)
     * @return string
     */
    public function _n($string0, $string1, $n)
    {
        if (is_array($n)) {
            $n = count($n);
        }

        $result = dngettext(static::GETTEXT_DOMAIN, $string0, $string1, $n);

        if ($result === $string0 || $result === $string1) {
            $result = ngettext($string0, $string1, $n);
        }

        return $result;
    }
}
