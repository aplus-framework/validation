<?php
/*
 * This file is part of Aplus Framework Validation Library.
 *
 * (c) Natan Felles <natanfelles@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tests\Validation;

use Framework\Language\Language;
use Framework\Validation\FilesValidator;
use Framework\Validation\Validator;
use PHPUnit\Framework\TestCase;

final class ValidationTest extends TestCase
{
    protected ValidationMock $validation;

    public function setup() : void
    {
        $this->validation = new ValidationMock();
    }

    public function testGetLanguage() : void
    {
        self::assertInstanceOf(Language::class, $this->validation->getLanguage());
    }

    public function testGetValidators() : void
    {
        self::assertSame([
            Validator::class,
            FilesValidator::class,
        ], $this->validation->getValidators());
    }

    public function testParseRule() : void
    {
        self::assertSame(
            [
                'rule' => 'foo',
                'args' => [],
            ],
            $this->validation->parseRule('foo')
        );
        self::assertSame(
            [
                'rule' => 'foo',
                'args' => ['bar:baz'],
            ],
            $this->validation->parseRule('foo:bar:baz')
        );
        self::assertSame(
            [
                'rule' => 'fo,o',
                'args' => ['bar:baz'],
            ],
            $this->validation->parseRule('fo,o:bar:baz')
        );
        self::assertSame(
            [
                'rule' => 'foo',
                'args' => ['param'],
            ],
            $this->validation->parseRule('foo:param')
        );
        self::assertSame(
            [
                'rule' => 'foo',
                'args' => ['param', 'param2'],
            ],
            $this->validation->parseRule('foo:param,param2')
        );
        self::assertSame(
            [
                'rule' => 'foo',
                'args' => ['  param', ' param2 '],
            ],
            $this->validation->parseRule('foo:  param, param2 ')
        );
        self::assertSame(
            [
                'rule' => 'foo',
                'args' => ['param', 'param2', 'param3'],
            ],
            $this->validation->parseRule('foo:param,param2,param3')
        );
        self::assertSame(
            [
                'rule' => 'foo',
                'args' => ['param', 'param2,param3'],
            ],
            $this->validation->parseRule('foo:param,param2\,param3')
        );
        self::assertSame(
            [
                'rule' => 'foo',
                'args' => ['param', 'param2\,param3'],
            ],
            $this->validation->parseRule('foo:param,param2\\\,param3')
        );
    }

    public function testExtractRules() : void
    {
        self::assertSame(
            [
                [
                    'rule' => 'foo',
                    'args' => [],
                ],
            ],
            $this->validation->extractRules('foo')
        );
        self::assertSame(
            [
                [
                    'rule' => 'foo',
                    'args' => [],
                ],
                [
                    'rule' => 'bar',
                    'args' => [],
                ],
            ],
            $this->validation->extractRules('foo|bar')
        );
        self::assertSame(
            [
                [
                    'rule' => 'foo',
                    'args' => [],
                ],
                [
                    'rule' => 'bar|baz',
                    'args' => [],
                ],
            ],
            $this->validation->extractRules('foo|bar\|baz')
        );
        self::assertSame(
            [
                [
                    'rule' => 'foo',
                    'args' => [],
                ],
                [
                    'rule' => 'bar\|baz',
                    'args' => [],
                ],
            ],
            $this->validation->extractRules('foo|bar\\\|baz')
        );
        self::assertSame(
            [
                [
                    'rule' => 'foo',
                    'args' => ['a', 'b,c'],
                ],
                [
                    'rule' => 'bar',
                    'args' => ['|\|'],
                ],
                [
                    'rule' => 'baz',
                    'args' => [],
                ],
            ],
            $this->validation->extractRules('foo:a,b\,c|bar:\|\\\||baz')
        );
    }

    public function testRule() : void
    {
        self::assertEmpty($this->validation->getRules());
        $this->validation->setRule('foo', 'foo:a|bar');
        self::assertSame([
            'foo' => [
                [
                    'rule' => 'foo',
                    'args' => ['a'],
                ],
                [
                    'rule' => 'bar',
                    'args' => [],
                ],
            ],
        ], $this->validation->getRules());
        $this->validation->setRule('bar', ['foo:a', 'bar']);
        self::assertSame([
            'foo' => [
                [
                    'rule' => 'foo',
                    'args' => ['a'],
                ],
                [
                    'rule' => 'bar',
                    'args' => [],
                ],
            ],
            'bar' => [
                [
                    'rule' => 'foo',
                    'args' => ['a'],
                ],
                [
                    'rule' => 'bar',
                    'args' => [],
                ],
            ],
        ], $this->validation->getRules());
        $this->validation->setRule('foo', 'baz');
        self::assertSame([
            'foo' => [
                [
                    'rule' => 'baz',
                    'args' => [],
                ],
            ],
            'bar' => [
                [
                    'rule' => 'foo',
                    'args' => ['a'],
                ],
                [
                    'rule' => 'bar',
                    'args' => [],
                ],
            ],
        ], $this->validation->getRules());
        $this->validation->setRule('baz', ['b|a|\z:s', 'x']);
        self::assertSame([
            'foo' => [
                [
                    'rule' => 'baz',
                    'args' => [],
                ],
            ],
            'bar' => [
                [
                    'rule' => 'foo',
                    'args' => ['a'],
                ],
                [
                    'rule' => 'bar',
                    'args' => [],
                ],
            ],
            'baz' => [
                [
                    'rule' => 'b|a|\z',
                    'args' => ['s'],
                ],
                [
                    'rule' => 'x',
                    'args' => [],
                ],
            ],
        ], $this->validation->getRules());
    }

    public function testRules() : void
    {
        self::assertEmpty($this->validation->getRules());
        $this->validation->setRules([
            'foo' => 'baz',
            'bar' => 'foo:a|bar',
            'baz' => ['b|a|\z:s', 'x'],
        ]);
        self::assertSame([
            'foo' => [
                [
                    'rule' => 'baz',
                    'args' => [],
                ],
            ],
            'bar' => [
                [
                    'rule' => 'foo',
                    'args' => ['a'],
                ],
                [
                    'rule' => 'bar',
                    'args' => [],
                ],
            ],
            'baz' => [
                [
                    'rule' => 'b|a|\z',
                    'args' => ['s'],
                ],
                [
                    'rule' => 'x',
                    'args' => [],
                ],
            ],
        ], $this->validation->getRules());
    }

    public function testLabel() : void
    {
        self::assertSame([], $this->validation->getLabels());
        self::assertNull($this->validation->getLabel('foo'));
        $this->validation->setLabel('foo', 'Foo');
        self::assertSame('Foo', $this->validation->getLabel('foo'));
        $this->validation->setLabels(['foo' => 'Foo ', 'bar' => 'Bar']);
        self::assertSame(['foo' => 'Foo ', 'bar' => 'Bar'], $this->validation->getLabels());
        $this->validation->reset();
        self::assertSame([], $this->validation->getLabels());
    }

    public function testSetError() : void
    {
        self::assertSame([], $this->validation->getErrors());
        self::assertNull($this->validation->getError('foo'));
        $this->validation->setError('foo', 'test', ['a', 'b']);
        self::assertSame(
            'validation.test',
            $this->validation->getError('foo')
        );
    }

    public function testValidate() : void
    {
        self::assertTrue($this->validation->validate([]));
        self::assertSame([], $this->validation->getErrors());
        $this->validation->setRules([
            'name' => 'minLength:5',
            'email' => 'email',
        ]);
        self::assertFalse($this->validation->validate([]));
        self::assertSame(
            [
                'name' => 'The name field requires 5 or more characters in length.',
                'email' => 'The email field requires a valid email address.',
            ],
            $this->validation->getErrors()
        );
    }

    public function testValidateUnknownRule() : void
    {
        $this->validation->setRule('name', 'foo');
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            "Validation rule 'foo' not found on field 'name'"
        );
        $this->validation->validate([]);
    }

    public function testValidateOnly() : void
    {
        self::assertTrue($this->validation->validateOnly([]));
        self::assertSame([], $this->validation->getErrors());
        $this->validation->setRules([
            'name' => 'minLength:5',
            'email' => 'email',
        ]);
        self::assertTrue($this->validation->validateOnly([]));
        self::assertSame([], $this->validation->getErrors());
        self::assertFalse($this->validation->validateOnly([
            'name' => 'foo',
            'email' => 'email',
        ]));
        self::assertSame(
            [
                'name' => 'The name field requires 5 or more characters in length.',
                'email' => 'The email field requires a valid email address.',
            ],
            $this->validation->getErrors()
        );
    }

    public function testError() : void
    {
        $this->validation->setRules([
            'email' => 'email',
        ]);
        self::assertFalse($this->validation->validate([]));
        self::assertSame(
            [
                'email' => 'The email field requires a valid email address.',
            ],
            $this->validation->getErrors()
        );
        self::assertNull(
            $this->validation->getError('unknown')
        );
        $this->validation = new ValidationMock([Validator::class], new Language('en'));
        $this->validation->setRules([
            'email' => 'email',
        ]);
        self::assertFalse($this->validation->validate([]));
        self::assertSame(
            'The email field requires a valid email address.',
            $this->validation->getError('email')
        );
        self::assertSame(
            ['email' => 'The email field requires a valid email address.'],
            $this->validation->getErrors()
        );
        $this->validation->setLabel('email', 'E-mail');
        self::assertSame(
            'The E-mail field requires a valid email address.',
            $this->validation->getError('email')
        );
        self::assertSame(
            ['email' => 'The E-mail field requires a valid email address.'],
            $this->validation->getErrors()
        );
        self::assertNull(
            $this->validation->getError('unknown')
        );
    }

    public function testOptional() : void
    {
        $this->validation->setRule('email', 'email');
        $this->validation->setRule('other', 'email');
        $this->validation->setRule('name', 'optional|minLength:5');
        $status = $this->validation->validate([
            'email' => 'user@domain.tld',
            'other' => 'other@domain.tld',
        ]);
        self::assertTrue($status);
        self::assertNull($this->validation->getError('email'));
        self::assertNull($this->validation->getError('other'));
        self::assertNull($this->validation->getError('name'));
    }

    public function testOptionalAsLastRule() : void
    {
        $this->validation->setRule('email', 'email|optional');
        $this->validation->setRule('name', 'minLength:5|optional');
        $status = $this->validation->validate([
            'name' => 'Jon',
        ]);
        self::assertFalse($status);
        self::assertNull($this->validation->getError('email'));
        self::assertStringContainsString(
            'The name field requires 5 or more characters in length.',
            $this->validation->getError('name')
        );
    }

    public function testEqualsField() : void
    {
        $this->validation->setRule('password', 'minLength:5');
        $this->validation->setRule('confirmPassword', 'equals:password');
        $this->validation->setLabels([
            'password' => 'Password',
            'confirmPassword' => 'Confirm Password',
        ]);
        $validated = $this->validation->validate([
            'password' => '123',
            'confirmPassword' => '',
        ]);
        self::assertFalse($validated);
        self::assertSame(
            'The Confirm Password field must be equals the Password field.',
            $this->validation->getError('confirmPassword')
        );
    }

    public function testEqualsFieldWithoutLabels() : void
    {
        $this->validation->setRule('password', 'minLength:5');
        $this->validation->setRule('confirmPassword', 'equals:password');
        $validated = $this->validation->validate([
            'password' => '123',
            'confirmPassword' => '',
        ]);
        self::assertFalse($validated);
        self::assertSame(
            'The confirmPassword field must be equals the password field.',
            $this->validation->getError('confirmPassword')
        );
    }

    public function testMessages() : void
    {
        self::assertSame([], $this->validation->getMessages());
        self::assertNull($this->validation->getMessage('name', 'minLength'));
        self::assertNull($this->validation->getMessage('name', 'maxLength'));
        $this->validation->setMessage('name', 'minLength', 'Field {field} is too short.');
        $this->validation->setMessage('name', 'latin', 'Field {field} must have only latin chars.');
        $this->validation->setMessage('country[city]', 'in', 'Not available in the selected city.');
        self::assertSame([
            'name' => [
                'minLength' => 'Field {field} is too short.',
                'latin' => 'Field {field} must have only latin chars.',
            ],
            'country[city]' => [
                'in' => 'Not available in the selected city.',
            ],
        ], $this->validation->getMessages());
        self::assertSame(
            'Field {field} is too short.',
            $this->validation->getMessage('name', 'minLength')
        );
        self::assertSame(
            'Field {field} must have only latin chars.',
            $this->validation->getMessage('name', 'latin')
        );
        $messages = [
            'name' => [
                'minLength' => 'Nome muito curto!',
            ],
        ];
        $this->validation->setMessages($messages);
        self::assertSame($messages, $this->validation->getMessages());
    }

    public function testMessageErrors() : void
    {
        $this->validation->setRule('name', 'minLength:5')->validate([]);
        self::assertSame(
            [
                'name' => 'The name field requires 5 or more characters in length.',
            ],
            $this->validation->getErrors()
        );
        $this->validation->setRule('name', 'minLength:5')
            ->setMessage('name', 'minLength', 'Field {field} too short.')
            ->validate([]);
        self::assertSame(
            [
                'name' => 'Field name too short.',
            ],
            $this->validation->getErrors()
        );
        $this->validation->setRule('name', 'minLength:5')
            ->setMessage('name', 'minLength', 'Field {field} too short. Min length is {args} chars.')
            ->setLabel('name', 'Nombre')
            ->validate([]);
        self::assertSame(
            [
                'name' => 'Field Nombre too short. Min length is 5 chars.',
            ],
            $this->validation->getErrors()
        );
    }

    public function testIsRuleAvailable() : void
    {
        self::assertTrue($this->validation->isRuleAvailable('alpha'));
        self::assertFalse($this->validation->isRuleAvailable('foo'));
        self::assertTrue($this->validation->isRuleAvailable('json'));
    }
}
