<p align="center"><img width="200" src="images/logo.png"></p>

## Introduction

Valet+ is a development environment for macOS. No Vagrant, no Docker, no `/etc/hosts` file.

### Valet vs. Valet+

Valet+ is a third-party fork of [Laravel Valet](https://github.com/laravel/valet). Valet+ adds functionality to Valet with a goal of making things even simpler and faster. We are very grateful to the Laravel team for providing the base that we built Valet+ on. Since this is a fork we'll pull in changes from the original Valet regularly as they are released.

Some of the documentation in this readme was taken from the Valet website and provided here for convenience, so that you can read this document and know about all features provided. The original documentation can be found here: https://laravel.com/docs/valet.

Since Valet+ is intended to replace Valet, it still uses the same `valet` command-line name. Any changes in its interface are documented below.

### Why Valet/Valet+?

Valet+ configures your Mac to always run Nginx in the background when your machine starts. Then, using [DnsMasq](https://en.wikipedia.org/wiki/Dnsmasq), Valet+ proxies all requests on the `*.test` domain to point to sites installed on your local machine.

In other words, a blazing fast development environment. Valet+ provides a great alternative if you want flexible basics or prefer extreme speed.

### Differences from Valet

Here are a few key differences compared to the original Valet:

- PHP version switch
- Xdebug (on/off mode)
- PHP extensions (mcrypt, intl, opcache)
- Optimized PHP configuration using opcache
- MySQL (with optimized configuration)
- Redis
- Elasticsearch (optional)
- Many more features outlined below...

## Table of Contents

<!-- START doctoc generated TOC please keep comment here to allow auto update -->
<!-- DON'T EDIT THIS SECTION, INSTEAD RE-RUN doctoc TO UPDATE -->
- [Introduction](#introduction)
- [Installation](#installation)
  - [Serving sites](#serving-sites)
- [Switching PHP version](#switching-php-version)
- [Xdebug](#xdebug)
  - [PhpStorm](#phpstorm)
- [Database](#database)
  - [Creating databases](#creating-databases)
  - [Importing databases](#importing-databases)
  - [Open database in Sequel Pro](#open-database-in-sequel-pro)
- [Redis](#redis)
- [Open project in browser](#open-project-in-browser)
- [Securing Sites With TLS](#securing-sites-with-tls)
- [Valet drivers](#valet-drivers)
- [Valet Documentation](#valet-documentation)
- [Credits](#credits)

<!-- END doctoc generated TOC please keep comment here to allow auto update -->

## Installation

> :warning: Valet+ requires macOS and [Homebrew](https://brew.sh/). Before installation, you should make sure that no other programs such as Apache or Nginx are binding to your local machine's port 80.

1. Install or update [Homebrew](https://brew.sh/) to the latest version using `brew update`.
3. Add the Homebrew PHP tap for Valet+ via `brew tap henkrehorst/php`.
3. Install PHP 7.2 using Homebrew via `brew install valet-php@7.2`.
4. Install Composer using Homebrew via `brew install composer`.
5. Install Valet+ with Composer via `composer global require marcofaul/valet-plus-reforged`.
6. Add `export PATH="$PATH:$HOME/.composer/vendor/bin"` to `.bash_profile` (for bash) or `.zshrc` (for zsh) depending on your shell (`echo $SHELL`)
7. Run the `valet fix` command. This will check for common issues preventing Valet+ from installing.
8. Run the `valet install` command. Optionally add `--with-mariadb` to use MariaDB instead of MySQL This will configure and install Valet+ and DnsMasq, and register Valet's daemon to launch when your system starts.
9. Once Valet+ is installed, try pinging any `*.test` domain on your terminal using a command such as `ping -c1 foobar.test`. If Valet+ is installed correctly you should see this domain responding on `127.0.0.1`. If not you might have to restart your system. Especially when coming from the Dinghy (docker) solution.

> :information_source: Valet+ will automatically start its daemon each time your machine boots. There is no need to run `valet start` or `valet install` ever again once the initial Valet+ installation is complete.

> :information_source: To update Valet+ to the latest version use the `composer global require weprovide/valet-plus` command in your terminal. After upgrading, it is good practice to run the `valet install` command so Valet+ can make additional upgrades to your configuration files if necessary.

### Serving sites

Once Valet+ is installed, you're ready to start serving sites. Valet+ provides a command to help you serve your sites: `valet park`. Which will register the current working directory as projects root. Generally this directory is `~/sites`.

1. Create a `sites` directory: `mkdir ~/sites`
2. `cd ~/sites`
3. `valet park`

That's all there is to it. Now, any project you create within your "parked" directory will automatically be served using the http://folder-name.test convention.

For example:

1. `mkdir ~/sites/example`
2. `cd ~/sites/example`
3. `echo "<?php echo 'Valet+ at your service';" > index.php`
4. Go to `http://example.test`, you should see `Valet+ at your service`

## Switching PHP version

Switch PHP version using one of four commands:

```
valet use 5.6
```

```
valet use 7.0
```

```
valet use 7.1
```

```
valet use 7.2
```

## Xdebug

Xdebug support is built-in. It works on port `9000` after you enable it.

The `[--remote_autostart=]` option can be used by typing: `valet xdebug --remote_autostart=0` or changing both settings: `valet xdebug on --remote_autostart=1`

Enable Xdebug:

```
valet xdebug on [--remote_autostart=]
```

Disable Xdebug:

```
valet xdebug off [--remote_autostart=]
```

Enable/disable xdebug.remote_autostart:

```
valet xdebug --remote_autostart=[true/false]
```

> :warning: Xdebug makes your environment slower. That's why we allow to fully enable / disable it. When not debugging it's best to disable it by running `valet xdebug off`.

### PhpStorm

To use Xdebug with PhpStorm you don't have to configure anything. Just run `valet xdebug on` and click the Xdebug button on the top right:

![xdebug-phpstorm](images/xdebug-phpstorm.png)

Then install [Xdebug helper](https://chrome.google.com/webstore/detail/xdebug-helper/eadndfjplgieldjbigjakmdgkmoaaaoc) for Chrome and enable it when viewing the page you want to use Xdebug on.

## Ioncube

Enable Ioncube:

```
valet ioncube on
```

Disable Ioncube:

```
valet ioncube off
```

## Database
Valet+ automatically installs MySQL 5.7 with 5.6 compatibility mode included. It includes a tweaked `my.cnf` which is aimed at improving speed.

Username: `root`

Password: `root`

## Change password

```
valet db pwd <old> <new>
```


## List databases

```
valet db ls
```

### Creating databases

Create databases using:

```
valet db create <name>
```

When no name is given it'll try to find the closest git repository directory name. When it can't find one it'll use the current working directory name.

```
valet db create
```

### Dropping databases

Drop a database using:

```
valet db drop <name>
```

When no name is given it'll try to find the closest git repository directory name. When it can't find one it'll use the current working directory name.

```
valet db drop
```

### Resetting databases

Drop and create a database using:

```
valet db reset <name>
```

When no name is given it'll try to find the closest git repository directory name. When it can't find one it'll use the current working directory name.

```
valet db reset
```

### Exporting databases

Export a database:

```
valet db export <filename> <database>
```

When no database name is given it'll try to find the closest git repository directory name. When it can't find one it'll use the current working directory name.

When no filename is given it will use `<database>-<date>.sql.gz`. Optionally you can use `valet db export - <database>` to automatically generate the filename for a custom database.

All database exports are gzipped.

### Importing databases

Import a database with progress bar

```
valet db import <filename>.sql(.gz) <name>
```

When no name is given it'll try to find the closest git repository directory name. When it can't find one it'll use the current working directory name.

You can import `.sql` directly as well as gzipped `.sql.gz` database exports.

### Open database in Sequel Pro

Valet+ has first class support for opening databases in [Sequel Pro](https://www.sequelpro.com/), a popular MySQL client for Mac.

```
valet db open <name>
```

When no name is given it'll try to find the closest git repository directory name. When it can't find one it'll open Sequel Pro without pre-selected database.

```
valet db open
```

## Logging

To easily read the log file of php, php-fpm, nginx, mysql, mailhog or redis you can use command below.
```
valet logs <logname>
```

## Subdomains

You can manage subdomains for the current working directory using:

```
valet subdomain list
```

```
valet subdomain add <subdomain>
```

For example:

```
valet subdomain add welcome
```

Will create `welcome.yourproject.test`.

## Domain Alias / Symlinks

Display all of the registered symbolic links based on the current folder.:

```
valet links
```

Add new alias:
```
valet link <domain>
```

For example:

```
valet link yourproject2
```

Will create a symbolic link to the current folder `yourproject2.test`.

Remove alias:
```
valet unlink <domain>
```

For example:

```
valet unlink yourproject2
```

## Mailhog

Mailhog is used to catch emails send from PHP. You can access the panel at [http://mailhog.test](http://mailhog.test).

Enable Mailhog:

```
valet mailhog on
```

Disable Mailhog:

```
valet mailhog off
```

## Redis

Redis is automatically installed and listens on the default port `6379`. The redis socket is located at `/tmp/redis.sock`

Enable Redis:

```
valet redis on
```

Disable Redis:

```
valet redis off
```

## Elasticsearch

Elasticsearch 2.4 can be installed using:

```
valet elasticsearch install
```

To uninstall:

```
brew uninstall elasticsearch@2.4
```

The most recent version of Elasticsearch – 5.6 at the time of writing – can be installed using:

```
brew install elasticsearch
```

It will run on the default port `9200`, and is accessible at [http://elasticsearch.test/](http://elasticsearch.test/).

Elasticsearch 2.4 is installed by default because [Magento 2.1 does not support Elasticsearch 5](http://devdocs.magento.com/guides/v2.1/config-guide/elasticsearch/es-overview.html).


### Switching Elasticsearch version

Switch Elasticsearch version using one of these commands:

```
valet use elasticsearch|es 2.4
```
```
valet use elasticsearch|es 5.6
```
```
valet use elasticsearch|es 6.8
```


## Framework specific development tools

Valet+ will automatically install framework specific development tools for you:

- [wp-cli](http://wp-cli.org/) available as `wp`
- [n98-magerun](https://github.com/netz98/n98-magerun) available as `magerun`
- [n98-magerun2](https://github.com/netz98/n98-magerun2) available as `magerun2` for you.

## Git Tower

Open current git project in [Tower](https://www.git-tower.com/mac/)

```
valet tower
```

## PhpStorm

Open current git project in [PhpStorm](https://www.jetbrains.com/phpstorm/)

```
valet phpstorm
```

## SourceTree

Open current git project in [SourceTree](https://www.sourcetreeapp.com/)

```
valet sourcetree
```

## VScode

Open current git project in [Visual Studio Code](https://code.visualstudio.com/)

```
valet vscode
```

## Open project in browser

To open the current project in your default browser:

```
valet open
```

## Copy ssh key

```
valet ssh-key
```

## Automatic configuration [beta]

Automatically configure environment for the project you're in.

```
valet configure
```

### Supported systems

#### Magento 2

Automatically configure the `env.php`, `config.php` base url, seo url rewrites and elastic search configuration in the database for Magento 2.

#### Magento 1

Automatically configure the `local.xml` and base url in the database for Magento 1.


## Securing Sites With TLS

By default, Valet serves sites over plain HTTP. However, if you would like to serve a site over encrypted TLS using HTTP/2, use the secure command. For example, if your site is being served by Valet on the example.test domain, you should run the following command to secure it:

```
valet secure example
```

To "unsecure" a site and revert back to serving its traffic over plain HTTP, use the `unsecure` command. Like the `secure` command, this command accepts the host name you wish to unsecure:

```
valet unsecure example
```

## Log locations

The `nginx-error.log`, `php.log` and `mysql.log` are located at `~/.valet/Log`.

Other logs are located at `/usr/local/var/log`

## PHP.ini location

The PHP.ini location is `/usr/local/etc/valet-php/VERSION/php.ini`.

## Valet drivers
Valet uses drivers to handle requests. You can read more about those [here](https://laravel.com/docs/5.4/valet#custom-valet-drivers).

~~When using Valet+ drivers are automatically cached using APCu to avoid doing a driver lookup every time there is a request. You can reset the cache for a specific site by running `valet which`.~~
APCu is temporarily turned off due to a compatibility issue with PHP-FPM, see https://github.com/weprovide/valet-plus/issues/49.

By default these are included:

- CakePHP 3
- Craft
- Drupal
- Jigsaw
- Laravel
- Lumen
- Magento
- Magento 2
- Neos
- Pimcore 5
- Shopware 5
- Slim
- Statamic
- Static HTML
- Symfony
- Typo3
- WordPress / Bedrock
- Zend Framework

A full list can be found [here](cli/drivers).

## Custom Valet Drivers

You can write your own Valet "driver" to serve PHP applications running on another framework or CMS that is not natively supported by Valet. When you install Valet+, a `~/.valet/Drivers` directory is created which contains a `SampleValetDriver.php` file. This file contains a sample driver implementation to demonstrate how to write a custom driver. Writing a driver only requires you to implement three methods: `serves`, `isStaticFile`, and `frontControllerPath`.

All three methods receive the `$sitePath`, `$siteName`, and `$uri` values as their arguments. The `$sitePath` is the fully qualified path to the site being served on your machine, such as `/Users/Lisa/Sites/my-project`. The `$siteName` is the "host" / "site name" portion of the domain (`my-project`). The `$uri` is the incoming request URI (`/foo/bar`).

Once you have completed your custom Valet+ driver, place it in the `~/.valet/Drivers` directory using the `FrameworkValetDriver.php` naming convention. For example, if you are writing a custom valet driver for WordPress, your file name should be `WordPressValetDriver.php`.

Let's take a look at a sample implementation of each method your custom Valet+ driver should implement.

#### The `serves` Method

The `serves` method should return `true` if your driver should handle the incoming request. Otherwise, the method should return `false`. So, within this method you should attempt to determine if the given `$sitePath` contains a project of the type you are trying to serve.

For example, let's pretend we are writing a `WordPressValetDriver`. Our serve method might look something like this:

```
/**
 * Determine if the driver serves the request.
 *
 * @param  string  $sitePath
 * @param  string  $siteName
 * @param  string  $uri
 * @return bool
 */
public function serves($sitePath, $siteName, $uri)
{
    return is_dir($sitePath.'/wp-admin');
}

```

#### The `isStaticFile` Method

The `isStaticFile` should determine if the incoming request is for a file that is "static", such as an image or a stylesheet. If the file is static, the method should return the fully qualified path to the static file on disk. If the incoming request is not for a static file, the method should return `false`:

```
/**
 * Determine if the incoming request is for a static file.
 *
 * @param  string  $sitePath
 * @param  string  $siteName
 * @param  string  $uri
 * @return string|false
 */
public function isStaticFile($sitePath, $siteName, $uri)
{
    if (file_exists($staticFilePath = $sitePath.'/public/'.$uri)) {
        return $staticFilePath;
    }

    return false;
}

```

> {note} The `isStaticFile` method will only be called if the `serves` method returns `true` for the incoming request and the request URI is not `/`.

#### The `frontControllerPath` Method

The `frontControllerPath` method should return the fully qualified path to your application's "front controller", which is typically your "index.php" file or equivalent:

```
/**
 * Get the fully resolved path to the application's front controller.
 *
 * @param  string  $sitePath
 * @param  string  $siteName
 * @param  string  $uri
 * @return string
 */
public function frontControllerPath($sitePath, $siteName, $uri)
{
    return $sitePath.'/public/index.php';
}

```

### Local Drivers

If you would like to define a custom Valet driver for a single application, create a `LocalValetDriver.php` in the application's root directory. Your custom driver may extend the base `ValetDriver` class or extend an existing application specific driver such as the `LaravelValetDriver`:

```
class LocalValetDriver extends LaravelValetDriver
{
    /**
     * Determine if the driver serves the request.
     *
     * @param  string  $sitePath
     * @param  string  $siteName
     * @param  string  $uri
     * @return bool
     */
    public function serves($sitePath, $siteName, $uri)
    {
        return true;
    }

    /**
     * Get the fully resolved path to the application's front controller.
     *
     * @param  string  $sitePath
     * @param  string  $siteName
     * @param  string  $uri
     * @return string
     */
    public function frontControllerPath($sitePath, $siteName, $uri)
    {
        return $sitePath.'/public_html/index.php';
    }
}
```

## Credits

This project is a fork of [weprovide/valet-plus](https://github.com/weprovide/valet-plus)

## Valet+ Reforged Authors

- MarcoFaul ([@marcofaul](https://github.com/marcofaul))
