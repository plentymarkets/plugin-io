<?php

namespace IO\Services;

use Plenty\Modules\Cloud\Storage\Contracts\StorageRepositoryContract;
use IO\Helper\StringUtils;

class OrderPropertyFileService
{
    private $storageRepository;
    private $stringUtils;
    
    private $bucket;
    private $key;
    private $tmpKey;
    
    
    public function __construct(StorageRepositoryContract $storageRepository, StringUtils $stringUtils)
    {
        $this->storageRepository = $storageRepository;
        $this->stringUtils = $stringUtils;
        
        $this->bucket = 'plentymarkets-documents';
        $this->key    = 'order_property_files';
        $this->tmpKey = $this->key.'/tmp';
    }
    
    public function uploadFile($fileData)
    {
        if(!is_null($fileData))
        {
            return $this->storageRepository->uploadFile($this->bucket, $this->tmpKey.'/'.$this->generateUniqueHash().'/'.$this->stringUtils->string4URL($fileData['name']), $fileData['tmp_name']);
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
    
    public function copyFromTemp($fileURL)
    {
        $targetFileURL = str_replace('tmp/', '', $fileURL);
        $result = $this->storageRepository->copyObject($this->bucket, $fileURL, $this->bucket, $targetFileURL);
        return $targetFileURL;
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