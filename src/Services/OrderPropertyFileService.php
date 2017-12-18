<?php

namespace IO\Services;

use Plenty\Modules\Cloud\Storage\Contracts\StorageRepositoryContract;

class OrderPropertyFileService
{
    private $storageRepository;
    
    private $bucket;
    private $key;
    private $tmpKey;
    
    
    public function __construct(StorageRepositoryContract $storageRepository)
    {
        $this->storageRepository = $storageRepository;
        
        $this->bucket = 'plentymarkets-documents';
        $this->key    = 'order_property_files';
        $this->tmpKey = $this->key.'/tmp';
    }
    
    public function uploadFile($fileData)
    {
        if(!is_null($fileData))
        {
            return $this->storageRepository->uploadFile($this->bucket, $this->tmpKey.'/'.$this->generateUniqueHash().'/'.urlencode($fileData['name']), $fileData['tmp_name']);
        }
        
        return null;
    }
    
    public function getFileURL($hash, $filename)
    {
        if(strlen($hash) && strlen($filename))
        {
            $fileURL = $this->storageRepository->getObjectUrl($this->bucket, $this->buildCompleteKey($this->tmpKey, $hash, $filename));
            return $fileURL;
        }
        
        return '';
    }
    
    private function buildCompleteKey($key, $hash, $filename)
    {
        return $key.'/'.$hash.'/'.$filename;
    }
    
    private function generateUniqueHash()
    {
        return sha1(microtime(true));
    }
}