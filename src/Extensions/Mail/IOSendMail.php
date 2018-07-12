<?php

namespace IO\Extensions\Mail;

use IO\Services\CustomerPasswordResetService;
use Plenty\Modules\Plugin\Events\PluginSendMail;
use Plenty\Modules\Plugin\Services\PluginSendMailService;


class IOSendMail
{

    /**
     * @param PluginSendMail $pluginSendMail
     */
    public function handle(PluginSendMail $pluginSendMail)
    {
        $template = $pluginSendMail->getTemplate();
        $email = $pluginSendMail->getContactEmail();


        /** @var PluginSendMailService $pluginSendMailService */
        $pluginSendMailService = pluginApp(PluginSendMailService::class);

        $response = null;
        if(strlen($email) > 0)
        {
            switch($template)
            {
                case PluginSendMailService::PASSWORD_RESET:
                    /**
                     * @var CustomerPasswordResetService $customerPasswordResetService
                     */
                    $customerPasswordResetService = pluginApp(CustomerPasswordResetService::class);
                    $response = $customerPasswordResetService->resetPassword($email, 'Ceres::Customer.ResetPasswordMail', 'Ceres::Template.resetPwMailSubject');
                    break;
            }
        }

        if($response === true)
        {
            $pluginSendMailService->setStatus(true);
        }
        else
        {
            $pluginSendMailService->setStatus(false);
        }
    }
}