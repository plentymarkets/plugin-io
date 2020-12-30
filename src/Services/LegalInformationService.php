<?php //strict

namespace IO\Services;

use IO\Helper\Utils;
use Plenty\Modules\Frontend\LegalInformation\Contracts\LegalInformationRepositoryContract;
use Plenty\Modules\Frontend\LegalInformation\Models\LegalInformation;
use Plenty\Plugin\Application;

/**
 * Service Class LegalInformationService
 *
 * This service class contains functions related to the legal information widgets.
 * All public functions are available in the Twig template renderer.
 *
 * @package IO\Services
 */
class LegalInformationService
{
    private $legalInformationRepo;

    private $lang;

    private $plentyId;

    /**
     * LegalInformationService constructor.
     * @param LegalInformationRepositoryContract $legalInformationRepo
     * @param Application $app
     */
    public function __construct(LegalInformationRepositoryContract $legalInformationRepo, Application $app)
    {
        $this->legalInformationRepo = $legalInformationRepo;
        $this->plentyId = $app->getPlentyId();
        $this->lang = Utils::getLang();
    }

    /**
     * Get the LegalInformation model for the Terms and Conditions
     * @return LegalInformation
     */
    public function getTermsAndConditions(): LegalInformation
    {
        return $this->legalInformationRepo->find($this->plentyId, $this->lang, LegalInformation::TYPE_TERMS_AND_CONDITIONS);
    }

    /**
     * Get the LegalInformation model for the Cancellation Rights
     * @return LegalInformation
     */
    public function getCancellationRights(): LegalInformation
    {
        return $this->legalInformationRepo->find($this->plentyId, $this->lang, LegalInformation::TYPE_CANCELLATION_RIGHTS);
    }

    /**
     * Get the LegalInformation model for the Privacy Policy
     * @return LegalInformation
     */
    public function getPrivacyPolicy(): LegalInformation
    {
        return $this->legalInformationRepo->find($this->plentyId, $this->lang, LegalInformation::TYPE_PRIVACY_POLICY);
    }

    /**
     * Get the LegalInformation model for the Legal Disclosure
     * @return LegalInformation
     */
    public function getLegalDisclosure(): LegalInformation
    {
        return $this->legalInformationRepo->find($this->plentyId, $this->lang, LegalInformation::TYPE_LEGAL_DISCLOSURE);
    }

    /**
     * Get the LegalInformation model for the Withdrawal Form
     * @return LegalInformation
     */
    public function getWithdrawalForm(): LegalInformation
    {
        return $this->legalInformationRepo->find($this->plentyId, $this->lang, LegalInformation::TYPE_WITHDRAWAL_FORM);
    }
}
