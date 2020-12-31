<?php

namespace IO\Services;

use IO\DBModels\UserDataHash;
use IO\Helper\Utils;
use Plenty\Modules\Plugin\DataBase\Contracts\DataBase;
use Plenty\Modules\Plugin\DataBase\Contracts\Model;
use Plenty\Modules\Webshop\Contracts\ContactRepositoryContract;

/**
 * Service Class UserDataHashService
 *
 * This service class contains functions related to hashing and data storage.
 * All public functions are available from the Twig template renderer.
 *
 * @package IO\Services
 */
class UserDataHashService
{
    /** @var DataBase */
    private $db;
    private $defaultTTL = 0;

    /**
     * UserDataHashService constructor.
     *
     * @param DataBase $dataBase
     * @param TemplateConfigService $templateConfigService
     */
    public function __construct(DataBase $dataBase, TemplateConfigService $templateConfigService)
    {
        $this->db = $dataBase;
        $this->defaultTTL = $templateConfigService->getInteger('global.user_data_hash_max_age', 24);
    }

    /**
     * Get entry by hash if exists
     *
     * @param string $hash The hash to search an entry for
     * @param int|null $contactId Optional: Restrict results to a contact id. Otherwise use logged in contact
     * @param int|null $plentyId Optional: Use a specific plentyId. Otherwise use id of current client.
     *
     * @return null|UserDataHash
     */
    public function find($hash, $contactId = null, $plentyId = null)
    {
        if (is_null($plentyId)) {
            $plentyId = Utils::getPlentyId();
        }

        if (is_null($contactId)) {
            $contactId = $this->getContactId();
        }

        if (is_null($contactId) || $contactId <= 0) {
            return null;
        }

        $results = $this->db->query(UserDataHash::class)
            ->where('contactId', '=', $contactId)
            ->where('plentyId', '=', $plentyId)
            ->where('hash', '=', $hash)
            ->where('expiresAt', '>', date("Y-m-d H:i:s"))
            ->orWhere('expiresAt', '=', '')
            ->get();

        if (count($results)) {
            /** @var UserDataHash $entry */
            $entry = pluginApp(UserDataHash::class);
            $entry->fillByAttributes(json_decode(json_encode($results[0]), true));
            return $entry;
        }

        return null;
    }

    /**
     * Get the hash of a specific type if exists and is not expired.
     * @param string $type The type to get the hash for
     * @param int|null $contactId Optional: Restrict results to a contact id. Otherwise use logged in contact
     * @param int|null $plentyId Optional: Use a specific plentyId. Otherwise use id of current client.
     * @return null|string
     */
    public function findHash($type, $contactId = null, $plentyId = null)
    {
        if (is_null($plentyId)) {
            $plentyId = Utils::getPlentyId();
        }

        if (is_null($contactId)) {
            $contactId = $this->getContactId();
        }

        if (is_null($contactId) || $contactId <= 0) {
            return null;
        }

        $results = $this->db->query(UserDataHash::class)
            ->where('contactId', '=', $contactId)
            ->where('plentyId', '=', $plentyId)
            ->where('type', '=', $type)
            ->where('expiresAt', '>', date("Y-m-d H:i:s"))
            ->orWhere('expiresAt', '=', '')
            ->get();

        if (count($results)) {
            /** @var UserDataHash $entry */
            $entry = $results[0];
            return $entry->hash;
        }

        return null;
    }

    /**
     * Get the decoded data assigned to a hash
     *
     * @param string $hash The hash to search an entry for
     * @param int|null $contactId Optional: Restrict results to a contact id. Otherwise use logged in contact
     * @param int|null $plentyId Optional: Use a specific plentyId. Otherwise use id of current client.
     *
     * @return mixed|null
     */
    public function getData($hash, $contactId = null, $plentyId = null)
    {
        $entry = $this->find($hash, $contactId, $plentyId);
        if (is_null($entry)) {
            return null;
        }

        return $entry->data;
    }

    /**
     * Create a new entry and assign data to the hash for later usage.
     *
     * @param mixed $data The data to assign to the generated hash entry
     * @param string $type The type of the entry
     * @param int|null $ttl Optional: Lifetime of the hash entry in hours. Will get the value from config if not defined.
     * @param int|null $contactId Optional: Restrict results to a contact id. Otherwise use logged in contact
     * @param int|null $plentyId Optional: Use a specific plentyId. Otherwise use id of current client.
     *
     * @return null|UserDataHash
     */
    public function create($data, $type, $ttl = null, $contactId = null, $plentyId = null)
    {
        if (is_null($plentyId)) {
            $plentyId = Utils::getPlentyId();
        }

        if (is_null($contactId)) {
            $contactId = $this->getContactId();
        }

        if (is_null($contactId) || $contactId <= 0) {
            return null;
        }

        if (is_null($ttl)) {
            $ttl = $this->defaultTTL;
        }
        $existingEntries = $this->db->query(UserDataHash::class)
            ->where('contactId', '=', $contactId)
            ->where('plentyId', '=', $plentyId)
            ->where('type', '=', $type)
            ->get();

        foreach ($existingEntries as $entry) {
            $this->db->delete($entry);
        }

        /** @var UserDataHash $entry */
        $entry = pluginApp(UserDataHash::class);
        $entry->type = $type;
        $entry->plentyId = $plentyId;
        $entry->contactId = $contactId;
        $entry->hash = sha1(microtime(true));
        $entry->data = json_encode($data);
        $entry->createdAt = date("Y-m-d H:i:s");
        if ($ttl > 0) {
            $entry->expiresAt = date("Y-m-d H:i:s", time() + ($ttl * 60 * 60));
        } else {
            $entry->expiresAt = '';
        }

        /** @var Model $result */
        $result = $this->db->save($entry);

        /** @var UserDataHash $createdEntry */
        $createdEntry = pluginApp(UserDataHash::class);
        $createdEntry->fillByAttributes(json_decode(json_encode($result), true));
        return $createdEntry;
    }

    /**
     * Remove a hash entry if exists.
     *
     * @param string $hash The hash to search an entry for
     * @param int|null $contactId Optional: Restrict results to a contact id. Otherwise use logged in contact
     * @param int|null $plentyId Optional: Use a specific plentyId. Otherwise use id of current client.
     *
     * @return bool
     */
    public function delete($hash, $contactId = null, $plentyId = null)
    {
        $entry = $this->find($hash, $contactId, $plentyId);
        if (!is_null($entry)) {
            $this->db->delete($entry);
            return true;
        }

        return false;
    }

    /**
     * Delete all entries
     *
     * @param string|null $type Optional: Delete only entries for a specific type
     * @param int|null $contactId Optional: Restrict results to a contact id. Otherwise use logged in contact
     * @param int|null $plentyId Optional: Use a specific plentyId. Otherwise use id of current client.
     *
     * @return bool
     */
    public function deleteAll($type = null, $contactId = null, $plentyId = null)
    {
        if (is_null($plentyId)) {
            $plentyId = Utils::getPlentyId();
        }

        if (is_null($contactId)) {
            $contactId = $this->getContactId();
        }

        if (is_null($contactId) || $contactId <= 0) {
            return false;
        }

        $query = $this->db->query(UserDataHash::class)
            ->where('contactId', '=', $contactId)
            ->where('plentyId', '=', $plentyId);

        if (!is_null($type)) {
            $query = $query->where('type', '=', $type);
        }

        return $query->delete();
    }

    /**
     * @return int
     */
    private function getContactId()
    {
        /** @var ContactRepositoryContract $contactRepository */
        $contactRepository = pluginApp(ContactRepositoryContract::class);
        return $contactRepository->getContactId();
    }

}
