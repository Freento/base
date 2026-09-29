<?php

declare(strict_types=1);

namespace Freento\Base\Block\Adminhtml\System\Config;

use Freento\Base\Helper\Config as FreentoConfig;
use Freento\Base\Model\Recommended\RecommendedModuleListProvider;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Renders the "Recommended for you" group: free Freento modules not yet installed
 * on this store, followed by a static promo block linking to GitHub.
 */
class Recommended extends Field
{
    /**
     * @var string
     */
    protected $_template = 'Freento_Base::system/config/recommended.phtml';

    /**
     * @param Context $context
     * @param RecommendedModuleListProvider $recommendedModuleListProvider
     * @param FreentoConfig $freentoConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        private readonly RecommendedModuleListProvider $recommendedModuleListProvider,
        private readonly FreentoConfig $freentoConfig,
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
     * Returns the list of recommended modules not yet installed on this store.
     *
     * @return array[]
     */
    public function getModules(): array
    {
        return $this->recommendedModuleListProvider->getModules();
    }

    /**
     * Returns the Freento GitHub organization URL.
     *
     * @return string
     */
    public function getGithubUrl(): string
    {
        return $this->freentoConfig->getGithubUrl();
    }
}
