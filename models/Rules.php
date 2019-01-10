<?php
namespace SchwarzesBrett;

use Config;
use I18NString;

class Rules
{
    public static function store(I18NString $value)
    {
        $object = new self($value->original(), $value->toArray());
        $object->getContent()->storeTranslations();
    }

    public static function get($base)
    {
        $object = new self($base);
        return $object->getContent();
    }

    private $i18n;

    public function __construct($base, $lang = null)
    {
        $this->i18n = new I18NString($base, $lang, [
            'object_id' => 'rules',
            'table'     => 'config',
            'field'     => 'bulletinboard',
        ]);
    }

    public function getContent()
    {
        return $this->i18n;
    }
}
