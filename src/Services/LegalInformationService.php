<?php //strict

namespace IO\Services;

use Plenty\Modules\Frontend\LegalInformation\Contracts\LegalInformationRepositoryContract;
use Plenty\Modules\Frontend\LegalInformation\Models\LegalInformation;
use Plenty\Plugin\Application;
use IO\Services\SessionStorageService;


class LegalInformationService
{
    private $legalInformationRepo;
    
    private $lang;
    
    private $plentyId;
    
    private $pageKeys = [
        'terms_and_conditions' => '/gtc',
        'cancellation_rights'  => '/cancellation-rights',
        'privacy_policy'       => '/privacy-policy',
        'legal_disclosure'     => '/legal-disclosure',
        'cancellation_form'    => '/cancellation-form'
    ];
    
    public function __construct(LegalInformationRepositoryContract $legalInformationRepo, Application $app, SessionStorageService $sessionStorage)
    {
        $this->legalInformationRepo = $legalInformationRepo;
        $this->plentyId = $app->getPlentyId();
        $this->lang = $sessionStorage->getLang();
    }
    
    public function getTermsAndConditions():LegalInformation
    {
        return $this->legalInformationRepo->find($this->plentyId, $this->lang, LegalInformation::TYPE_TERMS_AND_CONDITIONS);
    }
    
    public function getCancellationRights():LegalInformation
    {
        return $this->legalInformationRepo->find($this->plentyId, $this->lang, LegalInformation::TYPE_CANCELLATION_RIGHTS);
    }
    
    public function getPrivacyPolicy():LegalInformation
    {
        return $this->legalInformationRepo->find($this->plentyId, $this->lang, LegalInformation::TYPE_PRIVACY_POLICY);
    }
    
    public function getLegalDisclosure():LegalInformation
    {
        return $this->legalInformationRepo->find($this->plentyId, $this->lang, LegalInformation::TYPE_LEGAL_DISCLOSURE);
    }

    public function getWithdrawalForm():LegalInformation
    {
        return $this->legalInformationRepo->find($this->plentyId, $this->lang, LegalInformation::TYPE_WITHDRAWAL_FORM);
    }
    
    public function getLegalPageURL($key)
    {
        $url = '';
        if(strlen($key))
        {
            /** @var TemplateConfigService $templateConfigService */
            $templateConfigService = pluginApp(TemplateConfigService::class);
            $categoryId = $templateConfigService->get('pages.'.$key, null);
            
            if(!is_null($categoryId) && (int)$categoryId > 0)
            {
                /** @var CategoryService $categoryService */
                $categoryService = pluginApp(CategoryService::class);
                $categoryURL = $categoryService->getURLById((int)$categoryId);
                if(strlen($categoryURL))
                {
                    $url = '/'.$categoryURL;
                }
                else
                {
                    $url = $this->getLegalFallbackURL($key);
                }
            }
            else
            {
                $url = $this->getLegalFallbackURL($key);
            }
        }
        
        return $url;
    }
    
    private function getLegalFallbackURL($key)
    {
        if(array_key_exists($key, $this->pageKeys))
        {
            return $this->pageKeys[$key];
        }
        
        return '';
    }
}