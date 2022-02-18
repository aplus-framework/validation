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
use Framework\Validation\Rules;
use Framework\Validation\Validator;
use PHPUnit\Framework\TestCase;

final class RulesTest extends TestCase
{
    protected Rules $rules;

    protected function setUp() : void
    {
        $this->rules = new Rules();
    }

    public function testCreate() : void
    {
        $r1 = Rules::create();
        $r2 = Rules::create();
        self::assertInstanceOf(Rules::class, $r1);
        self::assertInstanceOf(Rules::class, $r2);
        self::assertNotSame($r1, $r2);
    }

    public function testToString() : void
    {
        self::assertSame('', (string) $this->rules);
        self::assertSame('alpha', (string) $this->rules->alpha());
        self::assertSame('alpha|less:3', (string) $this->rules->less(3));
        self::assertSame('alpha|less:3|less:3', (string) $this->rules->less(3));
    }

    public function testGet() : void
    {
        self::assertSame([], $this->rules->rules);
        self::assertSame(['alpha'], $this->rules->alpha()->rules);
        self::assertSame(['alpha', 'less:3'], $this->rules->less(3)->rules);
        self::assertSame(['alpha', 'less:3', 'less:3'], $this->rules->less(3)->rules);
        $this->expectException(\Error::class);
        $this->expectExceptionMessage('Cannot access property ' . $this->rules::class . '::$foo');
        $this->rules->foo; // @phpstan-ignore-line
    }

    public function testMethodsCompatibility() : void
    {
        /**
         * @var array<int,\ReflectionMethod> $methods
         */
        $methods = [];
        $classes = [
            new \ReflectionClass(Validator::class),
            new \ReflectionClass(FilesValidator::class),
        ];
        foreach ($classes as $class) {
            foreach ($class->getMethods(\ReflectionMethod::IS_STATIC) as $method) {
                if ($method->isPublic()) {
                    $methods[] = $method;
                }
            }
        }
        $rules = new \ReflectionClass(Rules::class);
        foreach ($methods as $method) {
            $name = $method->getName();
            self::assertTrue($rules->hasMethod($name));
            self::assertTrue($rules->getMethod($name)->isPublic());
            self::assertFalse($rules->getMethod($name)->isStatic());
            self::assertSame(
                $method->getNumberOfParameters(),
                $rules->getMethod($name)->getNumberOfParameters() + 2
            );
        }
    }

    public function testRuleAlpha() : void
    {
        self::assertRule('alpha', $this->rules->alpha());
    }

    public function testRuleNumber() : void
    {
        self::assertRule('number', $this->rules->number());
    }

    public function testRuleAlphaNumber() : void
    {
        self::assertRule('alphaNumber', $this->rules->alphaNumber());
    }

    public function testRuleUuid() : void
    {
        self::assertRule('uuid', $this->rules->uuid());
    }

    public function testRuleTimezone() : void
    {
        self::assertRule('timezone', $this->rules->timezone());
    }

    public function testRuleBase64() : void
    {
        self::assertRule('base64', $this->rules->base64());
    }

    public function testRuleMd5() : void
    {
        self::assertRule('md5', $this->rules->md5());
    }

    public function testRuleHex() : void
    {
        self::assertRule('hex', $this->rules->hex());
    }

    public function testRuleHexColor() : void
    {
        self::assertRule('hexColor', $this->rules->hexColor());
    }

    public function testRuleJson() : void
    {
        self::assertRule('json', $this->rules->json());
    }

    public function testRuleRegex() : void
    {
        self::assertRule('regex:[a-z]', $this->rules->regex('[a-z]'));
    }

    public function testRuleNotRegex() : void
    {
        self::assertRule('notRegex:[a-z]', $this->rules->notRegex('[a-z]'));
    }

    public function testRuleEquals() : void
    {
        self::assertRule('equals:foo', $this->rules->equals('foo'));
    }

    public function testRuleNotEquals() : void
    {
        self::assertRule('notEquals:foo', $this->rules->notEquals('foo'));
    }

    public function testRuleBetween() : void
    {
        self::assertRule('between:1,5', $this->rules->between(1, 5));
    }

    public function testRuleNotBetween() : void
    {
        self::assertRule('notBetween:1,5', $this->rules->notBetween(1, 5));
    }

    public function testRuleIn() : void
    {
        self::assertRule('in:foo,bar', $this->rules->in('foo', 'bar'));
    }

    public function testRuleNotIn() : void
    {
        self::assertRule('notIn:foo,bar', $this->rules->notIn('foo', 'bar'));
    }

    public function testRuleIp() : void
    {
        self::assertRule('ip:0', $this->rules->ip());
        $this->rules = new Rules();
        self::assertRule('ip:4', $this->rules->ip(4));
    }

    public function testRuleUrl() : void
    {
        self::assertRule('url', $this->rules->url());
    }

    public function testRuleDatetime() : void
    {
        self::assertRule('datetime:Y-m-d H:i:s', $this->rules->datetime());
        $this->rules = new Rules();
        self::assertRule('datetime:r', $this->rules->datetime('r'));
    }

    public function testRuleEmail() : void
    {
        self::assertRule('email', $this->rules->email());
    }

    public function testRuleGreater() : void
    {
        self::assertRule('greater:8', $this->rules->greater(8));
    }

    public function testRuleGreaterOrEqual() : void
    {
        self::assertRule('greaterOrEqual:8', $this->rules->greaterOrEqual(8));
    }

    public function testRuleLess() : void
    {
        self::assertRule('less:8', $this->rules->less(8));
    }

    public function testRuleLessOrEqual() : void
    {
        self::assertRule('lessOrEqual:8', $this->rules->lessOrEqual(8));
    }

    public function testRuleLatin() : void
    {
        self::assertRule('latin', $this->rules->latin());
    }

    public function testRuleMaxLength() : void
    {
        self::assertRule('maxLength:32', $this->rules->maxLength(32));
    }

    public function testRuleMinLength() : void
    {
        self::assertRule('minLength:5', $this->rules->minLength(5));
    }

    public function testRuleLength() : void
    {
        self::assertRule('length:5', $this->rules->length(5));
    }

    public function testRuleRequired() : void
    {
        self::assertRule('required', $this->rules->required());
    }

    public function testRuleIsset() : void
    {
        self::assertRule('isset', $this->rules->isset());
    }

    public function testRuleSpecialChar() : void
    {
        self::assertRule(
            'specialChar:1,!"#$%&\'()*+\,-./:;=<>?@[\]^_`{|}~',
            $this->rules->specialChar()
        );
        $this->rules = new Rules();
        self::assertRule(
            'specialChar:3,@\,#',
            $this->rules->specialChar(3, '@,#')
        );
    }

    public function testRuleUploaded() : void
    {
        self::assertRule('uploaded', $this->rules->uploaded());
    }

    public function testRuleMaxSize() : void
    {
        self::assertRule('maxSize:10', $this->rules->maxSize(10));
    }

    public function testRuleMimes() : void
    {
        self::assertRule(
            'mimes:image/png,image/jpg',
            $this->rules->mimes('image/png', 'image/jpg')
        );
    }

    public function testRuleExt() : void
    {
        self::assertRule('ext:png,jpeg', $this->rules->ext('png', 'jpeg'));
    }

    public function testRuleImage() : void
    {
        self::assertRule('image', $this->rules->image());
    }

    public function testRuleMaxDim() : void
    {
        self::assertRule('maxDim:100,120', $this->rules->maxDim(100, 120));
    }

    public function testRuleMinDim() : void
    {
        self::assertRule('minDim:100,120', $this->rules->minDim(100, 120));
    }

    public function testRuleDim() : void
    {
        self::assertRule('dim:100,120', $this->rules->dim(100, 120));
    }

    public function testRuleOptional() : void
    {
        self::assertRule('optional', $this->rules->optional());
    }

    protected static function assertRule(string $rule, Rules $rules) : void
    {
        self::assertSame($rule, (string) $rules);
    }
}
