<?php
namespace IO\Extensions\Mail;

use Plenty\Modules\Helper\AutomaticEmail\Contracts\AutomaticEmailContract;
use Plenty\Modules\Helper\AutomaticEmail\Models\AutomaticEmail;

trait SendMail
{

    /**
     * @param string $template
     * @param string $emailData Must be a fully qualified class name
     * @param array $params
     */
    private function sendMail($template, $emailData, $params)
    {
        $emailData = pluginApp($emailData, $params);

        /**
        * @var AutomaticEmail $email
        */
        $email = pluginApp(AutomaticEmail::class, ['template' => $template , 'emailData' => $emailData]);

        /**
         * @var AutomaticEmailContract $automaticEmailRepository
         */
        $automaticEmailRepository = pluginApp(AutomaticEmailContract::class);
        $automaticEmailRepository->sendAutomatic($email);
    }
}