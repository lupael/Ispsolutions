<?php

declare(strict_types=1);

namespace Tests\Unit\Attributes;

use App\Attributes\Deprecated;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

class DeprecatedTest extends TestCase
{
    public function test_can_create_deprecated_attribute_with_all_parameters(): void
    {
        $deprecated = new Deprecated(
            guid: 'DEP-2024-001',
            message: 'This method is deprecated',
            since: 'v1.0.0',
            alternative: 'NewClass::newMethod()',
            removeIn: 'v2.0.0'
        );

        $this->assertSame('DEP-2024-001', $deprecated->guid);
        $this->assertSame('This method is deprecated', $deprecated->message);
        $this->assertSame('v1.0.0', $deprecated->since);
        $this->assertSame('NewClass::newMethod()', $deprecated->alternative);
        $this->assertSame('v2.0.0', $deprecated->removeIn);
    }

    public function test_can_create_deprecated_attribute_with_minimal_parameters(): void
    {
        $deprecated = new Deprecated(
            guid: 'DEP-2024-002',
            message: 'This is deprecated'
        );

        $this->assertSame('DEP-2024-002', $deprecated->guid);
        $this->assertSame('This is deprecated', $deprecated->message);
        $this->assertNull($deprecated->since);
        $this->assertNull($deprecated->alternative);
        $this->assertNull($deprecated->removeIn);
    }

    public function test_formatted_message_includes_all_information(): void
    {
        $deprecated = new Deprecated(
            guid: 'DEP-2024-003',
            message: 'Method is no longer supported',
            since: 'v1.5.0',
            alternative: 'BetterClass::betterMethod()',
            removeIn: 'v3.0.0'
        );

        $expected = 'DEPRECATED [DEP-2024-003] since v1.5.0 - Method is no longer supported Use BetterClass::betterMethod() instead. Will be removed in v3.0.0.';
        $this->assertSame($expected, $deprecated->getFormattedMessage());
    }

    public function test_formatted_message_with_minimal_info(): void
    {
        $deprecated = new Deprecated(
            guid: 'DEP-2024-004',
            message: 'Deprecated functionality'
        );

        $expected = 'DEPRECATED [DEP-2024-004] - Deprecated functionality';
        $this->assertSame($expected, $deprecated->getFormattedMessage());
    }

    public function test_formatted_message_with_since_only(): void
    {
        $deprecated = new Deprecated(
            guid: 'DEP-2024-005',
            message: 'Old method',
            since: 'v2.0.0'
        );

        $expected = 'DEPRECATED [DEP-2024-005] since v2.0.0 - Old method';
        $this->assertSame($expected, $deprecated->getFormattedMessage());
    }

    public function test_formatted_message_with_alternative_only(): void
    {
        $deprecated = new Deprecated(
            guid: 'DEP-2024-006',
            message: 'Use new API',
            alternative: 'NewApi::call()'
        );

        $expected = 'DEPRECATED [DEP-2024-006] - Use new API Use NewApi::call() instead.';
        $this->assertSame($expected, $deprecated->getFormattedMessage());
    }

    public function test_to_array_returns_all_properties(): void
    {
        $deprecated = new Deprecated(
            guid: 'DEP-2024-007',
            message: 'Test message',
            since: 'v1.0.0',
            alternative: 'TestClass::test()',
            removeIn: 'v2.0.0'
        );

        $array = $deprecated->toArray();

        $this->assertArrayHasKey('guid', $array);
        $this->assertArrayHasKey('message', $array);
        $this->assertArrayHasKey('since', $array);
        $this->assertArrayHasKey('alternative', $array);
        $this->assertArrayHasKey('removeIn', $array);
        $this->assertArrayHasKey('formattedMessage', $array);

        $this->assertSame('DEP-2024-007', $array['guid']);
        $this->assertSame('Test message', $array['message']);
        $this->assertSame('v1.0.0', $array['since']);
        $this->assertSame('TestClass::test()', $array['alternative']);
        $this->assertSame('v2.0.0', $array['removeIn']);
        $this->assertIsString($array['formattedMessage']);
    }

    public function test_attribute_can_be_applied_to_method(): void
    {
        $reflection = new ReflectionMethod(DeprecatedTestSubject::class, 'deprecatedMethod');
        $attributes = $reflection->getAttributes(Deprecated::class);

        $this->assertCount(1, $attributes);

        /** @var Deprecated $deprecated */
        $deprecated = $attributes[0]->newInstance();

        $this->assertSame('DEP-TEST-001', $deprecated->guid);
        $this->assertSame('This method is deprecated', $deprecated->message);
    }

    public function test_attribute_can_be_applied_to_class(): void
    {
        $reflection = new ReflectionClass(DeprecatedTestClass::class);
        $attributes = $reflection->getAttributes(Deprecated::class);

        $this->assertCount(1, $attributes);

        /** @var Deprecated $deprecated */
        $deprecated = $attributes[0]->newInstance();

        $this->assertSame('DEP-TEST-002', $deprecated->guid);
        $this->assertSame('This class is deprecated', $deprecated->message);
    }

    public function test_attribute_can_be_applied_to_property(): void
    {
        $reflection = new ReflectionClass(DeprecatedTestSubject::class);
        $property = $reflection->getProperty('deprecatedProperty');
        $attributes = $property->getAttributes(Deprecated::class);

        $this->assertCount(1, $attributes);

        /** @var Deprecated $deprecated */
        $deprecated = $attributes[0]->newInstance();

        $this->assertSame('DEP-TEST-003', $deprecated->guid);
    }

    public function test_attribute_can_be_applied_to_constant(): void
    {
        $reflection = new ReflectionClass(DeprecatedTestSubject::class);
        $constant = $reflection->getReflectionConstant('DEPRECATED_CONSTANT');
        $attributes = $constant->getAttributes(Deprecated::class);

        $this->assertCount(1, $attributes);

        /** @var Deprecated $deprecated */
        $deprecated = $attributes[0]->newInstance();

        $this->assertSame('DEP-TEST-004', $deprecated->guid);
    }
}

/**
 * Test subject class with deprecated elements
 */
class DeprecatedTestSubject
{
    #[Deprecated(
        guid: 'DEP-TEST-003',
        message: 'Property is deprecated'
    )]
    public string $deprecatedProperty = 'old';

    #[Deprecated(
        guid: 'DEP-TEST-004',
        message: 'Constant is deprecated'
    )]
    public const DEPRECATED_CONSTANT = 'old_value';

    #[Deprecated(
        guid: 'DEP-TEST-001',
        message: 'This method is deprecated',
        since: 'v1.0.0',
        alternative: 'newMethod()',
        removeIn: 'v2.0.0'
    )]
    public function deprecatedMethod(): void
    {
        // deprecated implementation
    }

    public function newMethod(): void
    {
        // new implementation
    }
}

/**
 * Test class marked as deprecated
 */
#[Deprecated(
    guid: 'DEP-TEST-002',
    message: 'This class is deprecated',
    since: 'v1.0.0',
    alternative: 'NewTestClass'
)]
class DeprecatedTestClass
{
    public function someMethod(): void
    {
        // implementation
    }
}
