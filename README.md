<p align="center">
    <a href="https://github.com/yiisoft" target="_blank">
        <img src="https://github.com/yiisoft.png" height="100px">
    </a>
    <h1 align="center">Yii Test Support</h1>
    <br>
</p>

The package is intended to simplify the process of testing application elements that depend on PSR interfaces.

[![Latest Stable Version](https://poser.pugx.org/yiisoft/test-support/v/stable.png)](https://packagist.org/packages/yiisoft/test-support)
[![Total Downloads](https://poser.pugx.org/yiisoft/test-support/downloads.png)](https://packagist.org/packages/yiisoft/test-support)
[![Build status](https://github.com/yiisoft/test-support/workflows/build/badge.svg)](https://github.com/yiisoft/test-support/actions?query=workflow%3Abuild)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/yiisoft/test-support/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/yiisoft/test-support/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/yiisoft/test-support/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/yiisoft/test-support/?branch=master)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fyiisoft%2Ftest-support%2Fmaster)](https://dashboard.stryker-mutator.io/reports/github.com/yiisoft/test-support/master)
[![static analysis](https://github.com/yiisoft/test-support/workflows/static%20analysis/badge.svg)](https://github.com/yiisoft/test-support/actions?query=workflow%3A%22static+analysis%22)

## Installation

The package could be installed with composer:

```bash
composer install yiisoft/test-support --dev
```

## Unit testing

The package is tested with [PHPUnit](https://phpunit.de/). To run tests:

```bash
./vendor/bin/phpunit
```

## Mutation testing

The package tests are checked with [Infection](https://infection.github.io/) mutation framework. To run it:

```bash
./vendor/bin/infection
```

## Static analysis

The code is statically analyzed with [Phan](https://github.com/phan/phan/wiki). To run static analysis:

```bash
./vendor/bin/phan
```
