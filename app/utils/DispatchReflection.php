<?php
/**
 * A handmade php micro-framework
 *
 * @author David Dewes <hello@david-dewes.de>
 *
 * Copyright - David Dewes (c) 2022
 */

declare(strict_types=1);

/**
 * Class DispatchReflection
 */
class DispatchReflection
{
    /**
     * Check if a class is dispatching
     *
     * @param string $className
     * @return bool
     * @throws ReflectionException
     */
    public static function isDispatchingClass(string $className)
    {
        $reflection = new ReflectionClass($className);
        return strpos($reflection->getDocComment(), "@dispatch") !== false;
    }

    /**
     * Checks if a method is dispatched by
     * a certain class
     *
     * @param string $className
     * @param string $methodName
     * @return bool
     * @throws ReflectionException
     */
    public static function isDispatchedByClass(string $className, string $methodName)
    {
        $reflection = new ReflectionClass($className);
        return strpos($reflection->getDocComment(), "@dispatch $methodName") !== false;
    }
}