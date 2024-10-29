<?php

namespace ChristophRumpel\MethodOverrider;

use ReflectionMethod;

class MethodOverrider
{
    public function override(
        string $class,
        string $methodName,
        callable $implementation
    ): object|false {
        if (! class_exists($class) || ! method_exists($class, $methodName)) {
            return false;
        }

        /* @phpstan-ignore-next-line */
        $originalMethodReturnTypeName = (new ReflectionMethod($class, $methodName))->getReturnType()?->getName();
        $originalMethodReturnType = $originalMethodReturnTypeName ? ": $originalMethodReturnTypeName" : '';

        $classDefinition = <<<EOT
    new class(\$implementation) extends \\$class  {
        private \$implementation;

        public function __construct(closure \$implementation)
        {
            \$this->implementation = \$implementation;
        }

        public function $methodName()$originalMethodReturnType
        {
            \$original = function() {
                return parent::$methodName();
            };

            return (\$this->implementation)(\$original);
        }
    }
EOT;

        return eval("return $classDefinition;");
    }
}
