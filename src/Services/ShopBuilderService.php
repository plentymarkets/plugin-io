<?php //strict

namespace IO\Services;

use Exception;
use IO\Helper\Utils;
use Plenty\Modules\Authorization\Services\AuthHelper;
use Plenty\Modules\ShopBuilder\Contracts\ContentRepositoryContract;
use Plenty\Plugin\Log\Loggable;
use Plenty\Plugin\Templates\Twig;

/**
 * Service Class for ShopBuilder Contents
 *
 * This service class contains functions related to tag functionality.
 * All public functions are available in the Twig template renderer.
 *
 * @package IO\Services
 */
class ShopBuilderService
{
    use Loggable;

    /** @var ContentRepositoryContract */
    private $contentRepo;
    /** @var AuthHelper */
    private $authHelper;

    private $pluginSetId = 0;
    private $lang = "de";

    /**
     * ShopBuilderService constructor.
     * @param ContentRepositoryContract $contentRepo
     */
    public function __construct(ContentRepositoryContract $contentRepo, AuthHelper $authHelper)
    {
        $this->contentRepo = $contentRepo;
        $this->authHelper = $authHelper;
        $this->pluginSetId = pluginSetId();
        $this->lang = Utils::getLang();
    }


    public function getContent($categoryId)
    {
        $markup = "";

        if ($categoryId > 0) {
            $options = [
                'containerName' => 'ShopBuilder::Category.' . $categoryId,
                // 'type' => 'content', // For now, rteturn contents of all types here
                'pluginSetId' => $this->pluginSetId,
                'language' => $this->lang,
                'active' => 1
            ];

            $this->getLogger(__CLASS__)->debug(
                "IO::Debug.ShopBuilderServiceGetContent",
                [
                    'options' => $options
                ]
            );

            $contentRepo = $this->contentRepo;
            $contentLinks = $this->authHelper->processUnguarded(function () use ($contentRepo, $options) {
                return $contentRepo->searchContents(200, 1, $options);
            });
            $entries = $contentLinks->getResult();

            $this->getLogger(__CLASS__)->debug(
                "IO::Debug.ShopBuilderServiceGetContentContentLinks",
                [
                    'contentLinks' => $contentLinks,
                    'entries' => $entries
                ]
            );

            if (count($entries) <= 0)
                return null;

            $contentId = $entries[0]->id;
            $markup = $this->getContentMarkup($contentId);

            if (strlen($markup) <= 0)
                return null;

            $twig = pluginApp(Twig::class);
            $markup = $twig->renderString($markup);
        }

        return $markup;
    }
    public function getContentUpdatedAt($categoryId)
    {
        if ($categoryId > 0) {
            $options = [
                'containerName' => 'ShopBuilder::Category.' . $categoryId,
                'pluginSetId' => $this->pluginSetId,
                'language' => $this->lang,
                'active' => 1
            ];

            $contentRepo = $this->contentRepo;
            $contentLinks = $this->authHelper->processUnguarded(function () use ($contentRepo, $options) {
                return $contentRepo->searchContents(200, 1, $options);
            });
            $entries = $contentLinks->getResult();

            if (count($entries) <= 0)
                return null;

            return $entries[0]->updatedAt;
        }
    }

    private function getContentMarkup($contentId = null)
    {
        $this->getLogger(__CLASS__)->debug(
            "IO::Debug.ShopBuilderServiceGetContentMarkup",
            [
                'contentId' => $contentId
            ]
        );

        if (is_null($contentId) || intval($contentId) <= 0) {
            $this->getLogger(__CLASS__)->debug(
                "IO::Debug.ShopBuilderServiceContentIdFault",
                [
                    'contentId' => $contentId,
                    'intval()' => intval($contentId),
                ]
            );
            return null;
        }

        $contentRepo = $this->contentRepo;
        $lang = $this->lang;
        $pluginSetId = $this->pluginSetId;
        $markup = "";

        $content = $this->authHelper->processUnguarded(function () use ($contentRepo, $contentId, $lang, $pluginSetId) {
            return $contentRepo->getContent($contentId, $pluginSetId, $lang);
        });

        $this->getLogger(__CLASS__)->debug(
            "IO::Debug.ShopBuilderServiceTryToGetContent",
            [
                'content' => $content
            ]
        );

        if (!isset($content->dropzones['defaultDropzone'])) {
            $this->getLogger(__CLASS__)->debug(
                "IO::Debug.ShopBuilderServiceTryToGetContent",
                [
                    'content' => $content
                ]
            );
            return null;
        }

        foreach ($content->dropzones['defaultDropzone'] as $dropzone) {
            $markup .= $dropzone->markup;
        }


        $this->getLogger(__CLASS__)->debug(
            "IO::Debug.ShopBuilderServiceFinalMarkup",
            [
                'markup' => $markup
            ]
        );
        return $markup;
    }
}