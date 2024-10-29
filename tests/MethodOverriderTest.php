<?php

use ChristophRumpel\MethodOverrider\MethodOverrider;
use Tests\Services\RandomIntService;

it('returns false if class does not exist', function (): void {
    // Act
    $methodOverrider = new MethodOverrider;
    $result = $methodOverrider->override(
        class: 'NonExistingClass',
        methodNames: 'nonExistingMethod',
        implementations: function (): void {}
    );

    // Assert
    expect($result)->toBeFalse();
});

it('returns false if method does not exists', function (): void {
    // Act
    $methodOverrider = new MethodOverrider;
    $result = $methodOverrider->override(
        class: RandomIntService::class,
        methodNames: 'nonExistingMethod',
        implementations: function (): void {}
    );

    // Assert
    expect($result)->toBeFalse();
});

it('overrides a method of a class', function (): void {
    // Act
    $methodOverrider = new MethodOverrider;
    $class = $methodOverrider->override(
        class: RandomIntService::class,
        methodNames: 'getOne',
        implementations: fn (callable $original): int|float => $original() + 1
    );

    // Assert
    expect($class->getOne())->toBe(2);
});

it('overrides two methods of a class', function (): void {
    // Act
    $methodOverrider = new MethodOverrider;
    $class = $methodOverrider->override(
        class: RandomIntService::class,
        methodNames: ['getOne', 'getTwo'],
        implementations: [
            fn (callable $original): int|float => $original() + 1,
            fn (callable $original): int|float => $original() + 1,
        ]
    );

    // Assert
    expect($class->getOne())->toBe(2);
    expect($class->getTwo())->toBe(3);
});
