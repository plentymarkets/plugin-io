<?php

namespace IO\Extensions\Mail;

use IO\Services\CustomerPasswordResetService;
use Plenty\Modules\Plugin\Events\PluginSendMail;
use Plenty\Modules\Plugin\Services\PluginSendMailService;
use Plenty\Plugin\ConfigRepository;

class IOSendMail
{
    /**
     * @param PluginSendMail $pluginSendMail
     */
    public function handle(PluginSendMail $pluginSendMail)
    {
        /** @var PluginSendMailService $pluginSendMailService */
        $pluginSendMailService = pluginApp(PluginSendMailService::class);



        /**
         * @var ConfigRepository $config
         */
        $config = pluginApp(ConfigRepository::class);
        $enabledRoutes = explode(", ",  $config->get("IO.routing.enabled_routes") );

        if (in_array("gtc", $enabledRoutes) || in_array("all", $enabledRoutes) ) {
            $pluginSendMailService->addEmailPlaceholder('Link_TermsCondition', 'gtc');
        } else {
            $pluginSendMailService->addEmailPlaceholder('Link_TermsCondition', '');
        }
        $pluginSendMailService->addEmailPlaceholder('Link_Item', '');

         if (in_array("password-reset", $enabledRoutes) || in_array("all", $enabledRoutes) ) {
             /**
              * @var CustomerPasswordResetService $customerPasswordResetService
              */
            $customerPasswordResetService = pluginApp(CustomerPasswordResetService::class);

            $contactId = $customerPasswordResetService->getContactIdbyEmailAddress($pluginSendMail->getContactEmail());
            $pluginSendMailService->addEmailPlaceholder('Link_NewPassword', '');
            $pluginSendMailService->addEmailPlaceholder('Link_ChangePassword', 'password-reset/'.$contactId. '/'  . $hash);
        } else {
            $pluginSendMailService->addEmailPlaceholder('Link_NewPassword', '');
            $pluginSendMailService->addEmailPlaceholder('Link_ChangePassword', '');
        }
    }
}