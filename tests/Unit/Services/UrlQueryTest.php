<?php

namespace IO\Tests\Services;

use Mockery;
use Plenty\Modules\Webshop\Helpers\UrlQuery;
use IO\Tests\TestCase;
use Plenty\Plugin\ConfigRepository;

class UrlQueryTest extends TestCase
{
    /** @var ConfigRepository $configRepoMock */
    private $configRepoMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->configRepoMock = Mockery::mock(ConfigRepository::class);
        $this->replaceInstanceByMock(ConfigRepository::class, $this->configRepoMock);
    }

    /**
     * @test
     * @dataProvider getUrlQueries
     */
    public function it_should_join_url_parts($path1, $path2)
    {
        $this->configRepoMock->shouldReceive('get')->andReturn(0);

        /** @var UrlQuery $urlQuery */
        $urlQuery = pluginApp(UrlQuery::class, ['path' => $path1]);
        $urlQuery->join($path2);
        $expected = '/' . str_replace('/', '', $path1) . '/' . str_replace('/', '', $path2);
            $this->assertEquals($expected, $urlQuery->toRelativeUrl());
        $this->assertStringNotContainsString('//', $urlQuery->toRelativeUrl());
    }

    /**
     * @test
     * @dataProvider getUrlQueries
     */
    public function it_should_append_trailing_slash($path)
    {
        $this->configRepoMock->shouldReceive('get')->andReturn(2);

        /** @var UrlQuery $urlQuery */
        $urlQuery = pluginApp(UrlQuery::class, ['path' => $path]);
        $url = $urlQuery->toRelativeUrl();
        $this->assertEquals('/', substr($url, strlen($url) - 1, 1));
    }

    /**
     * @test
     * @dataProvider getUrlQueries
     */
    public function it_should_remove_trailing_slash($path)
    {
        $this->configRepoMock->shouldReceive('get')->andReturn(1);

        /** @var UrlQuery $urlQuery */
        $urlQuery = pluginApp(UrlQuery::class, ['path' => $path]);
        $url = $urlQuery->toRelativeUrl();
        $this->assertNotEquals('/', substr($url, strlen($url) - 1, 1));
    }

    /**
     * @test
     * @dataProvider getUrlQueries
     */
    public function it_should_include_language($path)
    {
        $this->configRepoMock->shouldReceive('get');

        foreach (['de', 'en', 'fr'] as $lang) {
            /** @var UrlQuery $urlQuery */
            $urlQuery = pluginApp(UrlQuery::class, ['path' => $path, 'lang' => $lang]);
            $url = $urlQuery->toRelativeUrl(true);
            $this->assertEquals('/'.$lang.'/', substr($url, 0, strlen($lang) + 2));

            /** @var UrlQuery $urlQuery */
            $urlQuery = pluginApp(UrlQuery::class, ['path' => $lang, 'lang' => $lang]);
            $url = $urlQuery->join($path)->toRelativeUrl(true);
            $this->assertEquals('/'.$lang.'/', substr($url, 0, strlen($lang) + 2));
            $this->assertNotEquals('/'.$lang.'/'.$lang.'/', substr($url, 0, 2 * strlen($lang) + 3));
        }
    }

    /**
     * @test
     * @dataProvider getUrlQueries
     */
    public function it_should_append_trailing_slash_before_query_string($path1, $path2, $queryString)
    {
        $this->configRepoMock->shouldReceive('get')->andReturn(2);

        /** @var UrlQuery $urlQuery */
        $urlQuery = pluginApp(UrlQuery::class, ['path' => $path1.$queryString]);
        $url = $urlQuery->toRelativeUrl();
        $this->assertNotEquals('/', substr($url, strlen($url) - 1, 1));
        $this->assertEquals('/', substr($url, strlen($url) - strlen($queryString) - 1, 1));
    }

    /**
     * @test
     * @dataProvider getUrlQueries
     */
    public function it_should_remove_trailing_slash_before_query_string($path1, $path2, $queryString)
    {
        $this->configRepoMock->shouldReceive('get')->andReturn(1);

        /** @var UrlQuery $urlQuery */
        $urlQuery = pluginApp(UrlQuery::class, ['path' => $path1.$queryString]);
        $url = $urlQuery->toRelativeUrl();
        $this->assertNotEquals('/', substr($url, strlen($url) - 1, 1));
        $this->assertNotEquals('/', substr($url, strlen($url) - strlen($queryString) - 1, 1));
    }

    public function getUrlQueries()
    {
        $paths = ['%s', '%s/', '/%s', '/%s/'];

        $queryStrings = [
            '?a=42&b=test'
        ];

        $data = [];
        foreach($paths as $path1)
        {
            foreach($paths as $path2)
            {
                foreach($queryStrings as $queryString)
                {
                    $data[] = [sprintf($path1, 'foo'), sprintf($path2, 'bar'), $queryString];
                }
            }
        }

        return $data;
    }
}
