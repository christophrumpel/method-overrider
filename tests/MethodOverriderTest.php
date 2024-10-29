<?php

use ChristophRumpel\MethodOverrider\MethodOverrider;
use Tests\Services\RandomIntService;

it('returns false if class does not exist', function (): void {
    // Act
    $methodOverrider = new MethodOverrider;
    $result = $methodOverrider->override(
        class: 'NonExistingClass',
        methodName: 'nonExistingMethod',
        implementation: function (): void {}
    );

    // Assert
    expect($result)->toBeFalse();
});

it('returns false if method does not exists', function (): void {
    // Act
    $methodOverrider = new MethodOverrider;
    $result = $methodOverrider->override(
        class: RandomIntService::class,
        methodName: 'nonExistingMethod',
        implementation: function (): void {}
    );

    // Assert
    expect($result)->toBeFalse();
});

it('overrides a method', function (): void {
    // Act
    $methodOverrider = new MethodOverrider;
    $class = $methodOverrider->override(
        class: RandomIntService::class,
        methodName: 'getOne',
        implementation: fn (callable $original): int|float => $original() + 1
    );

    // Assert
    expect($class->getOne())->toBe(2);
});
