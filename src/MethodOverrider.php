<?php

namespace ChristophRumpel\MethodOverrider;

use ReflectionMethod;

class MethodOverrider
{
    /**
     * @param  string|array<int, string>  $methodNames
     * @param  callable|array<int, callable>  $implementations
     */
    public function override(
        string $class,
        string|array $methodNames,
        callable|array $implementations
    ): object|false {
        $methods = is_array($methodNames) ? $methodNames : [$methodNames];
        $implementations = is_array($implementations) ? $implementations : [$implementations];

        if (! class_exists($class)) {
            return false;
        }

        if (! $this->allMethodsExist($class, $methods)) {
            return false;
        }

        if (count($methods) !== count($implementations)) {
            return false;
        }

        $methodDefinitions = $this->buildMethodDefinitions($class, $methods);

        $classDefinition = <<<EOT
    new class(\$implementations) extends \\$class {
        private array \$implementations;

        public function __construct(array \$implementations)
        {
            \$this->implementations = \$implementations;
        }

        $methodDefinitions
    }
EOT;

        return eval("return $classDefinition;");
    }

    /**
     * @param  array<int, string>  $methods
     */
    private function allMethodsExist(string $class, array $methods): bool
    {
        foreach ($methods as $method) {
            if (! method_exists($class, $method)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  array<int, string>  $methods
     */
    private function buildMethodDefinitions(string $class, array $methods): string
    {
        $definitions = [];

        foreach ($methods as $index => $methodName) {
            $returnType = $this->getMethodReturnType($class, $methodName);

            $definitions[] = <<<EOT
                public function $methodName()$returnType
                {
                    \$original = function() {
                        return parent::$methodName();
                    };

                    return (\$this->implementations[$index])(\$original);
                }
EOT;
        }

        return implode("\n\n        ", $definitions);
    }

    private function getMethodReturnType(string $class, string $methodName): string
    {
        /* @phpstan-ignore-next-line */
        $returnTypeName = (new ReflectionMethod($class, $methodName))->getReturnType()?->getName();

        return $returnTypeName ? ": $returnTypeName" : '';
    }
}
