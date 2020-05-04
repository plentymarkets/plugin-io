<?php

namespace IO\Services\ItemSearch\Factories\Faker;

use IO\Helper\Utils;
use IO\Services\ItemSearch\Factories\Faker\Traits\FakeBoolean;
use IO\Services\ItemSearch\Factories\Faker\Traits\FakeCountry;
use IO\Services\ItemSearch\Factories\Faker\Traits\FakeDate;
use IO\Services\ItemSearch\Factories\Faker\Traits\FakeImage;
use IO\Services\ItemSearch\Factories\Faker\Traits\FakeLanguage;
use IO\Services\ItemSearch\Factories\Faker\Traits\FakeNumber;
use IO\Services\ItemSearch\Factories\Faker\Traits\FakeString;
use IO\Services\ItemSearch\Factories\Faker\Traits\FakeUnit;
use IO\Services\ItemSearch\Factories\Faker\Traits\HandleNestedArray;
use Plenty\Plugin\Application;

abstract class AbstractFaker
{
    use HandleNestedArray, FakeNumber, FakeString, FakeDate, FakeBoolean, FakeLanguage, FakeImage, FakeCountry, FakeUnit;

    const ES_LANGUAGES = [
        'de' => 'german',
        'en' => 'english',
        'fr' => 'french',
        'bg' => 'bulgarian',
        'it' => 'italian',
        'es' => 'spanish',
        'tr' => 'turkish',
        'nl' => 'dutch',
        'pt' => 'portuguese',
        'nn' => 'norwegian',
        'ro' => 'romanian',
        'da' => 'danish',
        'se' => 'swedish',
        'cz' => 'czech',
        'ru' => 'russian',
    ];

    public $isList = false;
    public $range = [1,2];
    public $index = 0;
    public $list = [];

    protected $lang;
    protected $esLang;
    protected $plentyId;

    private static $globals = [];

    public function __construct(Application $app)
    {
        $this->lang     = Utils::getLang();
        $this->esLang   = self::ES_LANGUAGES[$this->lang] ?? 'english';
        $this->plentyId = $app->getPlentyId();
    }

    public abstract function fill($data);

    protected function rand($values)
    {
        $index = $this->number(0, count($values)-1);
        return $values[$index];
    }

    protected function global($key, $value)
    {
        if ( !array_key_exists($key, self::$globals ) )
        {
            self::$globals[$key] = $value;
        }

        return self::$globals[$key];
    }

    public static function resetGlobals()
    {
        self::$globals = [];
    }
}
