<?php

declare(strict_types=1);

namespace Freento\Base\Block\Adminhtml\System\Config;

use Freento\Base\Model\Notifications\NotificationsContentProvider;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Renders the "Notifications" group: promo content loaded server-side from freento.com.
 */
class Notifications extends Field
{
    /**
     * @var string
     */
    protected $_template = 'Freento_Base::system/config/notifications.phtml';

    /**
     * @param Context $context
     * @param NotificationsContentProvider $notificationsContentProvider
     * @param array $data
     */
    public function __construct(
        Context $context,
        private readonly NotificationsContentProvider $notificationsContentProvider,
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
     * Returns the notifications content to render.
     *
     * @return string
     */
    public function getContent(): string
    {
        return $this->notificationsContentProvider->getContent();
    }
}
