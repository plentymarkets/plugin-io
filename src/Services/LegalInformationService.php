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
}