# Pacote de suporte de teste

Alguns componentes da base de código do aplicativo dependem de serviços que implementam [interfaces PSR](https://www.php-fig.org/psr/).
Para testar esses componentes, o desenvolvedor geralmente precisa escrever suas próprias versões específicas de testes de implementação PSR semelhantes.
O pacote Test Support fornece implementações prontas para uso de algumas interfaces PSR, destinadas exclusivamente a ajudar a testar seu código.

## Instalação

A forma preferida de instalar este pacote é através do [Composer](https://getcomposer.org/download/):

```shell
composer require yiisoft/test-support --dev
```

## Interface do Logger [PSR-3](https://github.com/php-fig/log)

O pacote fornece a classe `SimpleLogger`, que é uma implementação de `LoggerInterface`.

A instância `SimpleLogger` armazena todas as mensagens registradas em um array, que é destruído junto com a própria instância.
Para obter todas as mensagens registradas, use o método `getMessages()`.

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

## Interface do contêiner [PSR-11](https://github.com/php-fig/container)

O `SimpleContainer` é um contêiner de dependência simples que aceita a configuração das definições como argumento do construtor. Apesar da simplicidade, o container é flexível: o 2º parâmetro do construtor aceita um Closure.
Este Closure será chamado para obter o valor "default" se o valor solicitado não for encontrado se a configuração:

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

## Dispatcher de eventos [PSR-14](https://github.com/php-fig/event-dispatcher)

O `SimpleEventDispatcher` é um dispatcher de eventos específico de teste. Você pode passar qualquer número de closure-listeners para seu construtor. `SimpleEventDispatcher` não contém nenhuma lógica complexa para combinar um evento com um listener. Cada listener deve decidir por si mesmo se processa um evento ou não. O dispatcher é compatível com PSR e funciona com `StoppableEventInterface`.

Você pode testar eventos enviados para `SimpleEventDispatcher` usando os seguintes métodos:

- `isObjectTriggered::isObjectTriggered(object $event, int $times = null)` — O objeto `$event` exato foi acionado.
- `isClassTriggered::isClassTriggered(string $class, int $times = null)` — Evento da classe `$class` foi acionado.
- `isInstanceOfTriggered::isInstanceOfTriggered(string $class, int $times = null)` — Evento com interface `$class` foi acionado.

O 2º parâmetro `$times`, é responsável por verificar quantas vezes o evento foi chamado.

## Cache Simples [PSR-16](https://github.com/php-fig/simple-cache)

A interface `SimpleCacheInterface` é implementada por duas classes:

1. `MemorySimpleCache`, que funciona de forma semelhante a `ArrayCache` de um
pacote [yiisoft/cache](https://github.com/yiisoft/cache), e

2. `SimpleCacheActionLogger`. Ele armazena todos os comandos enviados para `SimpleCacheInterface`.

### MemorySimpleCache

A classe `MemorySimpleCache` não usa armazenamento externo para armazenar valores em cache.
Os valores são armazenados na propriedade array do próprio objeto e serão destruídos junto com o objeto.
Use `MemorySimpleCache` nos casos simples quando você não precisa acompanhar o histórico de acesso ao cache.

Você pode simular erros de cache definindo as propriedades públicas `returnOnSet`, `returnOnDelete` e `returnOnClear`. Estes definem valores retornados pelos métodos correspondentes do `SimpleCacheInterface`.

### SimpleCacheActionLogger

A classe `SimpleCacheActionLogger` é um decorator para `SimpleCacheInterface`. Ele lembra todas as chamadas de cache, mesmo que sejam inválidas, como quando a chave contém caracteres inválidos ou não é uma string.

Use o decorator quando não for suficiente testar o estado do cache antes e depois da execução do código.
Por exemplo, quando o serviço de teste deve reenviar o valor para o cache após a primeira tentativa fracassada.

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
