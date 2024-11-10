<?php

use ChristophRumpel\MethodOverrider\MethodOverrider;
use Tests\Services\IntegerService;

it('returns false if class does not exist', function (): void {
    // Act
    $methodOverrider = new MethodOverrider;
    $result = $methodOverrider->override(
        class: 'NonExistingClass',
        methodNames: 'nonExistingMethod',
        implementations: function (): void {
        }
    );

    // Assert
    expect($result)->toBeFalse();
});

it('returns false if method does not exists', function (): void {
    // Act
    $methodOverrider = new MethodOverrider;
    $result = $methodOverrider->override(
        class: IntegerService::class,
        methodNames: 'nonExistingMethod',
        implementations: function (): void {
        }
    );

    // Assert
    expect($result)->toBeFalse();
});

it('overrides a method of a class', function (): void {
    // Act
    $methodOverrider = new MethodOverrider;
    $class = $methodOverrider->override(
        class: IntegerService::class,
        methodNames: 'getOne',
        implementations: fn(callable $original): int => $original() + 1
    );

    // Assert
    expect($class->getOne())->toBe(2);
});

it('overrides a method with arguments of a class', function (): void {
    // Act
    $methodOverrider = new MethodOverrider;
    $class = $methodOverrider->override(
        class: IntegerService::class,
        methodNames: 'get',
        implementations: fn(callable $original): int|float => $original() + 5
    );

    // Assert
    expect($class->get(1))->toBe(6);
});

it('overrides two methods of a class', function (): void {
    // Act
    $methodOverrider = new MethodOverrider;
    $class = $methodOverrider->override(
        class: IntegerService::class,
        methodNames: ['getOne', 'getTwo'],
        implementations: [
            fn(callable $original): int|float => $original() + 1,
            fn(callable $original): int|float => $original() + 1,
        ]
    );

    // Assert
    expect($class->getOne())->toBe(2);
    expect($class->getTwo())->toBe(3);
});

it('can give you just the string content of the new class', function (): void {
    // Arrange
    $methodOverrider = new MethodOverrider;

    // Act
    $classDefinition = $methodOverrider->override(
        class: IntegerService::class,
        methodNames: 'getOne',
        implementations: fn(callable $original): int|float => $original() + 1,
        returnString: true
    );

    // Assert
    expect($classDefinition)->toBeString();
    expect($classDefinition)->toContain('new class');
    expect($classDefinition)->toContain('extends \Tests\Services\IntegerService');
    expect($classDefinition)->toContain('private array $implementations');
    expect($classDefinition)->toContain('public function __construct(array $implementations)');
    expect($classDefinition)->toContain('public function getOne()');
});
