<?php
/**
 * QuickyPHP - A handmade php micro-framework
 *
 * @author David Dewes <hello@david-dewes.de>
 *
 * Copyright - David Dewes (c) 2022
 */

declare(strict_types=1);

namespace App\Utils;

/**
 * Class MethodSearchTree
 */
class MethodSearchTree
{
    /**
     * Root MSTNode
     *
     * @var MSTNode|null
     */
    private ?MSTNode $root;

    /**
     * Cached methods, mapped to their
     * origin classes
     *
     * @var array
     */
    private array $cache;

    /**
     * MethodSearchTree constructor.
     */
    public function __construct()
    {
        $this->root = null;
    }

    /**
     * Insert a method into tree
     *
     * @param string $methodName
     */
    public function insert(string $methodName)
    {
        if (strpos($methodName, "__") !== false) return;
        $node = new MSTNode($methodName);

        if ($this->root === null) {
            $this->root = $node;
        } else {
            $current = $this->root;
            while (true) {
                if ($methodName < $current->data) {
                    if ($current->left === null) {
                        $current->left = $node;
                        break;
                    }
                    $current = $current->left;
                } else {
                    if ($current->right === null) {
                        $current->right = $node;
                        break;
                    }
                    $current = $current->right;
                }
            }
        }
    }

    /**
     * Find method
     *
     * @param string $methodName
     * @return string|null
     */
    public function find(string $methodName): ?string
    {
        if (isset($this->cache[$methodName])) {
            return $this->cache[$methodName];
        }

        $current = $this->root;

        while ($current !== null) {
            $compareWith = explode(".", $current->data)[0];
            $className = explode(".", $current->data)[1];

            if ($methodName === $compareWith) {
                $this->cache[$methodName] = $className;
                return $className;
            } else if ($methodName < $current->data) {
                $current = $current->left;
            } else {
                $current = $current->right;
            }
        }
        return null;
    }
}