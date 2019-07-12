<?php namespace Tests\Validation;

use Framework\Language\Language;
use Framework\Validation\Validator;
use PHPUnit\Framework\TestCase;

class ValidationTest extends TestCase
{
	/**
	 * @var ValidationMock
	 */
	protected $validation;

	public function setup() : void
	{
		$this->validation = new ValidationMock();
	}

	public function testParseRule()
	{
		$this->assertEquals(
			[
				'rule' => 'foo',
				'params' => [],
			],
			$this->validation->parseRule('foo')
		);
		$this->assertEquals(
			[
				'rule' => 'foo',
				'params' => ['bar:baz'],
			],
			$this->validation->parseRule('foo:bar:baz')
		);
		$this->assertEquals(
			[
				'rule' => 'fo,o',
				'params' => ['bar:baz'],
			],
			$this->validation->parseRule('fo,o:bar:baz')
		);
		$this->assertEquals(
			[
				'rule' => 'foo',
				'params' => ['param'],
			],
			$this->validation->parseRule('foo:param')
		);
		$this->assertEquals(
			[
				'rule' => 'foo',
				'params' => ['param', 'param2'],
			],
			$this->validation->parseRule('foo:param,param2')
		);
		$this->assertEquals(
			[
				'rule' => 'foo',
				'params' => ['  param', ' param2 '],
			],
			$this->validation->parseRule('foo:  param, param2 ')
		);
		$this->assertEquals(
			[
				'rule' => 'foo',
				'params' => ['param', 'param2', 'param3'],
			],
			$this->validation->parseRule('foo:param,param2,param3')
		);
		$this->assertEquals(
			[
				'rule' => 'foo',
				'params' => ['param', 'param2,param3'],
			],
			$this->validation->parseRule('foo:param,param2\,param3')
		);
		$this->assertEquals(
			[
				'rule' => 'foo',
				'params' => ['param', 'param2\,param3'],
			],
			$this->validation->parseRule('foo:param,param2\\\,param3')
		);
	}

	public function testExtractRules()
	{
		$this->assertEquals(
			[
				[
					'rule' => 'foo',
					'params' => [],
				],
			],
			$this->validation->extractRules('foo')
		);
		$this->assertEquals(
			[
				[
					'rule' => 'foo',
					'params' => [],
				],
				[
					'rule' => 'bar',
					'params' => [],
				],
			],
			$this->validation->extractRules('foo|bar')
		);
		$this->assertEquals(
			[
				[
					'rule' => 'foo',
					'params' => [],
				],
				[
					'rule' => 'bar|baz',
					'params' => [],
				],
			],
			$this->validation->extractRules('foo|bar\|baz')
		);
		$this->assertEquals(
			[
				[
					'rule' => 'foo',
					'params' => [],
				],
				[
					'rule' => 'bar\|baz',
					'params' => [],
				],
			],
			$this->validation->extractRules('foo|bar\\\|baz')
		);
		$this->assertEquals(
			[
				[
					'rule' => 'foo',
					'params' => ['a', 'b,c'],
				],
				[
					'rule' => 'bar',
					'params' => ['|\|'],
				],
				[
					'rule' => 'baz',
					'params' => [],
				],
			],
			$this->validation->extractRules('foo:a,b\,c|bar:\|\\\||baz')
		);
	}

	public function testRule()
	{
		$this->assertEmpty($this->validation->getRules());
		$this->validation->setRule('foo', 'foo:a|bar');
		$this->assertEquals([
			'foo' => [
				[
					'rule' => 'foo',
					'params' => ['a'],
				],
				[
					'rule' => 'bar',
					'params' => [],
				],
			],
		], $this->validation->getRules());
		$this->validation->setRule('bar', ['foo:a', 'bar']);
		$this->assertEquals([
			'foo' => [
				[
					'rule' => 'foo',
					'params' => ['a'],
				],
				[
					'rule' => 'bar',
					'params' => [],
				],
			],
			'bar' => [
				[
					'rule' => 'foo',
					'params' => ['a'],
				],
				[
					'rule' => 'bar',
					'params' => [],
				],
			],
		], $this->validation->getRules());
		$this->validation->setRule('foo', 'baz');
		$this->assertEquals([
			'foo' => [
				[
					'rule' => 'baz',
					'params' => [],
				],
			],
			'bar' => [
				[
					'rule' => 'foo',
					'params' => ['a'],
				],
				[
					'rule' => 'bar',
					'params' => [],
				],
			],
		], $this->validation->getRules());
		$this->validation->setRule('baz', ['b|a|\z:s', 'x']);
		$this->assertEquals([
			'foo' => [
				[
					'rule' => 'baz',
					'params' => [],
				],
			],
			'bar' => [
				[
					'rule' => 'foo',
					'params' => ['a'],
				],
				[
					'rule' => 'bar',
					'params' => [],
				],
			],
			'baz' => [
				[
					'rule' => 'b|a|\z',
					'params' => ['s'],
				],
				[
					'rule' => 'x',
					'params' => [],
				],
			],
		], $this->validation->getRules());
	}

	public function testRules()
	{
		$this->assertEmpty($this->validation->getRules());
		$this->validation->setRules([
			'foo' => 'baz',
			'bar' => 'foo:a|bar',
			'baz' => ['b|a|\z:s', 'x'],
		]);
		$this->assertEquals([
			'foo' => [
				[
					'rule' => 'baz',
					'params' => [],
				],
			],
			'bar' => [
				[
					'rule' => 'foo',
					'params' => ['a'],
				],
				[
					'rule' => 'bar',
					'params' => [],
				],
			],
			'baz' => [
				[
					'rule' => 'b|a|\z',
					'params' => ['s'],
				],
				[
					'rule' => 'x',
					'params' => [],
				],
			],
		], $this->validation->getRules());
	}

	public function testLabel()
	{
		$this->assertEquals([], $this->validation->getLabels());
		$this->assertNull($this->validation->getLabel('foo'));
		$this->validation->setLabel('foo', 'Foo');
		$this->assertEquals('Foo', $this->validation->getLabel('foo'));
		$this->validation->setLabels(['foo' => 'Foo ', 'bar' => 'Bar']);
		$this->assertEquals(['foo' => 'Foo ', 'bar' => 'Bar'], $this->validation->getLabels());
		$this->validation->reset();
		$this->assertEquals([], $this->validation->getLabels());
	}

	public function testSetError()
	{
		$this->assertEquals([], $this->validation->getErrors());
		$this->assertNull($this->validation->getError('foo'));
		$this->validation->setError('foo', 'test', ['a', 'b']);
		$this->assertEquals(
			'validation.test',
			$this->validation->getError('foo')
		);
	}

	public function testValidate()
	{
		$this->assertTrue($this->validation->validate([]));
		$this->assertEquals([], $this->validation->getErrors());
		$this->validation->setRules([
			'name' => 'minLength:5',
			'email' => 'email',
		]);
		$this->assertFalse($this->validation->validate([]));
		$this->assertEquals(
			[
				'name' => 'The name field requires more than 5 characters in length.',
				'email' => 'The email field requires a valid email address.',
			],
			$this->validation->getErrors()
		);
	}

	public function testValidateUnknownRule()
	{
		$this->validation->setRule('name', 'foo');
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage(
			"Validation rule 'foo' not found on field 'name'"
		);
		$this->validation->validate([]);
	}

	public function testValidateOnly()
	{
		$this->assertTrue($this->validation->validateOnly([]));
		$this->assertEquals([], $this->validation->getErrors());
		$this->validation->setRules([
			'name' => 'minLength:5',
			'email' => 'email',
		]);
		$this->assertTrue($this->validation->validateOnly([]));
		$this->assertEquals([], $this->validation->getErrors());
		$this->assertFalse($this->validation->validateOnly([
			'name' => 'foo',
			'email' => 'email',
		]));
		$this->assertEquals(
			[
				'name' => 'The name field requires more than 5 characters in length.',
				'email' => 'The email field requires a valid email address.',
			],
			$this->validation->getErrors()
		);
	}

	public function testError()
	{
		$this->validation->setRules([
			'email' => 'email',
		]);
		$this->assertFalse($this->validation->validate([]));
		$this->assertEquals(
			[
				'email' => 'The email field requires a valid email address.',
			],
			$this->validation->getErrors()
		);
		$this->assertNull(
			$this->validation->getError('unknown')
		);
		$this->validation = new ValidationMock([Validator::class], new Language('en'));
		$this->validation->setRules([
			'email' => 'email',
		]);
		$this->assertFalse($this->validation->validate([]));
		$this->assertEquals(
			'The email field requires a valid email address.',
			$this->validation->getError('email')
		);
		$this->assertEquals(
			['email' => 'The email field requires a valid email address.'],
			$this->validation->getErrors()
		);
		$this->validation->setLabel('email', 'E-mail');
		$this->assertEquals(
			'The E-mail field requires a valid email address.',
			$this->validation->getError('email')
		);
		$this->assertEquals(
			['email' => 'The E-mail field requires a valid email address.'],
			$this->validation->getErrors()
		);
		$this->assertNull(
			$this->validation->getError('unknown')
		);
	}
}
