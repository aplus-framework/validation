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

use Framework\Validation\Debug\ValidationCollection;
use PHPUnit\Framework\TestCase;

final class ValidationCollectionTest extends TestCase
{
    protected ValidationCollection $collection;

    protected function setUp() : void
    {
        $this->collection = new ValidationCollection('Validation');
    }

    public function testIcon() : void
    {
        self::assertStringStartsWith('<svg ', $this->collection->getIcon());
    }
}
