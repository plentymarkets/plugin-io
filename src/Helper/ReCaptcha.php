<?php

namespace IO\Helper;

use Plenty\Modules\Webshop\Template\Contracts\TemplateConfigRepositoryContract;

class ReCaptcha
{
    public static function verify($token)
    {
        /** @var TemplateConfigRepositoryContract $templateConfigRepo */
        $templateConfigRepo = pluginApp(TemplateConfigRepositoryContract::class);
        $secret = $templateConfigRepo->get('global.google_recaptcha_secret');

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
                || $result['score'] >= $templateConfigRepo->get('global.google_recaptcha_threshold')
            );
    }
}
