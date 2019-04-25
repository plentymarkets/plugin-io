<?php

namespace IO\Extensions\Mail;

use IO\DBModels\UserDataHash;
use IO\Helper\RouteConfig;
use IO\Services\TemplateConfigService;
use IO\Services\UserDataHashService;
use Plenty\Modules\Account\Contact\Contracts\ContactRepositoryContract;
use Plenty\Modules\Plugin\Events\PluginSendMail;
use Plenty\Modules\Plugin\Services\PluginSendMailService;

class IOSendMail
{
    /**
     * @param PluginSendMail $pluginSendMail
     */
    public function handle(PluginSendMail $pluginSendMail)
    {
        /** @var PluginSendMailService $pluginSendMailService */
        $pluginSendMailService = pluginApp(PluginSendMailService::class);
        $pluginSendMailService->setInitialized(true);

        if ($pluginSendMail->getCallFunction() == PluginSendMailService::FUNCTION_COLLECT_PLACEHOLDER) {

            if (RouteConfig::isActive(RouteConfig::TERMS_CONDITIONS)) {
                $pluginSendMailService->addEmailPlaceholder('Link_TermsCondition', 'gtc');
            } else {
                $pluginSendMailService->addEmailPlaceholder('Link_TermsCondition', '');
            }

            $templateConfig = pluginApp(TemplateConfigService::class);
            $enableOldURLPattern = $templateConfig->get('global.enableOldUrlPattern');
            if( RouteConfig::isActive(RouteConfig::ITEM) && (!strlen($enableOldURLPattern) || $enableOldURLPattern == 'false')) {
                $pluginSendMailService->addEmailPlaceholder('Link_Item', '_{itemId}_{variationId}');
            } else {
                $pluginSendMailService->addEmailPlaceholder('Link_Item', '');
            }

            if (RouteConfig::isActive(RouteConfig::PASSWORD_RESET)  && strlen($pluginSendMail->getContactEmail())) {
                /** @var ContactRepositoryContract $contactRepository */
                $contactRepository = pluginApp(ContactRepositoryContract::class);
                $contactId = $contactRepository->getContactIdByEmail($pluginSendMail->getContactEmail());

                if ($contactId === null) {
                    $pluginSendMailService->addEmailPlaceholder('Link_NewPassword', '');
                    $pluginSendMailService->addEmailPlaceholder('Link_ChangePassword', '');
                } else {
                    /** @var UserDataHashService $hashService */
                    $hashService = pluginApp(UserDataHashService::class);
                    $pluginSendMailService->addEmailPlaceholder('Link_NewPassword', '?show=forgotPassword&email='.$pluginSendMail->getContactEmail());
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
                    $pluginSendMailService->addEmailPlaceholder('Link_ChangePassword', 'password-reset/'.$contactId. '/'  . $hash);
                }

            } else {
                $pluginSendMailService->addEmailPlaceholder('Link_NewPassword', '');
                $pluginSendMailService->addEmailPlaceholder('Link_ChangePassword', '');
            }
        }
    }
}