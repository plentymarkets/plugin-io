<?php

namespace IO\Tests\Unit;

use IO\Constants\SessionStorageKeys;
use IO\Services\SessionStorageService;
use IO\Services\NotificationService;
use IO\Tests\TestCase;
use Mockery;


class NotificationServiceTest extends TestCase
{
    /* @var SessionStorageService $sessionStorageServiceMock*/
    protected $sessionStorageServiceMock;

    /* @var NotificationService $notificationService*/
    protected $notificationService;

    protected function setUp()
    {
        parent::setUp();
        $this->sessionStorageServiceMock = Mockery::mock(SessionStorageService::class);
        $this->replaceInstanceByMock(SessionStorageService::class, $this->sessionStorageServiceMock);

        $this->notificationService = pluginApp(NotificationService::class);
    }

    /** @test */
    public function it_checks_if_getNotifications_gets_cleared_when_true()
    {
        $notifications = [
            "error"     => null,
            "warn"      => null,
            "info"      => null,
            "success"   => null,
            "log"       => null
        ];

        $this->sessionStorageServiceMock
            ->shouldReceive("getSessionValue")
            ->with(SessionStorageKeys::NOTIFICATIONS)
            ->andReturn(json_encode($notifications));


        $this->sessionStorageServiceMock
            ->shouldReceive("setSessionValue")
            ->andReturn();

        $this->assertEquals($notifications,$this->notificationService->getNotifications(true));
    }

    /** @test */
    public function it_checks_if_getNotifications_with_false_parameter_does_not_clear_notifications()
    {
        $notifications = [
            "error"     => null,
            "warn"      => null,
            "info"      => null,
            "success"   => null,
            "log"       => ['test' => 'test']
        ];

        $this->sessionStorageServiceMock
            ->shouldReceive("getSessionValue")
            ->with(SessionStorageKeys::NOTIFICATIONS)
            ->andReturn(json_encode($notifications));

        $this->sessionStorageServiceMock
            ->shouldReceive("setSessionValue")
            ->andReturn();

        $this->assertEquals($notifications,$this->notificationService->getNotifications(false));
    }
}
