# Пакет Test Support

Некоторые компоненты кодовой базы в процессе своей работы полагаются на сервисы, отвечающие требованиям [PSR-интерфейсов](https://www.php-fig.org/psr/).
В целях тестирования таких компонентов разработчику часто приходится описывать собственные урезанные или расширенные
однотипные реализации PSR в тестовой среде.
Пакет Test Support предоставляет готовые реализации некоторых PSR интерфейсов, предназначенные исключительно для помощи в тестировании вашего кода.

## Установка

Предпочтительнее установить этот пакет через [Composer](https://getcomposer.org/download/):

```bash
composer require yiisoft/test-support --dev
```

## Container Interface [PSR-11](https://github.com/php-fig/container)

В качестве реализации `ContainerInterface` предлагается класс `SimpleContainer`.

Это простой контейнер зависимостей, конфигурацию для которого вы передаёте в конструктор.
Несмотря на простоту, контейнер гибкий: второй аргумент конструктора принимает замыкание. Оно
будет возвращать запрашиваемое у контейнера значение, если оно не задано в конфигурации.

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

Интерфейс диспетчера событий `EventDispatcherInterface` реализуется классом `SimpleEventDispatcher`.

В конструктор `SimpleEventDispatcher` вы можете передать любое количество замыканий-слушателей.
Диспетчер не содержит сложной логики для определения соответствий событий слушателям, поэтому слушатели сами должны
решать, нужно ли им обрабатывать событие.
В то же время диспетчер соответствует PSR и учитывает `StoppableEventInterface`.

Если ваш код отправил в `SimpleEventDispatcher` событие, то в тестах вы можете проверить это с помощью следующих методов:

- `isObjectTriggered::isObjectTriggered(object $event, int $times = null)` — вызван конкретный объект события `$event`.
- `isClassTriggered::isClassTriggered(string $class, int $times = null)` — вызвано событие конкретного класса `$class`.
- `isInstanceOfTriggered::isInstanceOfTriggered(string $class, int $times = null)` — вызвано событие с интерфейсом `$class`.

Второй параметр `$times` отвечает за проверку, сколько раз событие было вызвано.

## Simple Cache [PSR-16](https://github.com/php-fig/simple-cache)

Интерфейс `SimpleCacheInterface` реализуется сразу двумя классами:
`MemorySimpleCache`, который работает аналогично `ArrayCache` из пакета
[yiisoft/cache](https://github.com/yiisoft/cache),
и `SimpleCacheActionLogger`, который запоминает все команды, отправленные в `SimpleCacheInterface`.

### MemorySimpleCache

`MemorySimpleCache` не использует внешние хранилища для хранения кешируемых значений. Значения
сохраняются в виде массива в самом объекте и будут уничтожены вместе с объектом.
Используйте `MemorySimpleCache` в простых случаях, когда не нужно отслеживать историю обращений к кешу.

Для имитации ошибок кеширования в публичных свойствах `returnOnSet`, `returnOnDelete` и `returnOnClear`
можно определить возвращаемые значения для соответствующих методов `SimpleCacheInterface`.

### SimpleCacheActionLogger

Класс `SimpleCacheActionLogger` является декоратором `SimpleCacheInterface`, посредником, который запоминает все
переданные команды, даже если они невалидные (например, ключ содержит недопустимые символы или вообще не является
строкой).

Используйте декоратор тогда, когда не достаточно проверить состояние кеша до и после выполнения кода. Например, когда
ожидается, что тестируемый сервис должен повторно отправить значение в кеш после первой неудачной попытки.

```php
use Yiisoft\Test\Support\SimpleCache;

$cache = new SimpleCache\MemorySimpleCache();
// Метод MemorySimpleCache::set() будет возвращать false, что соответствует возникновению ошибки, согласно PSR
$cache->returnOnSet = false;

$cacheLogger = new SimpleCache\SimpleCacheActionLogger($cache);
$myService = new myService(/* CacheInterface */ $cacheLogger);

$myService->trySetAction('key', 'value'); // Сервис 3 раза пытается закешировать значение

\PHPUnit\Framework\TestCase::assertSame([
    [SimpleCache\Action::SET, 'key'],
    [SimpleCache\Action::SET, 'key'],
    [SimpleCache\Action::SET, 'key'],
], $cacheLogger->getActionKeyList()); // true. Logger регистрирует три попытки записи в кеш
```
