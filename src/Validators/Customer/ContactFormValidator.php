<?php

namespace IO\Validators\Customer;

use Plenty\Validation\Validator;

class ContactFormValidator extends Validator
{
    public function defineAttributes()
    {
        $this->addString('name', true);
        $this->addString('userMail', true);
        $this->addString('subject', true);
        $this->addString('message', true);
        $this->addString('cc', false);
    }
}