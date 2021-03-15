# Test Support package

Some components of the application codebase rely on services implementing [PSR-interfaces](https://www.php-fig.org/psr/).
To test such components, the developer often has to write his own tests-sepcific versions of similar PSR implementations.
Test Support package provides ready-to-use implementations of some PSR interfaces, intended solely to help testing your code.


## Installation

The preferred way to install this package is through [Composer](https://getcomposer.org/download/):

```bash
composer require yiisoft/test-support --dev
```

## Logger Interface [PSR-3](https://github.com/php-fig/log)

The package provides the `SimpleLogger` class, which is an implementation of `LoggerInterface`.

The `SimpleLogger` instance stores all logged messages in an array, which is destroyed along with the instance itself.
To get all logged messages, use the `getMessages()` method.

```php
$logger = new Yiisoft\Test\Support\Log\SimpleLogger();

$logger->emergency('Emergency message', ['key' => 'value']);
$logger->alert('Alert message', ['key' => 'value']);
$logger->critical('Critical message', ['key' => 'value']);
$logger->error('Error message', ['key' => 'value']);
$logger->warning('Warning message', ['key' => 'value']);
$logger->notice('Notice message', ['key' => 'value']);
$logger->info('Info message', ['key' => 'value']);
$logger->debug('Debug message', ['key' => 'value']);

$messages = $logger->getMessages();
/*
[
    ...
    ['level' => 'error', 'message' => 'Error message', 'context' => ['key' => 'value']];
    ['level' => 'warning', 'message' => 'Warning message', 'context' => ['key' => 'value']];
    ...
];
*/
```

## Container Interface [PSR-11](https://github.com/php-fig/container)

The `SimpleContainer` is a simple dependency container accepting the definitions configuration as the constructor argument. Despite the simplicity, container is flexible: 2nd parameter of the constructor accepts a Closure.
This Closure will be called to get "default" value if the requested value is not found if the configuration:

```php
use Yiisoft\Test\Support\Container\SimpleContainer;

$container = new SimpleContainer(
    ['foo' => 'Foo'],
    fn (string $id) => $id === 'bar' ? 'Bar' : 'Not found'
);
$foo = $container->get('foo'); // Foo
$foo = $container->get('bar'); // Bar
$baz = $container->get('baz'); // Not found
```

## Event Dispatcher [PSR-14](https://github.com/php-fig/event-dispatcher)

The `SimpleEventDispatcher` is a test-specific event dispatcher. You can pass any number of closure-listeners to its  constructor. SimpleEventDispatcher` does not contain any complex logic for matching an event to a listener. Every listener should decide by itself if it processes an event or not. The dispatcher is PSR-compliant and works with `StoppableEventInterface`.

You can test for events sent to `SimpleEventDispatcher` using the following methods:

- `isObjectTriggered::isObjectTriggered(object $event, int $times = null)` — Exact `$event` object was triggered.
- `isClassTriggered::isClassTriggered(string $class, int $times = null)` — Event of `$class` class was triggered.
- `isInstanceOfTriggered::isInstanceOfTriggered(string $class, int $times = null)` — Event with `$class` interface was triggered.

2nd parameter `$times`, is responsible for checking how many times the event has been called.

## Simple Cache [PSR-16](https://github.com/php-fig/simple-cache)

The `SimpleCacheInterface` interface is implemented by two classes:

1. `MemorySimpleCache`, which works similarly to `ArrayCache` from a 
[yiisoft/cache](https://github.com/yiisoft/cache) package, and

2. `SimpleCacheActionLogger`. It stores all commands, sent to `SimpleCacheInterface`.

### MemorySimpleCache

The `MemorySimpleCache` class does not use external storage to store cached values.
Values are stored in the array property of the object itself and will be destroyed along with the object.
Use `MemorySimpleCache` in the simple cases when you do not need to keep track of the history of cache access. 

You can simulate cache errors by setting public properties `returnOnSet`, `returnOnDelete` and `returnOnClear`. These define values returned by the corresponding methods of the `SimpleCacheInterface`.
 
### SimpleCacheActionLogger

The `SimpleCacheActionLogger` class is a decorator for `SimpleCacheInterface`. It remembers all cache calls even if they are invalid such as when the key contains invalid characters, or is not a string at all. 

Use the decorator when it is not enough to test the cache state before and after code execution.
For example, when the testing service is supposed to resend the value to the cache after the first failed attempt.

```php
use Yiisoft\Test\Support\SimpleCache;

$cache = new SimpleCache\MemorySimpleCache();
//MemorySimpleCache::set() method will return false, which is an error according to PSR
$cache->returnOnSet = false;

$cacheLogger = new SimpleCache\SimpleCacheActionLogger($cache);
$myService = new myService(/* CacheInterface */ $cacheLogger);

$myService->trySetAction('key', 'value'); // Service tries to cache value 3 times

\PHPUnit\Framework\TestCase::assertSame([
    [SimpleCache\Action::SET, 'key'],
    [SimpleCache\Action::SET, 'key'],
    [SimpleCache\Action::SET, 'key'],
], $cacheLogger->getActionKeyList()); // true. Logger registers 3 tries to set cache
```
