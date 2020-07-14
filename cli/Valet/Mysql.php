<?php

namespace Valet;

use DomainException;
use mysqli;
use MYSQLI_ASSOC;

//@TODO: refactor
class Mysql
{
    const MYSQL_CONF_DIR = '/usr/local/etc';
    const MYSQL_CONF = '/usr/local/etc/my.cnf';
    const MAX_FILES_CONF = '/Library/LaunchDaemons/limit.maxfiles.plist';
    const MYSQL_DIR = '/usr/local/var/mysql';
    const MYSQL_ROOT_PASSWORD = 'root';

    const MYSQL_FORMULA_NAME = 'mysql@';
    const MYSQL_57_VERSION = '5.7';
    const MARIA_DB = 'mariadb';
    const MYSQL_80_VERSION = '8.0';
    const SUPPORTED_MYSQL_FORMULAE = [
        'mysql5.7' => self::MYSQL_FORMULA_NAME . self::MYSQL_57_VERSION,
        'mysql8.0' => self::MYSQL_FORMULA_NAME . self::MYSQL_80_VERSION,
        'mariadb' => self::MARIA_DB
    ];
    const MYSQL_57_FORMULA = self::MYSQL_FORMULA_NAME . self::MYSQL_57_VERSION;
    const MYSQL_DEFAULT_FORMULA = self::MYSQL_57_FORMULA;

    public $brew;
    public $cli;
    public $files;
    public $configuration;
    public $site;
    public $systemDatabase = ['sys', 'performance_schema', 'information_schema', self::MYSQL_DEFAULT_FORMULA];
    /**
     * @var Mysqli
     */
    protected $link = false;

    /**
     * Create a new instance.
     *
     * @param Brew          $brew
     * @param CommandLine   $cli
     * @param Filesystem    $files
     * @param Configuration $configuration
     * @param Site          $site
     */
    public function __construct(
        Brew $brew,
        CommandLine $cli,
        Filesystem $files,
        Configuration $configuration,
        Site $site
    ) {
        $this->cli = $cli;
        $this->brew = $brew;
        $this->site = $site;
        $this->files = $files;
        $this->configuration = $configuration;
    }

    /**
     * Install the service.
     *
     * @param $type
     */
    public function install($type = self::MYSQL_DEFAULT_FORMULA)
    {
        $this->verifyType($type);
        $currentlyInstalled = $this->installedVersion();
        if ($currentlyInstalled) {
            $type = $currentlyInstalled;
        }

        $this->removeConfiguration($type);
        $this->files->copy(__DIR__ . '/../stubs/limit.maxfiles.plist', static::MAX_FILES_CONF);
        $this->cli->quietly('launchctl load -w ' . static::MAX_FILES_CONF);

        if (!$currentlyInstalled) {
            $this->brew->installOrFail($type);
        }

        $this->stop();
        $this->installConfiguration($type);
        $this->restart();

        // If formula is versioned link the formula as the binary.
        if (strpos($type, '@')) {
            $this->cli->runAsUser("brew link $type --force", function () {
                warning('Failed linking MySQL!');
            });
        }
    }

    /**
     * Switch between versions of installed MySQL. Switch to the provided version.
     *
     * @param $version
     *
     * @return void
     */
    public function switchTo(string $version): void
    {
        $currentVersion = $this->installedVersion();

        if (!\array_key_exists($version, self::SUPPORTED_MYSQL_FORMULAE)) {
            throw new DomainException("This version of MySQL is not available. The following versions are available: " . implode(
                ' ',
                \array_keys(self::SUPPORTED_MYSQL_FORMULAE)
            ));
        }

        // If the current version equals that of the current MySQL version, do not switch.
        if ($version === $currentVersion) {
            info('Already on this version');
            return;
        }

        $installed = $this->brew->installed(self::SUPPORTED_MYSQL_FORMULAE[$version]);
        if (!$installed) {
            $this->brew->ensureInstalled(self::SUPPORTED_MYSQL_FORMULAE[$version]);
        }

        // Unlink the current PHP version.
        if (!$this->unlinkMysql($currentVersion)) {
            return;
        }

        $this->stop();
        $this->install();


        $shell = \preg_replace('/\s+/', '', $this->cli->runAsUser('echo $SHELL'));
        $mysqlPath = \preg_replace('/\s+/', '', $this->cli->runAsUser('which mysql'));
        $this->cli->runAsUser(\sprintf('export PATH="%s:$PATH" >> %s', $mysqlPath, $shell));
        $this->cli->runAsUser('source ' . $shell);

        info("Valet is now using " . self::SUPPORTED_MYSQL_FORMULAE[$version]);
    }

    /**
     * check if type is valid.
     *
     * @param $type
     *
     * @throws DomainException
     */
    public function verifyType($type)
    {
        if (!\in_array($type, $this->supportedVersions())) {
            throw new DomainException('Invalid Mysql type given. Available: ' . \implode('/', self::SUPPORTED_MYSQL_FORMULAE));
        }
    }

    /**
     * Get supported version of database.
     *
     * @return array
     */
    public function supportedVersions()
    {
        return self::SUPPORTED_MYSQL_FORMULAE;
    }

    /**
     * Get installed version of database system.
     *
     * @param bool $default
     *
     * @return bool|string
     */
    public function installedVersion($default = false)
    {
        return collect($this->supportedVersions())->filter(function ($version) {
            return $this->brew->installed($version);
        })->first(null, $default);
    }

    /**
     * Remove current configuration before install new version.
     *
     * @param string $type
     */
    private function removeConfiguration($type = self::MYSQL_DEFAULT_FORMULA)
    {
        $this->files->unlink(static::MYSQL_CONF);
        $this->files->unlink(static::MYSQL_CONF . '.default');
    }

    /**
     * Stop the Mysql service.
     */
    public function stop()
    {
        $version = $this->installedVersion(self::MYSQL_DEFAULT_FORMULA);
        info('[' . $version . '] Stopping');

        $this->cli->quietly('sudo brew services stop ' . $version);
        $this->cli->quietlyAsUser('brew services stop ' . $version);
    }

    /**
     * Install the configuration files.
     *
     * @param string $type
     */
    public function installConfiguration($type = self::MYSQL_DEFAULT_FORMULA)
    {
        info('[' . $type . '] Configuring');

        $this->files->chmodPath(static::MYSQL_DIR, 0777);

        if (!$this->files->isDir($directory = static::MYSQL_CONF_DIR)) {
            $this->files->mkdirAsUser($directory);
        }

        $contents = $this->files->get(__DIR__ . '/../stubs/my.cnf');
        if ($type === self::MARIA_DB) {
            $contents = \str_replace('show_compatibility_56=ON', '', $contents);
        }

        $this->files->putAsUser(
            static::MYSQL_CONF,
            \str_replace('VALET_HOME_PATH', VALET_HOME_PATH, $contents)
        );
    }

    /**
     * Restart the Mysql service.
     */
    public function restart()
    {
        $version = $this->installedVersion() ?: self::MYSQL_DEFAULT_FORMULA;
        info('[' . $version . '] Restarting');
        $this->cli->quietlyAsUser('brew services restart ' . $version);
    }


    /**
     * Unlink a MySQL version, removing the binary symlink.
     *
     * @param $version
     * @return bool
     */
    private function unlinkMySQL(string $version): bool
    {
        $isUnlinked = true;

        info("[$version] Unlinking");
        output($this->cli->runAsUser('brew unlink ' . $version, function () use (&$isUnlinked) {
            $isUnlinked = false;
        }));
        if ($isUnlinked === false) {
            warning("Could not unlink MySQL version!" . PHP_EOL .
                "There appears to be an issue with your MySQL $version installation!" . PHP_EOL .
                "See the output above for more information.");
        }

        return $isUnlinked;
    }

    /**
     * Set root password of Mysql.
     * @param string $oldPwd
     * @param string $newPwd
     */
    public function setRootPassword($oldPwd = '', $newPwd = self::MYSQL_ROOT_PASSWORD)
    {
        $alreadyRootPW = $this->cli->runAsUser('mysql -u root -proot -e"quit"');

        if (\strpos($alreadyRootPW, 'mysql: [Warning] Using a password on the command line interface can be insecure.') !== false) {
            info('[mysql] Password of root user is already set to root');
            return;
        }

        $success = true;
        $this->cli->runAsUser("mysqladmin -u root --password='".$oldPwd."' password ".$newPwd, function () use (&$success) {
            warning('Setting password for root user failed. ');
            $success = false;
        });

        if ($success !== false) {
            $config = $this->configuration->read();
            if (!isset($config['mysql'])) {
                $config['mysql'] = [];
            }
            $config['mysql']['password'] = $newPwd;
            $this->configuration->write($config);
        }
    }

    /**
     * Returns the stored password from the config. If not configured returns the default root password.
     */
    private function getRootPassword()
    {
        $config = $this->configuration->read();
        if (isset($config['mysql']) && isset($config['mysql']['password'])) {
            return $config['mysql']['password'];
        }

        return self::MYSQL_ROOT_PASSWORD;
    }

    /**
     * Prepare Mysql for uninstallation.
     */
    public function uninstall()
    {
        $this->stop();
    }

    /**
     * Print table of exists databases.
     */
    public function listDatabases()
    {
        table(['Database'], $this->getDatabases());
    }

    /**
     * Get exists databases.
     *
     * @return array|bool
     */
    protected function getDatabases()
    {
        $result = $this->query('SHOW DATABASES');

        if (!$result) {
            return false;
        }

        return collect($result->fetch_all(MYSQLI_ASSOC))->reject(function ($row) {
            return \in_array($row['Database'], $this->getSystemDatabase());
        })->map(function ($row) {
            return [$row['Database']];
        })->toArray();
    }

    /**
     * Run Mysql query.
     *
     * @param $query
     * @param bool $escape
     *
     * @return bool|\mysqli_result
     */
    protected function query($query, $escape = true)
    {
        $link = $this->getConnection();

        $query = $escape ? $this->escape($query) : $query;

        return tap($link->query($query), function ($result) use ($link) {
            if (!$result) { // throw mysql error
                warning(\mysqli_error($link));
            }
        });
    }

    /**
     * Return Mysql connection.
     *
     * @return bool|mysqli
     */
    public function getConnection()
    {
        // if connection already exists return it early.
        if ($this->link) {
            return $this->link;
        }

        // Create connection
        $this->link = new mysqli('localhost', 'root', $this->getRootPassword());

        // Check connection
        if ($this->link->connect_error) {
            warning('Failed to connect to database');

            return false;
        }

        return $this->link;
    }

    /**
     * escape string of query via myslqi.
     *
     * @param string $string
     *
     * @return string
     */
    protected function escape($string)
    {
        return \mysqli_real_escape_string($this->getConnection(), $string);
    }

    /**
     * Get default databases of mysql.
     *
     * @return array
     */
    protected function getSystemDatabase()
    {
        return $this->systemDatabase;
    }

    /**
     * Drop current Mysql database & re-import it from file.
     *
     * @param $file
     * @param $database
     */
    public function reimportDatabase($file, $database)
    {
        $this->importDatabase($file, $database, true);
    }

    /**
     * Import Mysql database from file.
     *
     * @param string $file
     * @param string $database
     * @param bool   $dropDatabase
     */
    public function importDatabase($file, $database, $dropDatabase = false)
    {
        $database = $this->getDatabaseName($database);

        // drop database first
        if ($dropDatabase) {
            $this->dropDatabase($database);
        }

        $this->createDatabase($database);

        $gzip = ' | ';
        if (\stristr($file, '.gz')) {
            $gzip = ' | gzip -cd | ';
        }
        $this->cli->passthru('pv ' . \escapeshellarg($file) . $gzip . 'mysql ' . \escapeshellarg($database));
    }

    /**
     * Get database name via name or current dir.
     *
     * @param $database
     *
     * @return string
     */
    protected function getDatabaseName($database = '')
    {
        return $database ?: $this->getDirName();
    }

    /**
     * Get current dir name.
     *
     * @return string
     */
    public function getDirName()
    {
        $gitDir = $this->cli->runAsUser('git rev-parse --show-toplevel 2>/dev/null');

        if ($gitDir) {
            return \trim(\basename($gitDir));
        }

        return \trim(\basename(\getcwd()));
    }

    /**
     * Drop Mysql database.
     *
     * @param string $name
     *
     * @return bool|string
     */
    public function dropDatabase($name)
    {
        $name = $this->getDatabaseName($name);

        return $this->query('DROP DATABASE `' . $name . '`') ? $name : false;
    }

    /**
     * Create Mysql database.
     *
     * @param string $name
     *
     * @return bool|string
     */
    public function createDatabase($name)
    {
        $name = $this->getDatabaseName($name);

        return $this->query('CREATE DATABASE IF NOT EXISTS `' . $name . '`') ? $name : false;
    }

    /**
     * Check if database already exists.
     *
     * @param string $name
     *
     * @return bool|\mysqli_result
     */
    public function isDatabaseExists($name)
    {
        $name = $this->getDatabaseName($name);

        $query = $this->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '" . $this->escape($name) . "'", false);

        return (bool) $query->num_rows;
    }

    /**
     * Export Mysql database.
     *
     * @param $filename
     * @param $database
     *
     * @return array
     */
    public function exportDatabase($filename, $database)
    {
        $database = $this->getDatabaseName($database);

        if (!$filename || $filename === '-') {
            $filename = $database . '-' . \date('Y-m-d-His', \time());
        }

        if (!\stristr($filename, '.sql')) {
            $filename = $filename . '.sql.gz';
        }
        if (!\stristr($filename, '.gz')) {
            $filename = $filename . '.gz';
        }

        $this->cli->passthru('mysqldump ' . \escapeshellarg($database) . ' | gzip > ' . \escapeshellarg($filename ?: $database));

        return [
            'database' => $database,
            'filename' => $filename,
        ];
    }

    /**
     * Open Mysql database via Sequel pro.
     *
     * @param string $name
     */
    public function openSequelPro($name = '')
    {
        $tmpName = \tempnam(\sys_get_temp_dir(), 'sequelpro') . '.spf';

        $contents = $this->files->get(__DIR__ . '/../stubs/sequelpro.spf');

        $this->files->putAsUser(
            $tmpName,
            \str_replace(
                ['DB_NAME', 'DB_HOST', 'DB_USER', 'DB_PASS', 'DB_PORT'],
                [$this->getDatabaseName($name), '127.0.0.1', 'root', $this->getRootPassword(), '3306'],
                $contents
            )
        );

        $this->cli->quietly('open ' . $tmpName);
    }
}
