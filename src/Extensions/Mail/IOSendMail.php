<?php

namespace IO\Extensions\Mail;

use IO\Helper\RouteConfig;
use IO\Services\CustomerPasswordResetService;
use IO\Services\TemplateConfigService;
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
                /**
                 * @var CustomerPasswordResetService $customerPasswordResetService
                 */
                $customerPasswordResetService = pluginApp(CustomerPasswordResetService::class);

                $contactId = $customerPasswordResetService->getContactIdbyEmailAddress($pluginSendMail->getContactEmail());
                $pluginSendMailService->addEmailPlaceholder('Link_NewPassword', '?show=forgotPassword&email='.$pluginSendMail->getContactEmail());

                $hash = $customerPasswordResetService->getLastHashOrCreate($contactId, $pluginSendMail->getContactEmail());
                $pluginSendMailService->addEmailPlaceholder('Link_ChangePassword', 'password-reset/'.$contactId. '/'  . $hash);
            } else {
                $pluginSendMailService->addEmailPlaceholder('Link_NewPassword', '');
                $pluginSendMailService->addEmailPlaceholder('Link_ChangePassword', '');
            }
        }
    }
}