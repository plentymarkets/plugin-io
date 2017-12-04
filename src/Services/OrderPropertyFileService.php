<?php

namespace IO\Services;

use Plenty\Plugin\Application;
use Plenty\Modules\Cloud\Storage\Contracts\StorageRepositoryContract;

class OrderPropertyFileService
{
    private $app;
    private $storageRepository;
    
    public function __construct(Application $app, StorageRepositoryContract $storageRepository)
    {
        $this->app = $app;
        $this->storageRepository = $storageRepository;
    }
    
    public function uploadFile($fileData)
    {
        if(!is_null($fileData))
        {
            return $this->storageRepository->uploadObject('plentymarkets-documents', 'order_property_files/'.$this->app->getPlentyId().'/'.sha1(microtime(true)), $fileData);
        }
        
        return null;
    }
}