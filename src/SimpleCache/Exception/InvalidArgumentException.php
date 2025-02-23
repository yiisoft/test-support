<?php

declare(strict_types=1);

namespace Yiisoft\Test\Support\SimpleCache\Exception;

/**
 * @final
 */
class InvalidArgumentException extends \InvalidArgumentException implements \Psr\SimpleCache\InvalidArgumentException
{
}
