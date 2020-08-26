<?php

declare(strict_types=1);

namespace Yiisoft\Test\Support\Container\Exception;

use Exception;
use Psr\Container\NotFoundExceptionInterface;
use Throwable;

final class NotFoundException extends Exception implements NotFoundExceptionInterface
{
    private string $id;

    public function __construct(string $id, $message = "", $code = 0, Throwable $previous = null)
    {
        $this->id = $id;
        parent::__construct($message, $code, $previous);
    }

    public function getId(): string
    {
        return $this->id;
    }
}
