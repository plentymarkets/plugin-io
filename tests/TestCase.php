<?php

namespace IO\Tests;

use Plenty\Modules\Account\Contact\Models\Contact;
use Plenty\Modules\Account\Contact\Models\ContactOption;
use Plenty\Modules\Authentication\Services\AccountAuthenticationProxy;
use Tests\BrowserKitTestCase;

/**
 * Class TestCase
 */
abstract class TestCase extends BrowserKitTestCase
{
    protected function setUp()
	{
		parent::setUp();
	}

    protected function performLogin()
    {
        $email = $this->fake->email;
        $password = $this->fake->password;

        $contact = factory(Contact::class)->create(['blocked' => 0, 'password' => pluginApp('hash')->make($password)]);

        factory(ContactOption::class)->create([
            'contactId' => $contact->id,
            'typeId' => ContactOption::TYPE_MAIL,
            'subTypeId' => ContactOption::SUBTYPE_PRIVATE,
            'value' => $email
        ]);

        /** @var AccountAuthenticationProxy $authProxy */
        $authProxy = app(AccountAuthenticationProxy::class);

        /** @noinspection PhpUnhandledExceptionInspection */
        $authProxy->performLogin([
            'email' => $email,
            'password' => $password,
        ]);
    }
}