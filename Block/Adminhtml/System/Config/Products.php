<?php

declare(strict_types=1);

namespace Freento\Base\Block\Adminhtml\System\Config;

use Freento\Base\Model\ModuleDataProvider;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Renders the "Products" table: Freento modules installed via Composer, with their
 * current and latest available version.
 */
class Products extends Field
{
    /**
     * @var string
     */
    protected $_template = 'Freento_Base::system/config/products.phtml';

    /**
     * @param Context $context
     * @param ModuleDataProvider $moduleDataProvider
     * @param array $data
     */
    public function __construct(
        Context $context,
        private readonly ModuleDataProvider $moduleDataProvider,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Renders the block content as a single full-width row of the fieldset table.
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element): string
    {
        return sprintf(
            '<tr id="row_%s"><td colspan="5">%s</td></tr>',
            $element->getHtmlId(),
            $this->toHtml()
        );
    }

    /**
     * Returns the list of Freento modules installed via Composer.
     *
     * @return array[]
     */
    public function getModules(): array
    {
        return $this->moduleDataProvider->getModules();
    }
}
