<?php

namespace IO\Tests\Services;

use IO\Services\SessionStorageService;
use IO\Services\TagService;
use IO\Tests\TestCase;
use Plenty\Modules\Authorization\Services\AuthHelper;
use Plenty\Modules\Tag\Models\Tag;
use Mockery;
use Plenty\Modules\Tag\Models\TagName;

class TagServiceTest extends TestCase
{
    /** @var TagService */
    private $tagService;

    /** @var AuthHelper */
    private $authHelperMock;

    /** @var SessionStorageService */
    private $sessionStorageMock;

    const TAG_ID = 42;
    const TAG_NAME_DE = "Test Tag DE";
    const TAG_NAME_EN = "Test Tag EN";

    protected function setUp()
    {
        parent::setUp();
        $this->tagService = pluginApp(TagService::class);

        $this->authHelperMock = Mockery::mock(AuthHelper::class);
        app()->instance(AuthHelper::class, $this->authHelperMock);

        $this->sessionStorageMock = Mockery::mock(SessionStorageService::class);
        app()->instance(SessionStorageService::class, $this->sessionStorageMock);
    }

    /** @test */
    public function it_gets_a_tag_by_id()
    {
        $this->authHelperMock
            ->shouldReceive("processUnguarded")
            ->andReturn(factory(Tag::class)->make([
                "id"        => self::TAG_ID
            ]));

        $tag = $this->tagService->getTagById(self::TAG_ID);

        $this->assertNotNull($tag);
        $this->assertInstanceOf(Tag::class, $tag);
        $this->assertEquals(self::TAG_ID, $tag->id);
    }

    /** @test */
    public function it_gets_the_legacy_name_of_a_tag()
    {
        $tagName = "Test Tag";

        $this->authHelperMock
            ->shouldReceive("processUnguarded")
            ->andReturn(factory(Tag::class)->make([
                "id"        => self::TAG_ID,
                "tagName"   => $tagName
            ]));

        $this->sessionStorageMock->shouldReceive("getLang");

        $this->assertEquals($tagName, $this->tagService->getTagName(self::TAG_ID));
    }

    /** @test */
    public function it_gets_the_name_of_a_tag_in_the_default_language()
    {
        $this->authHelperMock
            ->shouldReceive("processUnguarded")
            ->andReturn(factory(Tag::class)->make([
                "id"        => self::TAG_ID,
                "names"     => collect([
                    factory(TagName::class)->make([
                        "tagLang" => "de",
                        "tagName" => self::TAG_NAME_DE
                    ]),
                    factory(TagName::class)->make([
                        "tagLang" => "en",
                        "tagName" => self::TAG_NAME_EN
                    ])
                ]),
            ]));

        $this->sessionStorageMock
            ->shouldReceive("getLang")
            ->andReturn("de");

        $tagName = $this->tagService->getTagName(self::TAG_ID);

        $this->assertEquals(self::TAG_NAME_DE, $tagName);
    }

    /** @test */
    public function it_gets_the_name_of_a_tag_in_a_language()
    {
        $this->authHelperMock
            ->shouldReceive("processUnguarded")
            ->andReturn(factory(Tag::class)->make([
                "id"        => self::TAG_ID,
                "names"     => collect([
                    factory(TagName::class)->make([
                        "tagLang" => "de",
                        "tagName" => self::TAG_NAME_DE
                    ]),
                    factory(TagName::class)->make([
                        "tagLang" => "en",
                        "tagName" => self::TAG_NAME_EN
                    ])
                ]),
            ]));

        $this->sessionStorageMock->shouldNotReceive("getLang");

        $tagName = $this->tagService->getTagName(self::TAG_ID, "en");

        $this->assertEquals(self::TAG_NAME_EN, $tagName);
    }

    /** @test */
    public function it_gets_the_legacy_name_as_fallback()
    {
        $tagName = "Test Tag";
        $this->authHelperMock
            ->shouldReceive("processUnguarded")
            ->andReturn(factory(Tag::class)->make([
                "id"        => self::TAG_ID,
                "tagName"   => $tagName,
                "names"     => collect([
                    factory(TagName::class)->make([
                        "tagLang" => "de",
                        "tagName" => self::TAG_NAME_DE
                    ]),
                    factory(TagName::class)->make([
                        "tagLang" => "en",
                        "tagName" => self::TAG_NAME_EN
                    ])
                ]),
            ]));

        $this->sessionStorageMock->shouldNotReceive("getLang");

        $this->assertEquals($tagName, $this->tagService->getTagName(self::TAG_ID, "fr"));
    }

    /** @test */
    public function it_returns_empty_string_if_tag_does_not_exist()
    {
        $this->authHelperMock
            ->shouldReceive("processUnguarded")
            ->andReturn(null);

        $this->sessionStorageMock->shouldReceive("getLang");

        $this->assertEquals("", $this->tagService->getTagName(self::TAG_ID));
    }
}