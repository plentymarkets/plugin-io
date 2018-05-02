<?php //strict

namespace IO\Services;

use Plenty\Modules\Authorization\Services\AuthHelper;
use Plenty\Modules\Tag\Contracts\TagRepositoryContract;


/**
 * Class TagService
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

    public function getTagById(int $tagId)
    {
        /** @var AuthHelper $authHelper */
        $authHelper = pluginApp(AuthHelper::class);
        $tagRepository = $this->tagRepository;

        $tagData = $authHelper->processUnguarded( function() use ($tagRepository, $tagId)
        {
            return $tagRepository->getTagById($tagId);
        });

        return $tagData;
    }
}
