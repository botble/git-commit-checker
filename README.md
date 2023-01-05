# Laravel GIT Commit Checker

[![Latest Version](https://img.shields.io/github/release/botble/git-commit-checker.svg?style=flat-square)](https://github.com/botble/git-commit-checker/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Total Downloads](https://img.shields.io/packagist/dt/botble/git-commit-checker.svg?style=flat-square)](https://packagist.org/packages/botble/git-commit-checker)
[![Maintainability](https://api.codeclimate.com/v1/badges/a6e4612307e3b3bf8252/maintainability)](https://codeclimate.com/github/botble/git-commit-checker/maintainability)

## Requirement

- Laravel 9.32 or later
- If you're using Laravel 8.0 or earlier, please use version 1.x

## Installation

You can install the package via composer:

```shell
composer require botble/git-commit-checker
```

Publish the configuration:

```bash
php artisan vendor:publish --tag=git-commit-checker-config
```

### Install GIT hooks

Run this command to install:

```shell
php artisan git-commit-checker:install
```

Run test manually (made sure you've added all changed files to git stage):

```shell
php artisan git-commit-checker:pre-commit-hook
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email contact@dinhquochan.com instead of using the issue tracker.

## Credits

- [Sang Nguyen](https://github.com/sangnguyenplus)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
