# Poweradmin [![Composer](https://github.com/poweradmin/poweradmin/actions/workflows/php.yml/badge.svg)](https://github.com/poweradmin/poweradmin/actions/workflows/php.yml) [![Gitter](https://badges.gitter.im/poweradmin/poweradmin.svg)](https://gitter.im/poweradmin/poweradmin?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge)

[Poweradmin](https://www.poweradmin.org) is a friendly web-based DNS administration tool for Bert Hubert's PowerDNS
server. The interface has full support for most of the features of PowerDNS. It has full support for all zone types (
master, native and slave), for supermasters for automatic provisioning of slave zones, full support for IPv6 and comes
with multi-language support.

## Disclaimer

This project is not affiliated with [PowerDNS.com](https://www.powerdns.com/index.html)
, [Open-Xchange](https://www.open-xchange.com), or any other third parties.
It is independently funded and maintained. If this project does not meet your needs, please check out these
other [options](https://github.com/PowerDNS/pdns/wiki/WebFrontends).

## Requirements

* PHP 8.1
* PHP intl extension
* PHP gettext extension
* PHP openssl extension
* PHP pdo extension
* PHP pdo-mysql, pdo-pgsql or pdo-sqlite extension
* PHP ldap extension (optional)
* MySQL 5.7.x/8.x, MariaDB, PostgreSQL or SQLite database
* PowerDNS authoritative server 4.0.0+

## Tested on

| Poweradmin | PHP            | PowerDNS | MariaDB | MySQL  | PostgreSQL | SQLite |
|------------|----------------|----------|---------|--------|------------|--------|
| 3.7.x      | 8.1.2          | 4.5.3    | 11.1.2  | 8.2.0  | 16.0       | 3.40.1 |
| 3.6.x      | 8.1.2          | 4.5.3    | 11.1.2  | 8.1.0  | 16.0       | 3.40.1 |
| 3.5.x      | 8.1.17         | 4.5.3    | 10.11.2 | 8.0.32 | 15.2       | 3.34.1 |
| 3.4.x      | 7.4.3 / 8.1.12 | 4.2.1    | 10.10.2 | 8.0.31 | 15.1       | 3.34.1 |

## Installation

Install the following dependencies:

On Debian based Systems:

```sh
apt install php-intl

For MySQL/MariaDB
apt install php-mysqlnd

For PostgreSQL
apt install php-pgsql

For SQLite
apt install php-sqlite3
```

On RHEL based Systems:

```sh
yum install -y php-intl

For MySQL/MariaDB
yum install -y php-mysqlnd

For PostgreSQL
yum install -y php-pgsql
```

Download the project files

* Via Git:
    * Clone the repository: ```git clone https://github.com/poweradmin/poweradmin.git```
    * Select latest tag (for example v3.6.0) or skip this if you want to run from master: ```git checkout tags/v3.6.0```
* Via releases:
    * Get the latest file from [releases](https://github.com/poweradmin/poweradmin/releases)

Go to the installed system in your browser

* Visit http(s)://URL/install/ and follow the installation steps
* Once the installation is complete, remove the `install` folder
* Point your browser to: http(s)://URL
* Log in using the credentials created during setup

## Troubleshooting

Whenever you experience a blank page or other weird behavior, check your http server logs for PHP errors. Alternatively,
you can add a code block with error output to the browser (for instance, index.php if it fails):

```php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
```

## Screenshots

### Log in

![The login screen](https://www.poweradmin.org/screenshots/ignite_login.png)

### Zone list

![List of zones](https://www.poweradmin.org/screenshots/ignite_zone_list.png)
