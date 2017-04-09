Event Gator, Api Event Aggregator
=======================

Event Gator is a PHP Api Event Aggregator client that makes it easy to construct a standard
event set from your third party event services.

```php
$config = [
    "api_1" => [...API_1_CREDS...],
    "api_2" => [...API_2_CREDS...],
    "..."
]
$gator = new \EventGator\EventGatorClient();
$events = $gator->getEvents();
```

## Help and docs

## Installing Event Gator

The recommended way to install Guzzle is through
[Composer](http://getcomposer.org).

```bash
# Install Composer
curl -sS https://getcomposer.org/installer | php
```

Next, run the Composer command to install the latest stable version of Guzzle:

```bash
php composer.phar require jonbrobinson/eventgator
```

After installing, you need to require Composer's autoloader:

```php
require 'vendor/autoload.php';
```

You can then later update EventGator using composer:

 ```bash
composer.phar update
 ```
