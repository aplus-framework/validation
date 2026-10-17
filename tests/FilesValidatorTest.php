<?php declare(strict_types=1);
/*
 * This file is part of Aplus Framework Validation Library.
 *
 * (c) Natan Felles <natanfelles@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tests\Validation;

use Framework\Validation\FilesValidator;
use PHPUnit\Framework\TestCase;

final class FilesValidatorTest extends TestCase
{
    protected function setUp() : void
    {
        $_FILES = [
            'foo' => [
                'name' => ['a' => 'logo-circle.png'],
                'type' => ['a' => 'image/png'],
                'tmp_name' => ['a' => __DIR__ . '/files/logo-circle.png'],
                'error' => ['a' => 0],
                'size' => ['a' => 18688],
            ],
            'bar' => [
                'name' => 'desenhando.svg',
                'type' => 'image/svg+xml',
                'tmp_name' => __DIR__ . '/files/desenhando.svg',
                'error' => 0,
                'size' => 1960,
            ],
            'baz' => [
                'name' => [
                    'x' => [
                        'y' => 'logo-circle.png',
                    ],
                ],
                'type' => [
                    'x' => [
                        'y' => 'image/png',
                    ],
                ],
                'tmp_name' => [
                    'x' => [
                        'y' => '/tmp/phplXINR3',
                    ],
                ],
                'error' => [
                    'x' => [
                        'y' => 0,
                    ],
                ],
                'size' => [
                    'x' => [
                        'y' => 18688,
                    ],
                ],
            ],
        ];
    }

    public function testOrganizedFiles() : void
    {
        self::assertSame([
            'foo' => [
                'a' => [
                    'name' => 'logo-circle.png',
                    'type' => 'image/png',
                    'tmp_name' => __DIR__ . '/files/logo-circle.png',
                    'error' => 0,
                    'size' => 18688,
                ],
            ],
            'bar' => [
                'name' => 'desenhando.svg',
                'type' => 'image/svg+xml',
                'tmp_name' => __DIR__ . '/files/desenhando.svg',
                'error' => 0,
                'size' => 1960,
            ],
            'baz' => [
                'x' => [
                    'y' => [
                        'name' => 'logo-circle.png',
                        'type' => 'image/png',
                        'tmp_name' => '/tmp/phplXINR3',
                        'error' => 0,
                        'size' => 18688,
                    ],
                ],
            ],
        ], FilesValidatorMock::getOrganizedFiles());
    }

    public function testUploaded() : void
    {
        self::assertFalse(FilesValidator::uploaded('foo[a]'));
        self::assertFalse(FilesValidator::uploaded('bar'));
        self::assertFalse(FilesValidator::uploaded('unknown'));
        self::assertTrue(FilesValidatorMock::uploaded('foo[a]'));
        self::assertTrue(FilesValidatorMock::uploaded('bar'));
        self::assertFalse(FilesValidatorMock::uploaded('unknown'));
    }

    public function testMaxSize() : void
    {
        self::assertFalse(FilesValidatorMock::maxSize('foo[a]', [], 10, 'b'));
        self::assertFalse(FilesValidatorMock::maxSize('foo[a]', [], 10, 'kb'));
        self::assertTrue(FilesValidatorMock::maxSize('foo[a]', [], 10, 'mb'));
        self::assertTrue(FilesValidatorMock::maxSize('foo[a]', [], 10, 'gb'));
        self::assertTrue(FilesValidatorMock::maxSize('foo[a]', [], 10, 'tb'));
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid unit of measurement: foo');
        FilesValidatorMock::maxSize('foo[a]', [], 10, 'foo');
    }

    public function testMaxSizeUploadedFail() : void
    {
        self::assertFalse(FilesValidator::maxSize('foo[a]', [], 10, 'b'));
    }

    public function testMimeTypes() : void
    {
        self::assertFalse(FilesValidatorMock::mimes('foo[a]', [], 'application/json'));
        self::assertTrue(
            FilesValidatorMock::mimes('foo[a]', [], 'application/json', 'image/png')
        );
    }

    public function testMimeTypesUploadedFail() : void
    {
        self::assertFalse(
            FilesValidator::mimes('foo[a]', [], 'application/json', 'image/png')
        );
    }

    public function testExtensions() : void
    {
        self::assertFalse(FilesValidatorMock::ext('foo[a]', [], 'pdf', 'svg'));
        self::assertTrue(FilesValidatorMock::ext('foo[a]', [], 'pdf', 'png'));
    }

    public function testExtensionsUploadedFail() : void
    {
        self::assertFalse(FilesValidator::ext('foo[a]', [], 'pdf', 'png'));
    }

    public function testImage() : void
    {
        self::assertTrue(FilesValidatorMock::image('foo[a]'));
    }

    public function testImageUploadedFail() : void
    {
        self::assertFalse(FilesValidator::image('foo[a]'));
    }

    public function testMaxDimensions() : void
    {
        self::assertFalse(FilesValidatorMock::maxDim('foo[a]', [], 200, 200));
        self::assertFalse(FilesValidatorMock::maxDim('foo[a]', [], '200', '200'));
        self::assertTrue(FilesValidatorMock::maxDim('foo[a]', [], 400, 500));
        self::assertTrue(FilesValidatorMock::maxDim('foo[a]', [], '400', '500'));
    }

    public function testMaxDimensionsUploadedFail() : void
    {
        self::assertFalse(FilesValidator::maxDim('foo[a]', [], 400, 500));
    }

    public function testMinDimensions() : void
    {
        self::assertFalse(FilesValidatorMock::minDim('foo[a]', [], 500, 500));
        self::assertFalse(FilesValidatorMock::minDim('foo[a]', [], '500', '500'));
        self::assertTrue(FilesValidatorMock::minDim('foo[a]', [], 300, 400));
        self::assertTrue(FilesValidatorMock::minDim('foo[a]', [], '300', '400'));
    }

    public function testMinDimensionsUploadedFail() : void
    {
        self::assertFalse(FilesValidator::minDim('foo[a]', [], 300, 500));
    }

    public function testDimensions() : void
    {
        self::assertFalse(FilesValidatorMock::dim('foo[a]', [], 500, 500));
        self::assertFalse(FilesValidatorMock::dim('foo[a]', [], '500', '500'));
        self::assertTrue(FilesValidatorMock::dim('foo[a]', [], 400, 400));
        self::assertTrue(FilesValidatorMock::dim('foo[a]', [], '400', '400'));
    }

    public function testDimensionsUploadedFail() : void
    {
        self::assertFalse(FilesValidator::dim('foo[a]', [], 300, 500));
    }
}
