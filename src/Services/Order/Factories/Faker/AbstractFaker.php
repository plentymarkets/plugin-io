<?php

namespace IO\Services\Order\Factories\Faker;

use IO\Services\ItemSearch\Factories\Faker\Traits\FakeBoolean;
use IO\Services\ItemSearch\Factories\Faker\Traits\FakeCountry;
use IO\Services\ItemSearch\Factories\Faker\Traits\FakeDate;
use IO\Services\ItemSearch\Factories\Faker\Traits\FakeImage;
use IO\Services\ItemSearch\Factories\Faker\Traits\FakeLanguage;
use IO\Services\ItemSearch\Factories\Faker\Traits\FakeNumber;
use IO\Services\ItemSearch\Factories\Faker\Traits\FakeString;
use IO\Services\ItemSearch\Factories\Faker\Traits\FakeUnit;
use IO\Services\ItemSearch\Factories\Faker\Traits\HandleNestedArray;
use IO\Services\SessionStorageService;
use Plenty\Plugin\Application;

abstract class AbstractFaker
{
    use HandleNestedArray, FakeNumber, FakeString, FakeDate, FakeBoolean, FakeLanguage, FakeImage, FakeCountry, FakeUnit;

    public $isList = false;
    public $range = [1,2];
    public $index = 0;
    public $list = [];

    protected $lang;
    protected $plentyId;

    private static $globals = [];

    public function __construct(SessionStorageService $sessionStorageService, Application $app)
    {
        $this->lang     = $sessionStorageService->getLang();
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
