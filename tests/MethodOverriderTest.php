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

it('can generate a class file with its implementations', function (): void {
    // Arrange
    $methodOverrider = new MethodOverrider;
    $implementation = fn(callable $original): int => $original() + 1;

    // Act
    $result = $methodOverrider->generateOverriddenClass(
        class: IntegerService::class,
        methodNames: 'getOne',
        implementations: $implementation
    );

    // Assert structure
    expect($result)
        ->toBeArray()
        ->toHaveKeys(['content', 'implementations', 'className']);

    // Assert instance
    $tempFile = sys_get_temp_dir() . '/test_class_' . uniqid() . '.php';
    file_put_contents($tempFile, $result['content']);

    require $tempFile;
    $className = $result['className'];
    $instance = new $className($result['implementations']);
    unlink($tempFile);

    expect($instance)
        ->toBeObject()
        ->toBeInstanceOf(IntegerService::class)
        ->and($instance->getOne())
        ->toBe(2);
});
