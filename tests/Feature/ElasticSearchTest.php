
<?php

use IO\Tests\TestCase;
use Plenty\Modules\Cloud\ElasticSearch\Factories\ElasticSearchResultFactory;
use Plenty\Modules\Item\Search\Index\Mapping;

/**
 * Created by PhpStorm.
 * User: lukasmatzen
 * Date: 02.11.18
 * Time: 15:18
 */

class ElasticSearchTest extends TestCase
{
    /** @var ElasticSearchResultFactory $esFactory */
    protected $esFactory;

    protected function setUp()
    {
        parent::setUp();
    }

    /** @test */
    public function it_runs_es_mapping()
    {
        $this->esFactory = pluginApp(ElasticSearchResultFactory::class);
        $this->esFactory->getElasticSearchResult();
    }
}