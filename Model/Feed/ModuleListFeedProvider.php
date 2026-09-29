<?php

declare(strict_types=1);

namespace Freento\Base\Model\Feed;

use Freento\Base\Helper\Config;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Serialize\Serializer\Json;
use Psr\Log\LoggerInterface;

/**
 * Loads and caches the Freento module list feed (freento.com/module-list.json).
 *
 * The very same feed is used to determine the latest available version of already
 * installed modules ("Products" group) and to recommend modules that are not
 * installed yet ("Recommended for you" group).
 */
class ModuleListFeedProvider
{
    private const CACHE_KEY = 'freento_base_module_list_feed';
    private const CACHE_LIFETIME = 86400;
    private const REQUEST_TIMEOUT = 3;

    /**
     * @var array[]|null
     */
    private ?array $entries = null;

    /**
     * @var array[]|null
     */
    private ?array $entriesByModule = null;

    /**
     * @param Config $config
     * @param Curl $curl
     * @param CacheInterface $cache
     * @param Json $json
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly Config $config,
        private readonly Curl $curl,
        private readonly CacheInterface $cache,
        private readonly Json $json,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Returns all entries of the feed, as published.
     *
     * @return array[]
     */
    public function getEntries(): array
    {
        if ($this->entries === null) {
            $this->entries = $this->loadEntries();
        }

        return $this->entries;
    }

    /**
     * Returns the feed entry for a given Magento module name (e.g. "Freento_EmailsLog"), if any.
     *
     * @param string $moduleName
     * @return array|null
     */
    public function getByModule(string $moduleName): ?array
    {
        if ($this->entriesByModule === null) {
            $this->entriesByModule = [];
            foreach ($this->getEntries() as $entry) {
                if (!empty($entry['module'])) {
                    $this->entriesByModule[$entry['module']] = $entry;
                }
            }
        }

        return $this->entriesByModule[$moduleName] ?? null;
    }

    /**
     * Loads the feed, using the cache when available.
     *
     * @return array[]
     */
    private function loadEntries(): array
    {
        $cached = $this->cache->load(self::CACHE_KEY);
        if ($cached !== false) {
            try {
                return $this->json->unserialize($cached);
            } catch (\InvalidArgumentException $e) {
                $this->logger->error('Freento_Base: corrupted module list feed cache entry: ' . $e->getMessage());
            }
        }

        $data = $this->fetchFromUrl();
        $this->cache->save($this->json->serialize($data), self::CACHE_KEY, [], self::CACHE_LIFETIME);

        return $data;
    }

    /**
     * Fetches the feed from the configured URL.
     *
     * @return array[]
     */
    private function fetchFromUrl(): array
    {
        $url = $this->config->getModuleListUrl();
        if (!$url) {
            return [];
        }

        try {
            $this->curl->setTimeout(self::REQUEST_TIMEOUT);
            $this->curl->get($url);

            if ($this->curl->getStatus() === 200) {
                $decoded = $this->json->unserialize($this->curl->getBody());

                return is_array($decoded) ? array_values(array_filter($decoded, 'is_array')) : [];
            }
        } catch (\Throwable $e) {
            $this->logger->error('Freento_Base: failed to load module list feed: ' . $e->getMessage());
        }

        return [];
    }
}
