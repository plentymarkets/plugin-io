<?php

namespace IO\Tests\Feature;

use IO\Constants\LogLevel;
use IO\Middlewares\ClearNotifications;
use IO\Services\NotificationService;
use IO\Tests\TestCase;
use Plenty\Modules\Webshop\Contracts\SessionStorageRepositoryContract;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;


class NotificationServiceFeatureTest extends TestCase
{
    /** @var SessionStorageRepositoryContract $sessionStorageRepository */
    protected $sessionStorageRepository;

    /** @var NotificationService $notificationService */
    protected $notificationService;

    /* @var Request $request */
    protected $request;

    /* @var Response $response */
    protected $response;

    /* @var ClearNotifications $clearNotifications */
    protected $clearNotifications;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sessionStorageRepository = pluginApp(SessionStorageRepositoryContract::class);
        $this->notificationService = pluginApp(NotificationService::class);
        $this->request = pluginApp(Request::class);
        $this->response = pluginApp(Response::class);
        $this->clearNotifications = pluginApp(ClearNotifications::class);
    }

    /**
     * @test
     * @dataProvider dataProviderAddNotificationData
     */
    public function it_checks_addNotification_sets_session_storage_entry($message, $type, $code)
    {
        $this->notificationService->{$type}($message, $code);
        $sessionNotifications = json_decode(
            $this->sessionStorageRepository->getSessionValue(SessionStorageRepositoryContract::NOTIFICATIONS),
            true
        );
        $this->assertEquals($code, $sessionNotifications[$type]['code']);
        $this->assertEquals($message, $sessionNotifications[$type]['message']);
    }

    /** @test */
    public function it_checks_getNotification_true_clears_session_storage_entry()
    {
        $expectedEmptyArray = [];
        $this->notificationService->log($this->fake->text, 200);
        $this->notificationService->getNotifications(true);
        $this->clearNotifications->after($this->request, $this->response);
        $notifications = json_decode(
            $this->sessionStorageRepository->getSessionValue(SessionStorageRepositoryContract::NOTIFICATIONS),
            true
        );
        $this->assertEquals($expectedEmptyArray, $notifications);
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

