<?php

declare(strict_types=1);

namespace Yiisoft\Test\Support\Tests\SimpleCache;

use Yiisoft\Test\Support\SimpleCache\Action;
use PHPUnit\Framework\TestCase;

final class ActionTest extends TestCase
{
    public function mixedValueProvider(): array
    {
        return [
            ['simple-key'],
            [null],
            [true],
            [[]],
        ];
    }

    /**
     * @dataProvider mixedValueProvider
     */
    public function testCreateGetAction(mixed $mixed): void
    {
        $action = Action::createGetAction($mixed);

        $this->assertSame(Action::GET, $action->getAction());
        $this->assertSame($mixed, $action->getKey());
    }

    /**
     * @dataProvider mixedValueProvider
     */
    public function testCreateHasAction(mixed $mixed): void
    {
        $action = Action::createHasAction($mixed);

        $this->assertSame(Action::HAS, $action->getAction());
        $this->assertSame($mixed, $action->getKey());
    }

    /**
     * @dataProvider mixedValueProvider
     */
    public function testCreateSetAction(mixed $mixed): void
    {
        $action = Action::createSetAction($mixed, $mixed, $mixed);

        $this->assertSame(Action::SET, $action->getAction());
        $this->assertSame($mixed, $action->getKey());
        $this->assertSame($mixed, $action->getValue());
        $this->assertSame($mixed, $action->getTtl());
    }

    public function testCreateSetActionDifferentValues(): void
    {
        $action = Action::createSetAction(1, 2, 3);

        $this->assertSame(1, $action->getKey());
        $this->assertSame(2, $action->getValue());
        $this->assertSame(3, $action->getTtl());
    }

    /**
     * @dataProvider mixedValueProvider
     */
    public function testCreateDeleteAction(mixed $mixed): void
    {
        $action = Action::createDeleteAction($mixed);

        $this->assertSame(Action::DELETE, $action->getAction());
        $this->assertSame($mixed, $action->getKey());
    }

    public function testCreateClearAction(): void
    {
        $action = Action::createClearAction();

        $this->assertSame(Action::CLEAR, $action->getAction());
    }
}
