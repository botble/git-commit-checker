# Changelog

All notable changes to `laravel-git-commit-checker` will be documented in this file.


## 2.1.0 - 2023-01-06

- Fix output render on Windows platform (#10)

## 2.0.1 - 2023-01-05

- Add warning message "Run command to see coding standard detail issues"
- Update version icon from README.md
- Remove verbose when run `pint --test`

## 2.0.0 - 2023-01-05

- Drop support PHP 7.x, Laravel <= 8.x
- Replace PHP Code Sniffer, PHPLint to Laravel Pint
- Rename command `git:install-hooks` to `git-commit-checker:install`
- Rename command `git:pre-commit-hook` to `git-commit-checker:pre-commit-hook`
- Remove command `git:create-phpcs`
- Remove TravisCI, StyleCI, Scrutinizer
- Remove unnecessary files

## 1.0.0 - 2019-08-26

- First release.
