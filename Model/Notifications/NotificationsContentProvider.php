<?php

declare(strict_types=1);

namespace Freento\Base\Model\Notifications;

use Freento\Base\Helper\Config;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\HTTP\Client\Curl;
use Psr\Log\LoggerInterface;

/**
 * Loads the promo/notifications HTML block content published on freento.com.
 */
class NotificationsContentProvider
{
    private const CACHE_KEY = 'freento_base_notifications_content';
    private const CACHE_LIFETIME = 86400;
    private const REQUEST_TIMEOUT = 3;

    /**
     * @param Config $config
     * @param Curl $curl
     * @param CacheInterface $cache
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly Config $config,
        private readonly Curl $curl,
        private readonly CacheInterface $cache,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Returns the cached (or freshly fetched) notifications content.
     *
     * @return string
     */
    public function getContent(): string
    {
        $cached = $this->cache->load(self::CACHE_KEY);
        if ($cached !== false) {
            return $cached;
        }

        $content = $this->fetchContent();
        $this->cache->save($content, self::CACHE_KEY, [], self::CACHE_LIFETIME);

        return $content;
    }

    /**
     * Fetches the notifications content from the configured URL.
     *
     * @return string
     */
    private function fetchContent(): string
    {
        $url = $this->config->getNotificationsUrl();
        if (!$url) {
            return '';
        }

        try {
            $this->curl->setTimeout(self::REQUEST_TIMEOUT);
            $this->curl->get($url);

            if ($this->curl->getStatus() === 200) {
                return $this->curl->getBody();
            }
        } catch (\Throwable $e) {
            $this->logger->error('Freento_Base: failed to load notifications content: ' . $e->getMessage());
        }

        return '';
    }
}
