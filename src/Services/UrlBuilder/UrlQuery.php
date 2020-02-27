<?php

namespace IO\Services\UrlBuilder;

use IO\Helper\Utils;
use Plenty\Modules\Webshop\Contracts\WebstoreConfigurationRepositoryContract;
use Plenty\Plugin\ConfigRepository;

/**
 * Class UrlQuery
 * @package IO\Services\UrlBuilder
 * @deprecated since 5.0.0 will be removed in 6.0.0
 * @see \Plenty\Modules\Webshop\Helpers\UrlQuery
 */
class UrlQuery
{
    private $domain;
    private $path;
    private $lang;

    /**
     * @return bool
     * @deprecated since 5.0.0 will be removed in 6.0.0
     * @see \Plenty\Modules\Webshop\Helpers\UrlQuery::shouldAppendTrailingSlash()
     */
    public static function shouldAppendTrailingSlash()
    {
        /** @var ConfigRepository $configRepository */
        $configRepository = pluginApp(ConfigRepository::class);
        return $configRepository->get('plenty.system.info.urlTrailingSlash', 0) === 2;
    }

    /**
     * UrlQuery constructor.
     * @param string $path
     * @param string $lang
     */
    public function __construct($path = null, $lang = null)
    {
        /** @var WebstoreConfigurationRepositoryContract $webstoreConfigurationRepository */
        $webstoreConfigurationRepository = pluginApp(WebstoreConfigurationRepositoryContract::class);
        $this->domain = $webstoreConfigurationRepository->getWebstoreConfiguration()->domainSsl;
        $this->path = $path;

        if ($path !== null) {
            if (substr($this->path, 0, 1) !== "/") {
                $this->path = "/" . $this->path;
            }

            if (substr($this->path, strlen($this->path) - 1, 1) === "/") {
                $this->path = substr($this->path, 0, strlen($this->path) - 1);
            }
        }


        if ($lang === null) {
            $this->lang = Utils::getLang();
        } else {
            $this->lang = $lang;
        }
    }

    /**
     * @param string $suffix
     * @return UrlQuery
     * @deprecated since 5.0.0 will be removed in 6.0.0
     * @see \Plenty\Modules\Webshop\Helpers\UrlQuery::append()
     */
    public function append($suffix): UrlQuery
    {
        $this->path = $this->path . $suffix;

        return $this;
    }

    /**
     * @param string $path
     * @return UrlQuery
     * @deprecated since 5.0.0 will be removed in 6.0.0
     * @see \Plenty\Modules\Webshop\Helpers\UrlQuery::join()
     */
    public function join($path): UrlQuery
    {
        if (substr($path, 0, 1) !== "/" && substr($this->path, strlen($this->path) - 1, 1) !== "/") {
            $path = "/" . $path;
        }

        if (substr($path, strlen($path) - 1, 1) === "/") {
            $path = substr($path, 0, strlen($path) - 1);
        }

        return $this->append($path);
    }

    /**
     * @param bool $includeLanguage
     * @return string|null
     * @deprecated since 5.0.0 will be removed in 6.0.0
     * @see \Plenty\Modules\Webshop\Helpers\UrlQuery::toAbsoluteUrl()
     */
    public function toAbsoluteUrl(bool $includeLanguage = false)
    {
        if ($this->path === null) {
            return null;
        }

        return $this->domain . $this->toRelativeUrl($includeLanguage);
    }

    /**
     * @param bool $includeLanguage
     * @return string|null
     * @deprecated since 5.0.0 will be removed in 6.0.0
     * @see \Plenty\Modules\Webshop\Helpers\UrlQuery::toRelativeUrl()
     */
    public function toRelativeUrl(bool $includeLanguage = false)
    {
        if ($this->path === null) {
            return null;
        }

        $splittedPath = explode('?', $this->path);
        $path = $splittedPath[0];

        $queryParams = '';
        if (isset($splittedPath[1])) {
            $queryParams = $splittedPath[1];
        }

        if (isset($path[strlen($path) - 1]) && $path[strlen($path) - 1] == '/') {
            $path = substr($path, 0, -1);
        }

        $queryParams = strlen($queryParams) ? "?" . $queryParams : "";

        $trailingSlash = self::shouldAppendTrailingSlash() ? "/" : "";

        if ($includeLanguage && strpos($path, '/' . $this->lang) !== 0) {
            return '/' . $this->lang . $path . $trailingSlash . $queryParams;
        } elseif (strlen($path) == 0) {
            return '/';
        }

        return $path . $trailingSlash . $queryParams;
    }

    /**
     * @param bool $includeLanguage
     * @return false|string|null
     * @deprecated since 5.0.0 will be removed in 6.0.0
     * @see \Plenty\Modules\Webshop\Helpers\UrlQuery::getPath()
     */
    public function getPath(bool $includeLanguage = false)
    {
        if ($this->path === null) {
            return null;
        }

        return substr($this->toRelativeUrl($includeLanguage), 1);
    }

    /**
     * @param string $path
     * @return bool
     * @deprecated since 5.0.0 will be removed in 6.0.0
     * @see \Plenty\Modules\Webshop\Helpers\UrlQuery::equals()
     */
    public function equals($path)
    {
        return $this->path === $path || $this->path === $path . "/";
    }
}
