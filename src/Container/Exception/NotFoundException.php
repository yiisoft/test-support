<?php

declare(strict_types=1);

namespace Yiisoft\Test\Support\Container\Exception;

use Exception;
use Psr\Container\NotFoundExceptionInterface;

final class NotFoundException extends Exception implements NotFoundExceptionInterface
{
    private string $id;

    public function __construct(string $id)
    {
        $this->id = $id;
        parent::__construct("No definition or class found for \"$id\".");
    }

    public function getId(): string
    {
        return $this->id;
    }
}
