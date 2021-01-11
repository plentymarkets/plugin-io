<?php //strict

namespace IO\Services;

use IO\Helper\Utils;
use Plenty\Modules\Authorization\Services\AuthHelper;
use Plenty\Modules\Tag\Contracts\TagRepositoryContract;
use Plenty\Modules\Tag\Models\Tag;

/**
 * Service Class TagService
 *
 * This service class contains functions related to tag functionality.
 * All public functions are available in the Twig template renderer.
 *
 * @package IO\Services
 */
class TagService
{
    /** @var TagRepositoryContract */
    private $tagRepository;

    /**
     * TagService constructor.
     * @param TagRepositoryContract $tagRepository
     */
    public function __construct(TagRepositoryContract $tagRepository)
    {
        $this->tagRepository = $tagRepository;
    }

    /**
     * Get a tag by its id
     *
     * @param int $tagId The id of the tag
     * @return Tag
     * @throws \Throwable
     */
    public function getTagById(int $tagId)
    {
        /** @var AuthHelper $authHelper */
        $authHelper = pluginApp(AuthHelper::class);
        $tagRepository = $this->tagRepository;

        $tagData = $authHelper->processUnguarded(function () use ($tagRepository, $tagId) {
            return $tagRepository->getTagById($tagId);
        });

        return $tagData;
    }

    /**
     * Get the name of a tag for a specific language
     *
     * @param int $tagId The id of the tag
     * @param string|null $lang The language to get the name in (ISO-639-1)
     * @return string
     * @throws \Throwable
     */
    public function getTagName(int $tagId, $lang = null)
    {
        if ($lang === null) {
            $lang = Utils::getLang();
        }

        $tag = $this->getTagById($tagId);

        if (is_null($tag)) {
            return "";
        }

        foreach ($tag->names as $tagName) {
            if ($tagName->tagLang === $lang) {
                return $tagName->tagName;
            }
        }

        return $tag->tagName;
    }
}
