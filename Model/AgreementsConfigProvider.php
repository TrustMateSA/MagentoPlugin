<?php
/**
 * @package   TrustMate\Opinions
 * @copyright 2020 TrustMate
 */

namespace TrustMate\Opinions\Model;

use Magento\CheckoutAgreements\Model\AgreementsProvider;
use Magento\CheckoutAgreements\Model\AgreementsConfigProvider as CoreAgreementsConfigProvider;
use Magento\Store\Model\ScopeInterface;
use TrustMate\Opinions\Helper\Data;

/**
 * Class CheckoutAgreementsRepository
 * @package TrustMate\Opinions\Model
 */
class AgreementsConfigProvider extends CoreAgreementsConfigProvider
{
    /**
     * @inheritdoc
     */
    protected function getAgreementsConfig()
    {
        $agreementConfiguration = [];
        $isAgreementsEnabled = $this->scopeConfiguration->isSetFlag(
            AgreementsProvider::PATH_ENABLED,
            ScopeInterface::SCOPE_STORE
        );

        $agreementsList = $this->checkoutAgreementsRepository->getList();
        $agreementConfiguration['isEnabled'] = (bool)($isAgreementsEnabled && count($agreementsList) > 0);

        foreach ($agreementsList as $agreement) {
            $agreementConfiguration['agreements'][] = [
                'content' => $agreement->getIsHtml()
                    ? $agreement->getContent()
                    : nl2br($this->escaper->escapeHtml($agreement->getContent())),
                'checkboxText' => $this->escaper->escapeHtml($agreement->getCheckboxText()),
                'mode' => $agreement->getMode(),
                'agreementId' => $agreement->getAgreementId(),
                'required' => $agreement->getName() != Data::TRUSTMATE_CODE ? 'required-entry' : ''
            ];
        }

        return $agreementConfiguration;
    }
}