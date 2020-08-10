<?php

namespace IO\Tests\Services;

use IO\Services\TagService;
use IO\Tests\TestCase;
use Plenty\Modules\Authorization\Services\AuthHelper;
use Plenty\Modules\Tag\Models\Tag;
use Mockery;
use Plenty\Modules\Tag\Models\TagName;
use Plenty\Modules\Webshop\Contracts\LocalizationRepositoryContract;
use Plenty\Modules\Webshop\Contracts\SessionStorageRepositoryContract;

class TagServiceTest extends TestCase
{
    /** @var TagService */
    private $tagService;

    /** @var AuthHelper */
    private $authHelperMock;

    /** @var LocalizationRepositoryContract */
    private $localizationRepositoryMock;

    const TAG_ID = 42;
    const TAG_NAME_DE = "Test Tag DE";
    const TAG_NAME_EN = "Test Tag EN";

    protected function setUp(): void
    {
        parent::setUp();
        $this->tagService = pluginApp(TagService::class);

        $this->authHelperMock = Mockery::mock(AuthHelper::class);
        app()->instance(AuthHelper::class, $this->authHelperMock);

        $this->localizationRepositoryMock = Mockery::mock(SessionStorageRepositoryContract::class);
        app()->instance(LocalizationRepositoryContract::class, $this->localizationRepositoryMock);
    }

    /** @test */
    public function it_gets_a_tag_by_id()
    {
        $this->authHelperMock
            ->shouldReceive("processUnguarded")
            ->andReturn(
                factory(Tag::class)->make(
                    [
                        "id" => self::TAG_ID
                    ]
                )
            );

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
            ->andReturn(
                factory(Tag::class)->make(
                    [
                        "id" => self::TAG_ID,
                        "tagName" => $tagName
                    ]
                )
            );

        $this->localizationRepositoryMock->shouldReceive("getLanguage");

        $this->assertEquals($tagName, $this->tagService->getTagName(self::TAG_ID));
    }

    /** @test */
    public function it_gets_the_name_of_a_tag_in_the_default_language()
    {
        $this->authHelperMock
            ->shouldReceive("processUnguarded")
            ->andReturn(
                factory(Tag::class)->make(
                    [
                        "id" => self::TAG_ID,
                        "names" => collect(
                            [
                                factory(TagName::class)->make(
                                    [
                                        "tagLang" => "de",
                                        "tagName" => self::TAG_NAME_DE
                                    ]
                                ),
                                factory(TagName::class)->make(
                                    [
                                        "tagLang" => "en",
                                        "tagName" => self::TAG_NAME_EN
                                    ]
                                )
                            ]
                        ),
                    ]
                )
            );

        $this->localizationRepositoryMock->shouldReceive("getLanguage")->andReturn("de");

        $tagName = $this->tagService->getTagName(self::TAG_ID);

        $this->assertEquals(self::TAG_NAME_DE, $tagName);
    }

    /** @test */
    public function it_gets_the_name_of_a_tag_in_a_language()
    {
        $this->authHelperMock
            ->shouldReceive("processUnguarded")
            ->andReturn(
                factory(Tag::class)->make(
                    [
                        "id" => self::TAG_ID,
                        "names" => collect(
                            [
                                factory(TagName::class)->make(
                                    [
                                        "tagLang" => "de",
                                        "tagName" => self::TAG_NAME_DE
                                    ]
                                ),
                                factory(TagName::class)->make(
                                    [
                                        "tagLang" => "en",
                                        "tagName" => self::TAG_NAME_EN
                                    ]
                                )
                            ]
                        ),
                    ]
                )
            );

        $this->localizationRepositoryMock->shouldReceive("getLanguage");

        $tagName = $this->tagService->getTagName(self::TAG_ID, "en");

        $this->assertEquals(self::TAG_NAME_EN, $tagName);
    }

    /** @test */
    public function it_gets_the_legacy_name_as_fallback()
    {
        $tagName = "Test Tag";
        $this->authHelperMock
            ->shouldReceive("processUnguarded")
            ->andReturn(
                factory(Tag::class)->make(
                    [
                        "id" => self::TAG_ID,
                        "tagName" => $tagName,
                        "names" => collect(
                            [
                                factory(TagName::class)->make(
                                    [
                                        "tagLang" => "de",
                                        "tagName" => self::TAG_NAME_DE
                                    ]
                                ),
                                factory(TagName::class)->make(
                                    [
                                        "tagLang" => "en",
                                        "tagName" => self::TAG_NAME_EN
                                    ]
                                )
                            ]
                        ),
                    ]
                )
            );

        $this->localizationRepositoryMock->shouldReceive("getLanguage");

        $this->assertEquals($tagName, $this->tagService->getTagName(self::TAG_ID, "fr"));
    }

    /** @test */
    public function it_returns_empty_string_if_tag_does_not_exist()
    {
        $this->authHelperMock
            ->shouldReceive("processUnguarded")
            ->andReturn(null);

        $this->localizationRepositoryMock->shouldReceive("getLanguage");


        $this->assertEquals("", $this->tagService->getTagName(self::TAG_ID));
    }
}
