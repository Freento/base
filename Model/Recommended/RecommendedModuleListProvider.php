<?php

declare(strict_types=1);

namespace Freento\Base\Model\Recommended;

use Freento\Base\Model\Feed\ModuleListFeedProvider;
use Magento\Framework\Module\ModuleListInterface;

/**
 * Returns the modules from the Freento module list feed that are not installed
 * on the current store yet.
 */
class RecommendedModuleListProvider
{
    /**
     * @param ModuleListFeedProvider $feedProvider
     * @param ModuleListInterface $moduleList
     */
    public function __construct(
        private readonly ModuleListFeedProvider $feedProvider,
        private readonly ModuleListInterface $moduleList
    ) {
    }

    /**
     * Returns recommended modules that are not installed on this store yet.
     *
     * @return array[]
     */
    public function getModules(): array
    {
        $result = [];

        foreach ($this->feedProvider->getEntries() as $entry) {
            if (empty($entry['module']) || empty($entry['title'])) {
                continue;
            }

            if ($this->moduleList->has($entry['module'])) {
                continue;
            }

            $result[] = [
                'title' => $entry['title'],
                'description' => $entry['description'] ?? '',
                'url' => $entry['url'] ?? '',
            ];
        }

        return $result;
    }
}
