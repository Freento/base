<?php

declare(strict_types=1);

namespace Freento\Base\Model;

use Freento\Base\Model\Feed\ModuleListFeedProvider;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Module\Dir as ModuleDir;
use Magento\Framework\Module\ModuleListInterface;
use Psr\Log\LoggerInterface;

/**
 * Collects the list of Freento_* modules installed via Composer on the store,
 * together with their current version and the latest version published in the
 * Freento module list feed.
 */
class ModuleDataProvider
{
    private const VENDOR_MODULE_PREFIX = 'Freento_';
    private const OWN_MODULE_NAME = 'Freento_Base';
    private const COMPOSER_FILE = 'composer.json';
    private const VERSION_NOT_AVAILABLE = 'N/A';

    /**
     * @var array[]|null
     */
    private ?array $modules = null;

    /**
     * @param ModuleListInterface $moduleList
     * @param ModuleDir $moduleDir
     * @param File $fileDriver
     * @param ModuleListFeedProvider $feedProvider
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly ModuleListInterface $moduleList,
        private readonly ModuleDir $moduleDir,
        private readonly File $fileDriver,
        private readonly ModuleListFeedProvider $feedProvider,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Returns the list of Freento_* modules installed via Composer.
     *
     * @return array[]
     */
    public function getModules(): array
    {
        if ($this->modules !== null) {
            return $this->modules;
        }

        $this->modules = [];

        foreach ($this->moduleList->getNames() as $moduleName) {
            if (strpos($moduleName, self::VENDOR_MODULE_PREFIX) !== 0 || $moduleName === self::OWN_MODULE_NAME) {
                continue;
            }

            $currentVersion = $this->readCurrentVersion($moduleName);
            if ($currentVersion === null) {
                // module is not installed via Composer - out of scope per specification
                continue;
            }

            $feedEntry = $this->feedProvider->getByModule($moduleName);
            $latestVersion = $feedEntry['latest_version'] ?? null;
            $hasUpdate = $latestVersion !== null && version_compare($currentVersion, $latestVersion, '<');

            $this->modules[] = [
                'name' => $feedEntry['title'] ?? $this->humanizeModuleName($moduleName),
                'code' => $moduleName,
                'version' => $currentVersion,
                'latest_version' => $latestVersion ?? self::VERSION_NOT_AVAILABLE,
                'has_update' => $hasUpdate,
                'url' => $feedEntry['url'] ?? null,
            ];
        }

        usort($this->modules, static function (array $a, array $b): int {
            return strcmp($a['name'], $b['name']);
        });

        return $this->modules;
    }

    /**
     * Reads the current version from the module's own composer.json.
     *
     * @param string $moduleName
     * @return string|null
     */
    private function readCurrentVersion(string $moduleName): ?string
    {
        try {
            $path = rtrim($this->moduleDir->getDir($moduleName), '/') . '/' . self::COMPOSER_FILE;
        } catch (\InvalidArgumentException) {
            return null;
        }

        try {
            if (!$this->fileDriver->isExists($path)) {
                return null;
            }

            $content = $this->fileDriver->fileGetContents($path);
        } catch (FileSystemException $e) {
            $this->logger->error('Freento_Base: could not read ' . $path . ': ' . $e->getMessage());

            return null;
        }

        $data = json_decode($content, true);
        if (!is_array($data) || empty($data['version'])) {
            return null;
        }

        return (string)$data['version'];
    }

    /**
     * Turns "Freento_EmailsLog" into "Emails Log".
     *
     * @param string $moduleName
     * @return string
     */
    private function humanizeModuleName(string $moduleName): string
    {
        $shortName = substr($moduleName, strlen(self::VENDOR_MODULE_PREFIX));

        return trim((string)preg_replace('/(?<!^)[A-Z]/', ' $0', $shortName));
    }
}
