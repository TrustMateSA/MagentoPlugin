<?php

declare(strict_types=1);

namespace TrustMate\Opinions\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class ConfirmSwitch extends Field
{
    protected function _getElementHtml(AbstractElement $element): string
    {
        $html = parent::_getElementHtml($element);
        $id = $element->getHtmlId();

        $message = __('Are you sure you want to switch this option?');
        $html .= "
            <script>
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
            </script>
        ";

        return $html;
    }
}
