<?php

namespace IO\Extensions\Mail;

use IO\DBModels\UserDataHash;
use IO\Extensions\Constants\ShopUrls;
use IO\Helper\RouteConfig;
use IO\Services\TemplateConfigService;
use IO\Services\UserDataHashService;
use Plenty\Modules\Account\Contact\Contracts\ContactRepositoryContract;
use Plenty\Modules\Plugin\Events\PluginSendMail;
use Plenty\Modules\Plugin\Services\PluginSendMailService;
use Plenty\Plugin\Log\Loggable;

class IOSendMail
{
    use Loggable;

    /** @var PluginSendMailService */
    private $sendMailService;

    private $placeholderList = [];

    public function __construct(PluginSendMailService $sendMailService)
    {
        $this->sendMailService = $sendMailService;
    }

    /**
     * @param PluginSendMail $pluginSendMail
     */
    public function handle(PluginSendMail $pluginSendMail)
    {
        $this->getLogger(__CLASS__)->debug(
            "IO::Debug.IOSendMail_handleSendMail",
            [
                "template" => $pluginSendMail->getTemplate(),
                "email" => $pluginSendMail->getContactEmail(),
                "callFunction" => $pluginSendMail->getCallFunction()
            ]
        );

        if ($pluginSendMail->getCallFunction() == PluginSendMailService::FUNCTION_COLLECT_PLACEHOLDER) {

            if (RouteConfig::isActive(RouteConfig::TERMS_CONDITIONS)) {
                $this->setPlaceholderValue('Link_TermsCondition', 'gtc');
            } else {
                $this->setPlaceholderValue('Link_TermsCondition', '');
            }

            /** @var TemplateConfigService $templateConfig */
            $templateConfig = pluginApp(TemplateConfigService::class);
            $enableOldURLPattern = $templateConfig->getBoolean('global.enableOldUrlPattern');
            if( RouteConfig::isActive(RouteConfig::ITEM) && !$enableOldURLPattern) {
                $this->setPlaceholderValue('Link_Item', '_{itemId}_{variationId}');
            } else {
                $this->setPlaceholderValue('Link_Item', '');
            }

            if (in_array(RouteConfig::PASSWORD_RESET, RouteConfig::getEnabledRoutes())  && strlen($pluginSendMail->getContactEmail())) {
                /** @var ContactRepositoryContract $contactRepository */
                $contactRepository = pluginApp(ContactRepositoryContract::class);
                $contactId = $contactRepository->getContactIdByEmail($pluginSendMail->getContactEmail());

                if ($contactId === null) {
                    $this->setPlaceholderValue('Link_NewPassword', '');
                    $this->setPlaceholderValue('Link_ChangePassword', '');
                } else {
                    /** @var UserDataHashService $hashService */
                    $hashService = pluginApp(UserDataHashService::class);
                    $this->setPlaceholderValue('Link_NewPassword', '?show=forgotPassword&email='.$pluginSendMail->getContactEmail());
                    $hash = $hashService->findHash(UserDataHash::TYPE_RESET_PASSWORD, $contactId);
                    if (is_null($hash))
                    {
                        /** @var UserDataHash $hashEntry */
                        $hashEntry = $hashService->create(
                            ['mail' => $pluginSendMail->getContactEmail()],
                            UserDataHash::TYPE_RESET_PASSWORD,
                            null,
                            $contactId
                        );
                        $hash = $hashEntry->hash;
                    }

                    if (RouteConfig::getCategoryId( RouteConfig::PASSWORD_RESET ) === 0)
                    {
                        // Build legacy route
                        $this->setPlaceholderValue('Link_ChangePassword', 'password-reset/'.$contactId. '/'  . $hash);
                    }
                    else
                    {
                        /** @var ShopUrls $shopUrls */
                        $shopUrls = pluginApp(ShopUrls::class);
                        // Because of the placeholder structure, we need to remove a slash from the front of the link
                        $passwordResetLink = $shopUrls->passwordReset . '?contactId=' . $contactId . '&hash=' . $hash;
                        $passwordResetLink = ltrim($passwordResetLink, '/');
                        $this->setPlaceholderValue('Link_ChangePassword', $passwordResetLink);
                    }
                }

            } else {
                $this->setPlaceholderValue('Link_NewPassword', '');
                $this->setPlaceholderValue('Link_ChangePassword', '');
            }

            $this->getLogger(__CLASS__)->debug(
                "IO::Debug.IOSendMail_placeholdersCollected",
                [
                    "template" => $pluginSendMail->getTemplate(),
                    "email" => $pluginSendMail->getContactEmail(),
                    "placeholder" => $this->placeholderList
                ]
            );
        }
    }

    private function setPlaceholderValue($placeholder, $value)
    {
        $this->placeholderList[$placeholder] = $value;
        $this->sendMailService->addEmailPlaceholder($placeholder, $value);
    }
}
