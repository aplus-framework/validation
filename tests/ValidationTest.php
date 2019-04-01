<?php namespace Tests\Validation;

use PHPUnit\Framework\TestCase;

class ValidationTest extends TestCase
{
	/**
	 * @var ValidationMock
	 */
	protected $validation;

	public function setup()
	{
		$this->validation = new ValidationMock([]);
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
}
