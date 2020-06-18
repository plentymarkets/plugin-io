<?php

namespace IO\Tests\Feature;


use IO\DBModels\UserDataHash;
use IO\Services\UserDataHashService;
use IO\Tests\TestCase;

class UserDataHashServiceFeatureTest extends TestCase
{
    /** @var UserDataHashService $hashService */
    private $hashService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->hashService = pluginApp(UserDataHashService::class);
    }

    /** @test */
    public function should_create_user_data_hash()
    {
        $loginMail = $this->fake->email;
        $loginPassword = $this->fake->password;

        $contact = $this->createContact($loginMail, $loginPassword);
        $this->performLogin($loginMail, $loginPassword);

        $hashMail = $this->fake->email;
        $hashData = ["mail" => $hashMail];

        $hash = $this->hashService->create(
            $hashData,
            UserDataHash::TYPE_CHANGE_MAIL);

        $this->assertNotNull($hash);
        $this->assertEquals($hash->contactId, $contact->id);
        $this->assertNotNull($hash->hash);
        $this->assertEquals($hashData, $hash->data);
    }

    /** @test */
    public function should_find_a_user_data_hash()
    {
        $loginMail = $this->fake->email;
        $loginPassword = $this->fake->password;

        $this->createContact($loginMail, $loginPassword);
        $this->performLogin($loginMail, $loginPassword);

        $hashMail = $this->fake->email;
        $hashData = ["mail" => $hashMail];

        $hash = $this->hashService->create(
            $hashData,
            UserDataHash::TYPE_CHANGE_MAIL);

        $foundHash = $this->hashService->find($hash->hash);

        $this->assertNotNull($foundHash);
        $this->assertEquals($hash, $foundHash);
    }

    /** @test */
    public function should_find_hash_by_type()
    {
        $loginMail = $this->fake->email;
        $loginPassword = $this->fake->password;

        $this->createContact($loginMail, $loginPassword);
        $this->performLogin($loginMail, $loginPassword);

        $hashMail = $this->fake->email;
        $hashData = ["mail" => $hashMail];

        $hash = $this->hashService->create(
            $hashData,
            UserDataHash::TYPE_CHANGE_MAIL);

        $hashHash = $this->hashService->findHash(UserDataHash::TYPE_CHANGE_MAIL);

        $foundHash = $this->hashService->find($hashHash);

        $this->assertNotNull($foundHash);
        $this->assertEquals($hash, $foundHash);
    }
}
