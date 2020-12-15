# Test Support package

Some components of the application codebase rely on services implementing [PSR-interfaces](https://www.php-fig.org/psr/).
In order to test such components, the developer often has to write his own stripped or extended realizations 
of similar PSR implementations in test environment.
Test Support package provides ready-to-use implementations of some PSR interfaces, intended solely to help testing your code.


## Installation

The preferred way to install this package is through [Composer](https://getcomposer.org/download/):

```bash
composer require yiisoft/test-support --dev
```

## Container Interface [PSR-11](https://github.com/php-fig/container)

The `SimpleContainer` class is offered as an implementation of `ContainerInterface`. 

This is simple dependency container, the definitions configuration for which you pass in the constructor.
Despite the simplicity, container is flexible: 2nd parameter of the constructor accepts a Closure.
This Closure will be called to get 'default' value, if the requested value is not found if the configuration:

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

If your code sent an event to `SimpleEventDispatcher`, then in tests you can check this using the following methods:

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

The `MemorySimpleCache` class does not use external storage, to store cached values.
Values stores in the array in the object itself and will be destroyed along with the object.
Use `MemorySimpleCache` in the simple cases, when you dont need to keep track the history of calls to the cache. 

For caching errors simulation, in the public properties `returnOnSet`, `returnOnDelete` and `returnOnClear`
you can define return values for the corresponding methods of the `SimpleCacheInterface`.
 
### SimpleCacheActionLogger

The `SimpleCacheActionLogger` class is the decorator for `SimpleCacheInterface`, intermediary, that remembers all passed 
commands, even if they are invalid (the key contains invalid characters, or is not string at all, for example). 

Use decorator when it's not enough to test the cache state before and after code execution.
For example, when the testing service supposed to resend the value to the cache after the first failed attempt.  

```php
use Yiisoft\Test\Support\SimpleCache;

$cache = new SimpleCache\MemorySimpleCache();
//MemorySimpleCache::set() method will be returning false, which corresponds to an error, accourding to PSR
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
