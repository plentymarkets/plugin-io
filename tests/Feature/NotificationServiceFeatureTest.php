<?php

namespace IO\Tests\Feature;

use IO\Constants\LogLevel;
use IO\Constants\SessionStorageKeys;
use IO\Services\NotificationService;
use IO\Services\SessionStorageService;
use IO\Tests\TestCase;


class NotificationServiceFeatureTest extends TestCase
{
    /** @var SessionStorageService $sessionStorageService */
    protected $sessionStorageService;

    /** @var NotificationService $notificationService */
    protected $notificationService;

    protected function setUp()
    {
        parent::setUp();
        $this->sessionStorageService = pluginApp(SessionStorageService::class);
        $this->notificationService = pluginApp(NotificationService::class);
    }

    /**
     * @test
     * @dataProvider dataProviderAddNotificationData
     */
    public function it_checks_addNotification_sets_session_storage_entry($message, $type, $code)
    {
        $this->notificationService->{$type}($message, $code);
        $sessionNotifications = json_decode($this->sessionStorageService->getSessionValue(SessionStorageKeys::NOTIFICATIONS),true);
        $this->assertEquals($code,$sessionNotifications[$type]['code']);
        $this->assertEquals($message,$sessionNotifications[$type]['message']);
    }

    /** @test */
    public function it_checks_getNotification_true_clears_session_storage_entry()
    {
        $expectedEmptyArray = [];
        $this->notificationService->log($this->fake->text,200);
        $this->notificationService->getNotifications(true);
        $notifications = json_decode($this->sessionStorageService->getSessionValue(SessionStorageKeys::NOTIFICATIONS), true);
        $this->assertEquals($expectedEmptyArray,$notifications);
    }

    public function dataProviderAddNotificationData()
    {
        return [
            [
                $this->fake->text,
                LogLevel::SUCCESS,
                200
            ],
            [
                $this->fake->text,
                LogLevel::ERROR,
                404
            ],
            [
                $this->fake->text,
                LogLevel::LOG,
                200
            ],
            [
                $this->fake->text,
                LogLevel::INFO,
                200
            ],
            [
                $this->fake->text,
                LogLevel::WARN,
                301
            ]
        ];
    }
}

