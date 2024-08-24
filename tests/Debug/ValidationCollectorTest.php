<?php
/*
 * This file is part of Aplus Framework Validation Library.
 *
 * (c) Natan Felles <natanfelles@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tests\Validation\Debug;

use Framework\Validation\Debug\ValidationCollector;
use Framework\Validation\Validation;
use Framework\Validation\Validator;
use PHPUnit\Framework\TestCase;

final class ValidationCollectorTest extends TestCase
{
    protected ValidationCollector $collector;

    protected function setUp() : void
    {
        $this->collector = new ValidationCollector();
    }

    protected function makeValidation() : Validation
    {
        $validation = new Validation();
        $validation->setDebugCollector($this->collector);
        return $validation;
    }

    public function testNoValidation() : void
    {
        self::assertStringContainsString(
            'A Validation instance has not been set',
            $this->collector->getContents()
        );
    }

    public function testRuleset() : void
    {
        $validation = $this->makeValidation();
        self::assertStringContainsString(
            'No rules have been set.',
            $this->collector->getContents()
        );
        $validation->setRule('email', 'required|email')->setRule('foo', 'foo');
        self::assertStringContainsString(
            'The following rules have been set',
            $this->collector->getContents()
        );
    }

    public function testValidationNotRun() : void
    {
        $this->makeValidation();
        self::assertStringContainsString(
            'Validation did not run',
            $this->collector->getContents()
        );
    }

    public function testValidationRun() : void
    {
        $validation = $this->makeValidation();
        $validation->validate([]);
        self::assertStringContainsString(
            'Validation ran 1 time',
            $this->collector->getContents()
        );
        $validation->setRule('name', 'minLength:5')->validateOnly([]);
        self::assertStringContainsString(
            'Validation ran 2 times',
            $this->collector->getContents()
        );
        $validation->setRule('email', 'required|email')->validate([]);
        self::assertStringContainsString(
            'Validation ran 3 times',
            $this->collector->getContents()
        );
    }

    public function testCustomValidatorsRules() : void
    {
        $validator = new class() extends Validator {
            // @phpstan-ignore-next-line
            public static function foo(
                string $field,
                array $data,
                int $int,
                float $float,
                $noType,
                mixed ...$spread
            ) : bool {
                return false;
            }

            // @phpstan-ignore-next-line
            public static function bar(
                string $field,
                array $data,
                string $str = 'foo',
                ?string $null = null
            ) : bool {
                return false;
            }
        };
        $validation = new Validation([$validator]);
        $validation->setDebugCollector($this->collector);
        $contents = $this->collector->getContents();
        self::assertStringContainsString(
            Validator::class,
            $contents
        );
        self::assertStringContainsString(
            $validator::class,
            $contents
        );
        self::assertStringContainsString(
            'alpha',
            $contents
        );
        self::assertStringContainsString(
            'foo',
            $contents
        );
        self::assertStringContainsString(
            '$int, $float, $noType, ...$spread',
            $contents
        );
        self::assertStringContainsString(
            'bar',
            $contents
        );
        self::assertStringContainsString(
            \htmlentities('$str = \'foo\', $null = null'),
            $contents
        );
    }

    public function testActivities() : void
    {
        $validation = $this->makeValidation();
        self::assertEmpty($this->collector->getActivities());
        $validation->validate([]);
        self::assertSame(
            [
                'collector',
                'class',
                'description',
                'start',
                'end',
            ],
            \array_keys($this->collector->getActivities()[0]) // @phpstan-ignore-line
        );
    }

    public function testValidationCollectorInstance() : void
    {
        $validation = new Validation();
        self::assertNull($validation->getDebugCollector());
        $validation->setDebugCollector($this->collector);
        self::assertInstanceOf(
            ValidationCollector::class,
            $validation->getDebugCollector()
        );
    }

    public function testSetErrorInDebugData() : void
    {
        $data = $this->collector->getData();
        self::assertEmpty($data);
        $this->collector->setErrorInDebugData('email', 'Email error.');
        $data = $this->collector->getData();
        self::assertCount(0, $data);
        $this->collector->addData([
            'start' => 1676932480,
            'end' => 1676932490,
            'validated' => false,
            'errors' => ['foo' => 'Foo error.'],
            'type' => 'all',
        ]);
        $data = $this->collector->getData();
        self::assertCount(1, $data[0]['errors']);
        $this->collector->setErrorInDebugData('email', 'Email error.');
        $data = $this->collector->getData();
        self::assertCount(2, $data[0]['errors']);
        $this->collector->setErrorInDebugData('bar', 'Bar error.', 1);
        $data = $this->collector->getData();
        self::assertCount(1, $data[1]['errors']);
    }
}
