<?php

namespace IO\Services;

use IO\DBModels\UserDataHash;
use Plenty\Modules\Plugin\DataBase\Contracts\DataBase;
use Plenty\Plugin\Application;

class UserDataHashService
{
    /** @var DataBase */
    private $db;
    private $defaultTTL = 0;

    public function __construct(DataBase $dataBase, TemplateConfigService $templateConfigService)
    {
        $this->db = $dataBase;
        $this->defaultTTL = $templateConfigService->get('global.user_data_hash_max_age', 24);
        if ( $this->defaultTTL <= 0 )
        {
            $this->defaultTTL = null;
        }
    }

    public function find( $hash, $contactId = null, $plentyId = null )
    {
        if ( is_null($plentyId) )
        {
            $plentyId = pluginApp(Application::class)->getPlentyId();
        }

        if ( is_null($contactId) )
        {
            $contactId = pluginApp(CustomerService::class)->getContactId();
        }

        if ( is_null($contactId) || $contactId <= 0 )
        {
            return null;
        }

        $results = $this->db->query(UserDataHash::class)
            ->where('contactId', '=', $contactId)
            ->where('plentyId', '=', $plentyId)
            ->where('hash', '=', $hash)
            ->where('expiresAt', '>', time())
            ->orWhere('expiresAt', '=', null)
            ->get();

        if (count($results))
        {
            return $results[0];
        }

        return null;
    }

    public function findHash( $type, $contactId = null, $plentyId = null )
    {
        if ( is_null($plentyId) )
        {
            $plentyId = pluginApp(Application::class)->getPlentyId();
        }

        if ( is_null($contactId) )
        {
            $contactId = pluginApp(CustomerService::class)->getContactId();
        }

        if ( is_null($contactId) || $contactId <= 0 )
        {
            return null;
        }

        $results = $this->db->query(UserDataHash::class)
            ->where('contactId', '=', $contactId)
            ->where('plentyId', '=', $plentyId)
            ->where('type', '=', $type )
            ->where('expiresAt', '>', time())
            ->orWhere('expiresAt', '=', null)
            ->get();

        if (count($results))
        {
            return $results[0]->hash;
        }

        return null;
    }

    public function getData( $hash, $contactId = null, $plentyId = null )
    {
        $entry = $this->find( $hash, $contactId, $plentyId );
        if (is_null($entry))
        {
            return null;
        }

        return json_decode($entry->data, true);
    }

    public function create( $data, $type, $ttl = null, $contactId = null, $plentyId = null )
    {
        if ( is_null($plentyId) )
        {
            $plentyId = pluginApp(Application::class)->getPlentyId();
        }

        if ( is_null($contactId) )
        {
            $contactId = pluginApp(CustomerService::class)->getContactId();
        }

        if ( is_null($contactId) || $contactId <= 0 )
        {
            return null;
        }

        if ( is_null($ttl) )
        {
            $ttl = $this->defaultTTL;
        }
        $existingEntries = $this->db->query(UserDataHash::class)
            ->where('contactId', '=', $contactId)
            ->where('plentyId', '=', $plentyId)
            ->where('type', '=', $type)
            ->get();

        foreach($existingEntries as $entry)
        {
            $this->db->delete($entry);
        }

        /** @var UserDataHash $entry */
        $entry = pluginApp(UserDataHash::class);
        $entry->type = $type;
        $entry->plentyId = $plentyId;
        $entry->contactId = $contactId;
        $entry->hash = sha1(microtime(true));
        $entry->data = json_encode( $data );
        $entry->createdAt = date("Y-m-d H:i:s");
        if ( $ttl > 0 )
        {
            $entry->expiresAt = date("Y-m-d H:i:s", time() + ($ttl * 60 * 60));
        }

        return $this->db->save($entry);
    }

    public function delete( $hash, $contactId = null, $plentyId = null )
    {
        $entry = $this->find( $hash, $contactId, $plentyId );
        if (!is_null($entry))
        {
            $this->db->delete($entry);
            return true;
        }

        return false;
    }

    public function deleteAll( $type = null, $contactId = null, $plentyId = null )
    {
        if ( is_null($plentyId) )
        {
            $plentyId = pluginApp(Application::class)->getPlentyId();
        }

        if ( is_null($contactId) )
        {
            $contactId = pluginApp(CustomerService::class)->getContactId();
        }

        if ( is_null($contactId) || $contactId <= 0 )
        {
            return null;
        }

        $query = $this->db->query(UserDataHash::class)
            ->where('contactId', '=', $contactId)
            ->where('plentyId', '=', $plentyId);

        if (!is_null($type))
        {
            $query = $query->where('type', '=', $type);
        }

        return $query->delete();
    }
}