<?php

namespace IO\Tests\Unit;

use IO\Services\NotificationService;
use IO\Tests\TestCase;
use Mockery;
use Plenty\Modules\Webshop\Contracts\SessionStorageRepositoryContract;


class NotificationServiceTest extends TestCase
{
    /* @var SessionStorageRepositoryContract $sessionStorageRepositoryMock */
    protected $sessionStorageRepositoryMock;

    /* @var NotificationService $notificationService */
    protected $notificationService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sessionStorageRepositoryMock = Mockery::mock(SessionStorageRepositoryContract::class);
        $this->replaceInstanceByMock(SessionStorageRepositoryContract::class, $this->sessionStorageRepositoryMock);

        $this->notificationService = pluginApp(NotificationService::class);
    }

    /** @test */
    public function it_checks_if_getNotifications_gets_cleared_when_true()
    {
        $notifications = [
            "error" => null,
            "warn" => null,
            "info" => null,
            "success" => null,
            "log" => null
        ];

        $this->sessionStorageRepositoryMock
            ->shouldReceive("getSessionValue")
            ->with(SessionStorageRepositoryContract::NOTIFICATIONS)
            ->andReturn(json_encode($notifications));


        $this->sessionStorageRepositoryMock
            ->shouldReceive("setSessionValue")
            ->andReturn();

        $this->assertEquals($notifications, $this->notificationService->getNotifications(true));
    }

    /** @test */
    public function it_checks_if_getNotifications_with_false_parameter_does_not_clear_notifications()
    {
        $notifications = [
            "error" => null,
            "warn" => null,
            "info" => null,
            "success" => null,
            "log" => ['test' => 'test']
        ];

        $this->sessionStorageRepositoryMock
            ->shouldReceive("getSessionValue")
            ->with(SessionStorageRepositoryContract::NOTIFICATIONS)
            ->andReturn(json_encode($notifications));

        $this->sessionStorageRepositoryMock
            ->shouldReceive("setSessionValue")
            ->andReturn();

        $this->assertEquals($notifications, $this->notificationService->getNotifications(false));
    }
}
