<p align="center">
    <a href="https://github.com/yiisoft" target="_blank">
        <img src="https://yiisoft.github.io/docs/images/yii_logo.svg" height="100px" alt="Yii">
    </a>
    <h1 align="center">Yii Test Support</h1>
    <br>
</p>

[![Latest Stable Version](https://poser.pugx.org/yiisoft/test-support/v)](https://packagist.org/packages/yiisoft/test-support)
[![Total Downloads](https://poser.pugx.org/yiisoft/test-support/downloads)](https://packagist.org/packages/yiisoft/test-support)
[![Build status](https://github.com/yiisoft/test-support/actions/workflows/build.yml/badge.svg)](https://github.com/yiisoft/test-support/actions/workflows/build.yml)
[![Code Coverage](https://codecov.io/gh/yiisoft/test-support/branch/master/graph/badge.svg)](https://codecov.io/gh/yiisoft/test-support)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fyiisoft%2Ftest-support%2Fmaster)](https://dashboard.stryker-mutator.io/reports/github.com/yiisoft/test-support/master)
[![Static analysis](https://github.com/yiisoft/test-support/actions/workflows/static.yml/badge.svg?branch=master)](https://github.com/yiisoft/test-support/actions/workflows/static.yml?query=branch%3Amaster)
[![type-coverage](https://shepherd.dev/github/yiisoft/test-support/coverage.svg)](https://shepherd.dev/github/yiisoft/test-support)

The package is intended to simplify the process of testing application elements that depend on PSR interfaces.

## Requirements

- PHP 8.0 - 8.5.

## Installation

The package could be installed with [Composer](https://getcomposer.org):

```shell
composer require yiisoft/test-support --dev
```

In case you need to satisfy PSR virtual packages (`*-implementation` requirements), add the following to `require-dev`
as well:

```shell
"yiisoft/psr-dummy-provider": "1.0"
```

## Documentation

- Guide: [English](docs/guide/en/README.md), [Português - Brasil](docs/guide/pt-BR/README.md), [Русский](docs/guide/ru/README.md)
- [Internals](docs/internals.md)

If you need help or have a question, the [Yii Forum](https://forum.yiiframework.com/c/yii-3-0/63) is a good place for that.
You may also check out other [Yii Community Resources](https://www.yiiframework.com/community).

## License

The Yii Test Support is free software. It is released under the terms of the BSD License.
Please see [`LICENSE`](./LICENSE.md) for more information.

Maintained by [Yii Software](https://www.yiiframework.com/).

## Support the project

[![Open Collective](https://img.shields.io/badge/Open%20Collective-sponsor-7eadf1?logo=open%20collective&logoColor=7eadf1&labelColor=555555)](https://opencollective.com/yiisoft)

## Follow updates

[![Official website](https://img.shields.io/badge/Powered_by-Yii_Framework-green.svg?style=flat)](https://www.yiiframework.com/)
[![Twitter](https://img.shields.io/badge/twitter-follow-1DA1F2?logo=twitter&logoColor=1DA1F2&labelColor=555555?style=flat)](https://twitter.com/yiiframework)
[![Telegram](https://img.shields.io/badge/telegram-join-1DA1F2?style=flat&logo=telegram)](https://t.me/yii3en)
[![Facebook](https://img.shields.io/badge/facebook-join-1DA1F2?style=flat&logo=facebook&logoColor=ffffff)](https://www.facebook.com/groups/yiitalk)
[![Slack](https://img.shields.io/badge/slack-join-1DA1F2?style=flat&logo=slack)](https://yiiframework.com/go/slack)
