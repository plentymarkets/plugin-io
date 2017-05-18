<?php

namespace IO\Validators\Customer;

use Plenty\Validation\Validator;
use Plenty\Plugin\ConfigRepository;
use IO\Services\TemplateConfigService;

class ContactValidator extends Validator
{
    public function defineAttributes()
    {
        /**
         * @var TemplateConfigService $templateConfigService
         */
        $templateConfigService = pluginApp(TemplateConfigService::class);
        $requiredFields = $templateConfigService->get('address.require');
    }
}