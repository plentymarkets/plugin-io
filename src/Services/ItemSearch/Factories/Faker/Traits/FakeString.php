<?php

namespace IO\Services\ItemSearch\Factories\Faker\Traits;

use Plenty\Plugin\Translation\Translator;

trait FakeString
{
    static $translationCounter = [];

    protected function text($minWords = 1, $maxWords = 100)
    {
        $wordCount = rand($minWords, $maxWords);
        $loremIpsum = explode(" ", FakeConstants::LOREM_IPSUM);
        $words = $loremIpsum;
        while(count($words) < $wordCount)
        {
            $words = array_merge($words, $loremIpsum);
        }

        $words = array_slice($words, 0, $wordCount);
        return implode(" ", $words);
    }

    protected function word()
    {
        $words = explode(" ", FakeConstants::LOREM_IPSUM);
        $index = rand(0, count($words));
        return $words[$index];
    }

    protected function trans($key, $params = [])
    {
        /** @var Translator $translator */
        $translator = pluginApp(Translator::class);

        $count = self::$translationCounter[$key] ?? 1;
        $params['count'] = $count;
        $result = $translator->trans($key, $params);

        self::$translationCounter[$key] = $count + 1;
        return $result;
    }

    protected function serial()
    {
        $tmp = strtoupper(uniqid());
        return substr($tmp, 0, rand(1,3)) . "-" . substr($tmp, rand(3,5), rand(1,3)) . "-" . rand(0, 1000);
    }

    protected function hash()
    {
        return sha1(microtime(true));
    }

    protected function url()
    {
        $depth = rand(1, 5);
        $path = [];
        for($i = 0; $i < $depth; $i++)
        {
            $path[] = $this->word();
        }

        return "/" . implode("/", $path);
    }

    protected function hexColor()
    {
        return "#".substr(md5(rand()), 0, 6);
    }
}
