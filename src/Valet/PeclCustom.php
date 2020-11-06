<?php

namespace Valet;

use DomainException;

class PeclCustom extends AbstractPecl
{

    // Extensions.
    const IONCUBE_LOADER_EXTENSION = 'ioncube_loader_dar';

    // File extensions.
    const TAR_GZ_FILE_EXTENSION = '.tar.gz';

    /**
     * Supported pecl extensions for the PECL manager. There are 2 types of extensions supported: zend_extensions and
     * extensions. Not all extension are available through PECL. This class handles custom packages that are not managed
     * by PECL.
     *
     * CUSTOM example:
     *
     * Packages that are not available through PECL can be downloaded. Every PHP version requires a download link
     * so the manager can download the .so file for the active PHP version.
     *
     * .so files can be packages by different file extensions. Use the file_extension key to set the file extension
     * of the downloaded file.
     *
     * Because packages files can contain a directory with a different name the manager will be unable to find the
     * unpacked folder. Set packaged_directory so that it equals the folder name after unpacking the folder.
     *
     * Downloaded extensions can either be of type zend_extension or extension. Because the manager does not know
     * what kind of extension the downloaded.so file is one defines extension_type with one of the extension types.
     *
     * To check if the extension is active the manager uses extension_php_name. The manager check of php -m returns the
     * extension name.
     *
     * @formatter:off
     *
     * 'extension_key_name' => [
     *    '7.4' => 'https://example.com/packagename.extension',
     *    '7.3' => 'https://example.com/packagename.extension',
     *    '7.2' => 'https://example.com/packagename.extension',
     *    '7.1' => 'https://example.com/packagename.extension',
     *    '7.0' => 'https://example.com/packagename.extension',
     *    '5.6' => 'https://example.com/packagename.extension',
     *    'file_extension' => self::TAR_GZ_FILE_EXTENSION,
     *    'extension_type' => self::ZEND_EXTENSION_TYPE,
     *    'extension_php_name' => 'the ionCube PHP Loader',
     * ]
     *
     * @formatter:on
     */
    const EXTENSIONS = [
        self::IONCUBE_LOADER_EXTENSION => [
            '7.4' => 'https://downloads.ioncube.com/loader_downloads/ioncube_loaders_dar_x86-64.tar.gz',
            '7.3' => 'https://downloads.ioncube.com/loader_downloads/ioncube_loaders_dar_x86-64.tar.gz',
            '7.2' => 'https://downloads.ioncube.com/loader_downloads/ioncube_loaders_dar_x86-64.tar.gz',
            '7.1' => 'https://downloads.ioncube.com/loader_downloads/ioncube_loaders_dar_x86-64.tar.gz',
            '7.0' => 'https://downloads.ioncube.com/loader_downloads/ioncube_loaders_dar_x86-64.tar.gz',
            '5.6' => 'https://downloads.ioncube.com/loader_downloads/ioncube_loaders_dar_x86-64.tar.gz',
            'file_extension' => self::TAR_GZ_FILE_EXTENSION,
            'packaged_directory' => 'ioncube',
            'default' => false,
            'extension_type' => self::ZEND_EXTENSION_TYPE,
            'extension_php_name' => 'the ionCube PHP Loader'
        ]
    ];

    /**
     * @inheritdoc
     */
    public function __construct(CommandLine $cli, Filesystem $files)
    {
        parent::__construct($cli, $files);
    }

    /**
     * @inheritdoc
     */
    public function installExtensions(bool $onlyDefaults = true): void
    {
        info("[PECL-CUSTOM] Installing extensions");
        foreach (self::EXTENSIONS as $extension => $versions) {
            if ($onlyDefaults && $this->isDefaultExtension($extension) === false) {
                continue;
            }

            $this->installExtension($extension);
            $this->enableExtension($extension);
        }
    }

    /**
     * Install a single extension if not already installed, default is true and has a version for this PHP version.
     * If version equals false the extension will never be installed for this PHP version.
     *
     * @param string $extension
     *    The extension key name.
     * @return bool
     */
    public function installExtension(string $extension): bool
    {
        $version = $this->getVersion($extension);

        if ($this->isInstalled($extension)) {
            output("\t$extension is already installed, skipping...");

            return false;
        }

        if (!$version) {
            return false;
        }

        $this->install($extension, $version);

        return true;
    }

    /**
     * Install a single custom extension.
     *
     * @param string $extension
     * @param string $url
     */
    public function install(string $extension, string $url): void
    {
        if (is_null($url)) {
            $phpVersion = $this->getPhpVersion();
            warning("[PECL-CUSTOM] $extension is not available for PHP $phpVersion.");

            return;
        }

        // Get file name from url
        $urlSplit = explode('/', $url);
        $fileName = $urlSplit[count($urlSplit) - 1];

        // Check if .so is available
        $extensionDirectory = $this->getExtensionDirectory();
        $extensionAlias = $this->getExtensionAlias($extension);

        if ($this->extensionIsMissing($extensionDirectory)) {
            info("[PECL-CUSTOM] $extension is not available from PECL, downloading from: $url");

            $this->downloadExtension(
                $extension,
                $url,
                $fileName,
                $extensionAlias,
                $extensionDirectory
            );
        } else {
            info("[PECL-CUSTOM] $extensionAlias found in $extensionDirectory skipping download..");
        }

        output("\t$extension successfully installed");
    }

    /**
     * Download and unpack a custom extension.
     *
     * @param string $extension
     *    The extension key name.
     * @param string $url
     *    The extension url
     * @param string $fileName
     *    The file name of the packaged directory.
     * @param string $extensionAlias
     *    The extension alias which equals the name of the .so file. E.G: xdebug.so
     * @param string $extensionDirectory
     *    The directory where the .so file needs to be placed.
     */
    public function downloadExtension(string $extension, string $url, string $fileName, string $extensionAlias, string $extensionDirectory): void
    {
        $unpackagedDirectory = $this->getPackagedDirectory($extension);

        // Download and unzip
        $this->cli->passthru("cd /tmp && sudo -u " . user() . " curl -O $url");

        // Unpackage the file using file extension.
        $fileExtension = $this->getFileExtension($extension);
        switch ($fileExtension) {
            case self::TAR_GZ_FILE_EXTENSION:
                info('[PECL-CUSTOM] Unpackaging .tar.gz:');
                $this->cli->passthru("cd /tmp && sudo -u " . user() . " tar -xvzf $fileName");
                break;
            default:
                throw new DomainException("File extension $fileExtension is not supported yet!");
        }

        // Search for extension file in unpackaged directory using the extension alias.
        $files = $this->files->scandir("/tmp/$unpackagedDirectory");
        if (in_array($extensionAlias, $files)) {
            info("[PECL-CUSTOM] $extensionAlias was found, moving to extension directory: $extensionDirectory");
            $this->cli->runAsUser("cp /tmp/$unpackagedDirectory/$extensionAlias $extensionDirectory/");
        } else {
            throw new DomainException("$extensionAlias could not be found!");
        }

        // Remove artifacts from /tmp folder.
        $this->cli->runAsUser("rm /tmp/$fileName");
        $this->cli->runAsUser("rm -r /tmp/$unpackagedDirectory");
    }

    /**
     * Enable an single extension if not already enabled, default is true and has a version for this PHP version.
     *
     * If version is false the extension will never be installed for this PHP version.
     *
     * @param string $extension
     * @return bool
     */
    public function enableExtension(string $extension): bool
    {
        if ($this->extensionIsMissing($extension)) {
            output("\t$extension is missing, ");

            if ($this->isEnabled($extension)) {
                output("disabling...");

                $this->disable($extension);
            }

            return false;
        }

        if ($this->isEnabled($extension)) {
            output("\t$extension is already enabled, skipping...");

            return false;
        }

        $this->enable($extension);

        return true;
    }

    /**
     * Install a single custom extension.
     *
     * @param string $extension
     */
    public function enable(string $extension): void
    {
        // Install php.ini directive.
        $extensionAlias = $this->getExtensionAlias($extension);
        $extensionType = $this->getExtensionType($extension);
        $phpIniPath = $this->getPhpIniPath();
        $directive = $extensionType . '="' . $extensionAlias . '"';
        $phpIniFile = $this->files->get($phpIniPath);
        $this->saveIniFile($phpIniPath, $directive . "\n" . $phpIniFile);

        output("\t$extension successfully enabled");
    }

    /**
     * @inheritdoc
     */
    public function uninstallExtensions(): void
    {
        info("[PECL-CUSTOM] Removing extensions");
        foreach (self::EXTENSIONS as $extension => $versions) {
            $this->uninstallExtension($extension);
        }
    }

    /**
     * Uninstall an extension if installed, by version.
     *
     * @param string $extension
     * @return bool
     */
    public function uninstallExtension(string $extension): bool
    {
        $version = $this->getVersion($extension);
        if ($this->isEnabled($extension)) {
            $this->disable($extension);
        }
        if ($this->isInstalled($extension)) {
            $this->uninstall($extension, $version);

            return true;
        }

        return false;
    }

    /**
     * Disable a single extension.
     *
     * @param string $extension
     */
    public function disable(string $extension): void
    {
        $this->removeIniDefinition($extension);
    }

    /**
     * Uninstall a single extension.
     *
     * @param string $extension
     *    The extension key name.
     * @param null $version
     */
    private function uninstall(string $extension, $version = null): void
    {
        // Check if .so is available
        $extensionDirectory = $this->getExtensionDirectory();
        $extensionAlias = $this->getExtensionAlias($extension);
        $filePath = $extensionDirectory . '/' . $extensionAlias;
        if ($this->files->exists($filePath)) {
            $this->cli->runAsUser("rm $filePath");
            output("\t$extension successfully uninstalled.");
        } else {
            output("\t$extension was already removed!");
        }
    }

    /**
     * @inheritdoc
     */
    public function isInstalled(string $extension): bool
    {
        $extensionDirectory = $this->getExtensionDirectory();
        $extensionAlias = $this->getExtensionAlias($extension);

        return $this->files->exists($extensionDirectory . '/' . $extensionAlias);
    }

    /**
     * Replace all definitions of the .so file to the given extension within the php.ini file.
     *
     * @param string $extension
     *
     * @return void
     */
    private function removeIniDefinition(string $extension): void
    {
        $phpIniPath = $this->getPhpIniPath();
        $alias = $this->getExtensionAlias($extension);
        $phpIniFile = $this->files->get($phpIniPath);
        $phpIniFile = preg_replace('/;?(zend_extension|extension)\=".*' . $alias . '"/', '', $phpIniFile);
        $this->saveIniFile($phpIniPath, $phpIniFile);
        output("\t$extension successfully disabled");
    }

    /**
     * Some extensions require to be loaded before others. Because the order is important within the php.ini file one
     * should always use this method before saving the php.ini file. This method makes sure that .so definitions within
     * the php.ini file are always ordered correctly.
     *
     * @param $phpIniPath
     * @param $phpIniFile
     *
     * @return void
     */
    public function saveIniFile(string $phpIniPath, string $phpIniFile): void
    {
        // Ioncube loader requires to be the first zend_extension loaded from the php.ini
        // before saving the ini file check if ioncube is enabled and move it to the top of the file.
        $ioncubeLoader = $this->getExtensionAlias(self::IONCUBE_LOADER_EXTENSION);
        if (preg_match('/(zend_extension|extension)\="(.*' . $ioncubeLoader . ')"/', $phpIniFile, $matches)) {
            $phpIniFile = preg_replace('/(zend_extension|extension)\="(.*' . $ioncubeLoader . ')"/', '', $phpIniFile);
            $phpIniFile = $matches[1] . '="' . $matches[2] . '"' . "\n" . $phpIniFile;
        }

        $this->files->putAsUser($phpIniPath, $phpIniFile);
    }

    /**
     * @param string $extension
     *
     * @return bool
     */
    public function isEnabled(string $extension): bool
    {
        $alias = $this->getExtensionName($extension);
        $extensions = explode("\n", $this->cli->runAsUser("php -m | grep '$alias'"));

        return in_array($alias, $extensions);
    }

    /**
     * Whether or not this extension should be enabled by default. Is set by setting the
     * 'default' key to true within the extensions configuration.
     *
     * @param string $extension
     * @return bool
     */
    private function isDefaultExtension(string $extension): bool
    {
        if (array_key_exists('default', self::EXTENSIONS[$extension])) {
            return false;
        } elseif (array_key_exists('default', self::EXTENSIONS[$extension]) === false) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get the extension alias for the extension. Should return the alias of the .so file without the .so extension.
     * E.G: apcu, apc, xdebug, geoip, etc...
     *
     * @param string $extension
     * @return string
     */
    protected function getExtensionAlias(string $extension): string
    {
        switch ($extension) {
            case self::IONCUBE_LOADER_EXTENSION:
                return self::IONCUBE_LOADER_EXTENSION . '_' . $this->getPhpVersion() . '.so';
            default:
                return $extension;
        }
    }

    /**
     * Get the version of the extension supported by the current PHP version.
     *
     * @param string $extension
     * @return null|string
     */
    private function getVersion(string $extension): ?string
    {
        $phpVersion = $this->getPhpVersion();

        if (array_key_exists($phpVersion, self::EXTENSIONS[$extension])) {
            return self::EXTENSIONS[$extension][$phpVersion];
        }

        return null;
    }

    /**
     * Get the file extension of the downloaded custom extension package.
     *
     * @param string $extension
     * @return string
     */
    private function getFileExtension(string $extension): string
    {
        if (array_key_exists('file_extension', self::EXTENSIONS[$extension])) {
            return self::EXTENSIONS[$extension]['file_extension'];
        }
        throw new DomainException('file_extension key is required for custom PECL packages');
    }

    /**
     * Get the directory within the packaged archive.
     *
     * @param string $extension
     *    The extension key name.
     * @return string
     */
    private function getPackagedDirectory(string $extension): string
    {
        if (array_key_exists('packaged_directory', self::EXTENSIONS[$extension])) {
            return self::EXTENSIONS[$extension]['packaged_directory'];
        }
        throw new DomainException('packaged_directory key is required for custom PECL packages');
    }

    /**
     * Get the extension name for the custom extension. Used for checking php -m output.
     *
     * @param string $extension
     * @return string
     */
    private function getExtensionName(string $extension): string
    {
        if (array_key_exists('extension_php_name', self::EXTENSIONS[$extension])) {
            return self::EXTENSIONS[$extension]['extension_php_name'];
        }
        throw new DomainException('extension_php_name key is required for custom PECL packages');
    }

    /**
     * Checks if the Extension is missing
     *
     * @param string $extension
     *
     * @return bool
     */
    public function extensionIsMissing(string $extension): bool
    {
        $extensionDirectory = $this->getExtensionDirectory();
        $extensionAlias = $this->getExtensionAlias($extension);

        $path = sprintf("%s/%s", $extensionDirectory, $extensionAlias);

        return $this->files->exists($path) === false;
    }
}
