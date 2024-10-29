<?php

namespace ChristophRumpel\MethodOverrider;

use ReflectionMethod;
use ReflectionParameter;

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
            $parameters = $this->getMethodParameters($class, $methodName);
            $parameterList = $this->buildParameterList($parameters);
            $parameterNames = $this->buildParameterNames($parameters);

            $useClause = $parameterNames !== '' && $parameterNames !== '0' ? " use ($parameterNames)" : '';
            $implementationParams = $parameterNames !== '' && $parameterNames !== '0' ? ", $parameterNames" : '';

            $definitions[] = <<<EOT
                public function $methodName($parameterList)$returnType
                {
                    \$original = function()$useClause {
                        return parent::$methodName($parameterNames);
                    };

                    return (\$this->implementations[$index])(\$original$implementationParams);
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

    /**
     * @return ReflectionParameter[]
     */
    private function getMethodParameters(string $class, string $methodName): array
    {
        return (new ReflectionMethod($class, $methodName))->getParameters();
    }

    /**
     * @param  ReflectionParameter[]  $parameters
     */
    private function buildParameterList(array $parameters): string
    {
        if ($parameters === []) {
            return '';
        }

        return implode(', ', array_map(function (ReflectionParameter $param): string {
            /* @phpstan-ignore-next-line */
            $type = $param->getType()?->getName();
            $name = $param->getName();
            $isOptional = $param->isOptional();
            $hasDefault = $param->isDefaultValueAvailable();

            $paramStr = $type ? "$type $$name" : "$$name";

            if ($hasDefault) {
                $default = $param->getDefaultValue();
                $defaultStr = is_string($default) ? "'$default'" : $default;
                $paramStr .= ' = '.var_export($defaultStr, true);
            } elseif ($isOptional) {
                $paramStr .= ' = null';
            }

            return $paramStr;
        }, $parameters));
    }

    /**
     * @param  ReflectionParameter[]  $parameters
     */
    private function buildParameterNames(array $parameters): string
    {
        if ($parameters === []) {
            return '';
        }

        return implode(', ', array_map(fn(ReflectionParameter $param): string => '$'.$param->getName(), $parameters));
    }
}
