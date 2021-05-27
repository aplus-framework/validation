<?php namespace Tests\Validation;

use Framework\Validation\FilesValidator;
use PHPUnit\Framework\TestCase;

class FilesValidatorTest extends TestCase
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

	public function testOrganizedFiles()
	{
		$this->assertEquals([
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

	public function testUploaded()
	{
		$this->assertFalse(FilesValidator::uploaded('foo[a]'));
		$this->assertFalse(FilesValidator::uploaded('bar'));
		$this->assertFalse(FilesValidator::uploaded('unknown'));
	}

	public function testMaxSize()
	{
		$this->assertFalse(FilesValidatorMock::maxSize('foo[a]', [], 10));
		$this->assertTrue(FilesValidatorMock::maxSize('foo[a]', [], 40));
		$this->assertFalse(FilesValidator::maxSize('foo[a]', [], 40));
	}

	public function testMimeTypes()
	{
		$this->assertFalse(FilesValidatorMock::mimes('foo[a]', [], 'application/json'));
		$this->assertTrue(
			FilesValidatorMock::mimes('foo[a]', [], 'application/json', 'image/png')
		);
		$this->assertFalse(
			FilesValidator::mimes('foo[a]', [], 'application/json', 'image/png')
		);
	}

	public function testExtensions()
	{
		$this->assertFalse(FilesValidatorMock::ext('foo[a]', [], 'pdf', 'svg'));
		$this->assertTrue(FilesValidatorMock::ext('foo[a]', [], 'pdf', 'png'));
		$this->assertFalse(FilesValidator::ext('foo[a]', [], 'pdf', 'png'));
	}

	public function testImage()
	{
		$this->assertTrue(FilesValidatorMock::image('foo[a]'));
		$this->assertFalse(FilesValidator::image('foo[a]'));
	}

	public function testMaxDimensions()
	{
		$this->assertFalse(FilesValidatorMock::maxDim('foo[a]', [], 200, 200));
		$this->assertTrue(FilesValidatorMock::maxDim('foo[a]', [], 400, 500));
		$this->assertFalse(FilesValidator::maxDim('foo[a]', [], 400, 500));
	}

	public function testMinDimensions()
	{
		$this->assertFalse(FilesValidatorMock::minDim('foo[a]', [], 500, 500));
		$this->assertTrue(FilesValidatorMock::minDim('foo[a]', [], 300, 400));
		$this->assertFalse(FilesValidator::minDim('foo[a]', [], 300, 500));
	}

	public function testDimensions()
	{
		$this->assertFalse(FilesValidatorMock::dim('foo[a]', [], 500, 500));
		$this->assertTrue(FilesValidatorMock::dim('foo[a]', [], 400, 400));
		$this->assertFalse(FilesValidator::dim('foo[a]', [], 300, 500));
	}
}
