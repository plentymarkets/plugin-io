<?php

namespace IO\Helper;

use IO\Services\TemplateConfigService;

class ReCaptcha
{
    public static function verify($token)
    {
        /** @var TemplateConfigService $templateConfigService */
        $templateConfigService = pluginApp(TemplateConfigService::class);
        $secret = $templateConfigService->get('global.google_recaptcha_secret');

        if ( !strlen( $secret ) )
        {
            // No secret defined in config => skip reCAPTCHA validation
            return true;
        }
        else if ( !strlen( $token ) )
        {
            // reCAPTCHA is enabled by config but no token is given
            return false;
        }

        $options = array(
            CURLOPT_URL => "https://www.google.com/recaptcha/api/siteverify?secret=$secret&response=$token",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode([
                "secret"   => $secret,
                "response" => $token
            ])
        );

        $ch = curl_init();

        foreach($options as $option => $value)
        {
            curl_setopt($ch, $option, $value);
        }

        $content = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($content, true);

        return $result["success"]
            && (!array_key_exists('score', $result)
                || $result['score'] >= $templateConfigService->get('global.google_recaptcha_threshold')
            );
    }
}
