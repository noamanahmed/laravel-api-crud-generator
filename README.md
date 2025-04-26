# This is my package laravel-api-crud-generator

[![Latest Version on Packagist](https://img.shields.io/packagist/v/noamanahmed/laravel-api-crud-generator.svg?style=flat-square)](https://packagist.org/packages/noamanahmed/laravel-api-crud-generator)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/noamanahmed/laravel-api-crud-generator/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/noamanahmed/laravel-api-crud-generator/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/noamanahmed/laravel-api-crud-generator/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/noamanahmed/laravel-api-crud-generator/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/noamanahmed/laravel-api-crud-generator.svg?style=flat-square)](https://packagist.org/packages/noamanahmed/laravel-api-crud-generator)


## Compatibility Matrix

Laravel Version | Package Version | Status
8.x | 1.x | Supported
9.x | 1.x | Supported
10.x | 1.x | Supported
11.x (beta) | 1.x | In Development

## Installation

You can install the package via composer:

```bash
composer require noamanahmed/laravel-api-crud-generator
```

You can publish the config and provider file with this command:

```bash
php artisan vendor:publish --tag="api-crud-generator-provider"
php artisan vendor:publish --tag="api-crud-generator-stubs"
```

```bash
php artisan api-crud-generator:init
```


## Usage

```php
php artisan api-crud-generator:create Post
php artisan api-crud-generator:create Comment
php artisan api-crud-generator:delete Comment

```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Noaman Ahmed](https://github.com/noamanahmed)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
