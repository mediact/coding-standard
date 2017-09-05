[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/mediact/coding-standard/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/mediact/coding-standard/?branch=master)

# MediaCT Coding Standard

This is the MediaCT coding standard, a set of tools for standardizing PHP development code style.

## Installation

Use composer to install the coding standard in your home directory.

```shell
$ composer global config repositories.mediact composer https://composer.mediact.nl
$ composer global require mediact/coding-standard
```

## Configuring PHPStorm to use the coding standard.

First configure PHPStorm to use the right phpcs command.

Go to __Settings > Languages & Frameworks > PHP > Code Sniffer__. Choose
"Local" for the path and fill in the full path to 
`~/.composer/vendor/bin/phpcs`

Then go to __Settings > Editor > Inspections__ and search for PHP Code Sniffer
Validation. Select Custom and the add the path to 
`~/.composer/vendor/mediact/coding-standard/src/MediaCT`

## Using the coding standard in a project

To use the standard in a project the standard needs to be required in composer.

```shell
$ cd <project_directory>
$ composer config repositories.mediact composer https://composer.mediact.nl
$ composer require mediact/coding-standard --dev
```

This will add the coding standard to the vendor directory of the project.

To let phpcs use the coding standard add a file phpcs.xml to the root of the
project.

```xml
<?xml version="1.0"?>
<ruleset>
    <rule ref="./vendor/mediact/coding-standard/src/MediaCT"/>
</ruleset>
```

The standard can be checked from the command line by going to the directory.

```shell
$ cd <project_directory>
$ ./vendor/bin/phpcs ./src
```

## Configuring PHP CodeSniffer to also show less severe messages

By default PHP CodeSniffer shows only messages with a severity higher than
__5__. The MediaCT coding standard also has some messages with a lower
severity. These are messages that encourage a better way of coding but should
not block a pull request.

To configure phpcs to show also these messages execute the following command.

```shell
$ ~/.composer/vendor/bin/phpcs \
  --config-set severity 1
```
