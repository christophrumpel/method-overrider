
<p align="center">
        <a href="https://github.com/christophrumpel/method-overrider.git/actions"><img alt="GitHub Workflow Status (master)" src="https://github.com/christophrumpel/method-overrider.git/actions/workflows/tests.yml/badge.svg"></a>
        <a href="https://packagist.org/packages/christophrumpel/method-overrider"><img alt="Total Downloads" src="https://img.shields.io/packagist/dt/christophrumpel/method-overrider"></a>
        <a href="https://packagist.org/packages/christophrumpel/method-overrider"><img alt="Latest Version" src="https://img.shields.io/packagist/v/christophrumpel/method-overrider"></a>
        <a href="https://packagist.org/packages/christophrumpel/method-overrider"><img alt="License" src="https://img.shields.io/packagist/l/christophrumpel/method-overrider"></a>
</p>

# PHP Method Overrider

> ⚠️ **Experimental Package** - This is a work in progress and not intended for production use.

Dynamically override specific methods of any PHP class at runtime while preserving method signatures and providing access to the original implementation.

## Installation

```bash
composer require christophrumpel/method-overrider
```

## Usage

```php
use ChristophRumpel\MethodOverrider\MethodOverrider;

class MathService
{
    public function add(int $a, int $b): int
    {
        return $a + $b;
    }
}

$overrider = new MethodOverrider();

// Override the add method to multiply instead
$instance = $overrider->override(
    class: MathService::class,
    methodNames: 'add',
    implementations: function (callable $original, int $a, int $b): int {
        // You can still call the original method
        $originalResult = $original();

        // Or implement completely new logic
        return $a * $b;
    }
);

echo $instance->add(2, 3); // Output: 6 (multiplied instead of added)
```

## How it works

The package uses reflection to analyze method signatures and dynamically generates new classes that extend your original class. The overridden methods wrap your custom implementations while providing access to the original method via a closure.

## Requirements

- PHP 8.3+
