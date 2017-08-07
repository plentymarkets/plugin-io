<?php

namespace IO\Validators\Customer;

use Plenty\Validation\Validator;

class ContactFormValidator extends Validator
{
    public function defineAttributes()
    {
        $this->addString('name', true);
        $this->addString('user_mail', true);
        $this->addString('subject', true);
        $this->addString('message', true);
    }
}