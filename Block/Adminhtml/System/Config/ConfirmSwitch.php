<?php

declare(strict_types=1);

namespace TrustMate\Opinions\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

class ConfirmSwitch extends Field
{
    /**
     * @var SecureHtmlRenderer
     */
    private $secureRenderer;

    public function __construct(
        Context $context,
        SecureHtmlRenderer $secureRenderer,
        array $data = []
    ) {
        $this->secureRenderer = $secureRenderer;

        parent::__construct($context, $data, $secureRenderer);
    }

    protected function _getElementHtml(AbstractElement $element): string
    {
        $html = parent::_getElementHtml($element);
        $id = $this->_escaper->escapeJs($element->getHtmlId());

        $message = $this->_escaper->escapeJs((string) __('Are you sure you want to switch this option?'));
        $script = "
            require(['jquery'], function($) {
                $('#$id').on('change', function(e) {
                    if (!confirm('$message')) {
                        e.preventDefault();
                        // Cofnięcie zmiany
                        this.value = this.getAttribute('data-old-value');
                    } else {
                        this.setAttribute('data-old-value', this.value);
                    }
                });

                $('#$id').attr('data-old-value', $('#$id').val());
            });
        ";

        return $html . $this->secureRenderer->renderTag('script', [], $script, false);
    }
}
