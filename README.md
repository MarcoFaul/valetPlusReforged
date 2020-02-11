## Introduction
Go here for the [valet+ documentation](https://github.com/weprovide/valet-plus/wiki).

### Differences from Valet+

Here are a few key differences compared to Valet+:

- Add PHP 7.3, 7.4 support
- Add Ioncube for PHP 7.3 
- Add Elasticsearch 6.8 support
- ADD TLD (Top-Level-Domain) command
- Add PHP switching error messages 
- Add Codesniffer
- Add Trust command to add valet and brew to sudoers (no password needed anymore)
- Add "on successful installation, open browser with valet domain"
- Add port scan
- Update Xdebug version update (2.2.7 -> 2.9.0)
- Fix APCU_BC extension sometimes gotten installed twice
- Fix MySQL Linking
- Fix Memcache because of missing zlib
- Fix missing Elasticsearch config
- A lot more

#### Coming soon…
- Ioncube for PHP 7.4 (https://forum.ioncube.com/viewtopic.php?t=4542&postdays=0&postorder=asc&start=15)
- MySQL 8 support
- default installation php 7.4

## Installation

> :warning: Valet+ requires macOS and [Homebrew](https://brew.sh/). Before installation, you should make sure that no other programs such as Apache or Nginx are binding to your local machine's port 80.

If you have valet or valet+ installed. It is recommended to remove it first  
composer remove weprovide/valet-plus && rm -rf ~/.valet && rm -rf /usr/local/bin/valet

1. Install or update [Homebrew](https://brew.sh/) to the latest version using `brew update`.
3. Add the Homebrew PHP tap for Valet+ via `brew tap henkrehorst/php`.
3. Install PHP 7.2 using Homebrew via `brew install valet-php@7.2`.
4. Install Composer using Homebrew via `brew install composer`.
5. Install Valet+ with Composer via `composer global require marcofaul/valet-plus-reforged`.
6. Add `export PATH="$PATH:$HOME/.composer/vendor/bin"` to `.bash_profile` (for bash) or `.zshrc` (for zsh) depending on your shell (`echo $SHELL`)
7. Run the `valet fix` command. This will check for common issues preventing Valet+ from installing.
8. Run the `valet install` command. Optionally add `--with-mariadb` to use MariaDB instead of MySQL This will configure and install Valet+ and DnsMasq, and register Valet's daemon to launch when your system starts.
9. Once Valet+ is installed, try pinging any `*.test` domain on your terminal using a command such as `ping -c1 foobar.test`. If Valet+ is installed correctly you should see this domain responding on `127.0.0.1`. If not you might have to restart your system. Especially when coming from the Dinghy (docker) solution.

## Credits

This project is a fork of [weprovide/valet-plus](https://github.com/weprovide/valet-plus)

## Valet+ Reforged Authors

- MarcoFaul ([@marcofaul](https://github.com/marcofaul))
