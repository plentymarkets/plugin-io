<?php

namespace IO\Services;

use Plenty\Modules\Account\Newsletter\Contracts\NewsletterRepositoryContract;
use Plenty\Modules\Account\Newsletter\Models\Recipient;
use Plenty\Modules\Authorization\Services\AuthHelper;

/**
 * Service Class CustomerNewsletterService
 *
 * This service class contains functions used for handling newsletter related tasks.
 * All public functions are available in the Twig template renderer.
 *
 * @package IO\Services
 */
class CustomerNewsletterService
{
    /** @var NewsletterRepositoryContract */
    private $newsletterRepo;

    /**
     * CustomerNewsletterService constructor.
     * @param NewsletterRepositoryContract $newsletterRepo
     */
    public function __construct(NewsletterRepositoryContract $newsletterRepo)
    {
        $this->newsletterRepo = $newsletterRepo;
    }

    /**
     * Subscribes a single user to a newsletter
     * @param string $email The email address of the customer
     * @param int $emailFolder Id for the type of newsletter
     * @param string $firstName Optional: First name of the customer
     * @param string $lastName Optional: Last name of the customer
     * @throws \Throwable
     */
    public function saveNewsletterData($email, $emailFolder, $firstName = '', $lastName = '')
    {
        $this->saveMultipleNewsletterData($email, [$emailFolder], $firstName, $lastName);
    }

    /**
     * Subscribes a single user to multiple newsletters
     * @param string $email The email address of the customer
     * @param int[] $emailFolders Ids for the types of newsletters
     * @param string $firstName Optional: First name of the customer
     * @param string $lastName Optional: Last name of the customer
     * @throws \Throwable
     */
    public function saveMultipleNewsletterData($email, $emailFolders, $firstName = '', $lastName = '')
    {
        if (strlen($email) && count($emailFolders)) {
            /** @var AuthHelper $authHelper */
            $authHelper = pluginApp(AuthHelper::class);
            $newsletterRepo = $this->newsletterRepo;

            foreach ($emailFolders as $key => $emailFolder) {
                $recipientData = $authHelper->processUnguarded(function () use ($email, $emailFolder, $newsletterRepo) {
                    return $newsletterRepo->listRecipients(['*'], 1, 1, ['email' => $email, 'folderId' => $emailFolder], [])->getResult()[0];
                });

                if ($recipientData instanceof Recipient) {
                    unset($emailFolders[$key]);
                }
            }

            if (count($emailFolders)) {
                $this->newsletterRepo->addToNewsletterList($email, $firstName, $lastName, $emailFolders);
            }
        }
    }

    /**
     * @param string $authString Authorization string used for security purposes
     * @param int $newsletterEmailId Unique id of the newsletter registration
     * @return bool
     * @throws \Throwable
     */
    public function updateOptInStatus($authString, $newsletterEmailId)
    {
        /** @var AuthHelper $authHelper */
        $authHelper = pluginApp(AuthHelper::class);
        $newsletterRepo = $this->newsletterRepo;

        $emailData = $authHelper->processUnguarded(function () use ($newsletterEmailId, $newsletterRepo) {
            return $newsletterRepo->listRecipientById($newsletterEmailId);
        });

        if ($authString === $emailData->confirmAuthString) {
            $authHelper->processUnguarded(function () use ($newsletterEmailId, $newsletterRepo) {
                $newsletterRepo->updateRecipientById($newsletterEmailId, [
                    'confirmedTimestamp' => date('Y-m-d H:i:s', time())
                ]);
            });

            return true;
        }

        return false;
    }

    /**
     * Delete recipients from the newsletter
     * Not passing the optional parameter $emailFolder deletes the recipient from all email-folders
     * @param string $email Email of the user
     * @param int $emailFolder Id of the type of newsletter
     * @return bool
     * @throws \Throwable
     */
    public function deleteNewsletterDataByEmail($email, $emailFolder = 0)
    {
        /** @var AuthHelper $authHelper */
        $authHelper = pluginApp(AuthHelper::class);
        $newsletterRepo = $this->newsletterRepo;

        // Set up filter
        $filter = [
            'email' => $email
        ];

        if ($emailFolder > 0) {
            $filter['folderId'] = $emailFolder;
        }

        // Fetch list of \Recipient based on filters
        $recipientList = $authHelper->processUnguarded(function () use ($filter, $newsletterRepo) {
            return $newsletterRepo->listRecipients(['*'], 1, 100, $filter, [])->getResult();
        });

        // If any exist, delete them
        if (count($recipientList)) {
            foreach ($recipientList as $recipientData) {
                if ($recipientData instanceof Recipient) {
                    $authHelper->processUnguarded(function () use ($recipientData, $newsletterRepo) {
                        return $this->newsletterRepo->deleteRecipientById($recipientData->id);
                    });
                }
            }

            $success = true;
        } else {
            $success = false;
        }

        return $success;
    }
}

