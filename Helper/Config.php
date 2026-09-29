<?php

declare(strict_types=1);

namespace Freento\Base\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

/**
 * Provides access to the module's own configuration values.
 *
 * Feed URLs are hardcoded defaults in etc/config.xml (per specification) and are not
 * exposed as editable fields in System Configuration.
 */
class Config extends AbstractHelper
{
    private const XML_PATH_MODULE_LIST_URL = 'freento_base/feeds/module_list_url';
    private const XML_PATH_NOTIFICATIONS_URL = 'freento_base/feeds/notifications_url';
    private const XML_PATH_GITHUB_URL = 'freento_base/feeds/github_url';

    /**
     * Returns the URL of the Freento module list JSON feed (module versions + recommendations).
     *
     * @return string
     */
    public function getModuleListUrl(): string
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_MODULE_LIST_URL,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Returns the URL of the notifications HTML content block.
     *
     * @return string
     */
    public function getNotificationsUrl(): string
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_NOTIFICATIONS_URL,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Returns the URL of the Freento GitHub organization.
     *
     * @return string
     */
    public function getGithubUrl(): string
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_GITHUB_URL,
            ScopeInterface::SCOPE_STORE
        );
    }
}
