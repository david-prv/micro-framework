<?php
/**
 * QuickyPHP - A handmade php micro-framework
 *
 * @author David Dewes <hello@david-dewes.de>
 *
 * Copyright - David Dewes (c) 2022
 */

declare(strict_types=1);

namespace App\Http;

/**
 * Class Route
 */
class Route
{
    /**
     * The method for the route
     *
     * @var string
     */
    private string $method;

    /**
     * A matching pattern for the route
     *
     * @var string
     */
    private string $pattern;

    /**
     * Provided middleware
     *
     * @var array
     */
    private array $middleware;

    /**
     * The callback method
     *
     * @var callable
     */
    private $callback;

    /**
     * Route constructor.
     *
     * @param string $method
     * @param string $pattern
     * @param callable $callback
     * @param array $middleware
     */
    public function __construct(
        string $method,
        string $pattern,
        callable $callback,
        array $middleware
    ) {
        $this->method = $method;
        $this->pattern = $pattern;
        $this->callback = $callback;
        $this->middleware = (is_null($middleware)) ? [] : $middleware;
    }

    /**
     * Returns the route hashcode
     *
     * @return string
     * @uses sha1()
     */
    public function hashCode(): string
    {
        return sha1($this->pattern . $this->method);
    }

    /**
     * Checks if middleware is used
     *
     * @return bool
     */
    private function usesMiddleware(): bool
    {
        return (count($this->middleware) >= 1);
    }

    /**
     * Executes the route closure
     *
     * @param Request $request
     * @param Response $response
     * @return mixed
     */
    public function execute(Request $request, Response $response)
    {
        $middleware = $this->middleware;
        $callback = $this->callback;

        // Define the initial "next" function
        $next = function (Request $request, Response $response) use ($callback) {
            return call_user_func($callback, $request, $response);
        };

        // Loop through the middleware in reverse order
        for ($i = count($middleware) - 1; $i >= 0; $i--) {
            // Define the next middleware function
            $next = function (Request $request, Response $response) use ($middleware, $i, $next) {
                $currentMiddleware = $middleware[$i];
                return $currentMiddleware->run($request, $response, $next);
            };
        }

        // Execute the middleware chain
        return $next($request, $response);
    }

    /**
     * Returns the route in string format
     *
     * @return string
     */
    public function toString(): string
    {
        return $this->method . " - " . $this->pattern . " (uses middleware: " .
            (($this->usesMiddleware()) ? "true" : "false") . ")";
    }

    /**
     * Checks if the requested url
     * matches this route and additionally parses
     * all arguments and updates the request, iff vars are present
     *
     * @param string $url
     * @param Request $request
     * @return bool
     */
    public function match(string $url, Request $request): bool
    {
        $pattern = array_filter(explode("/", $this->pattern), function ($e) {
            return $e !== "" && $e !== null;
        });
        $urlParts = array_filter(explode("/", $url), function ($e) {
            return $e !== "" && $e !== null;
        });

        if (count($pattern) !== count($urlParts)) {
            return false;
        }

        $values = array();
        for ($i = 1; $i < count($pattern) + 1; $i++) {
            if (preg_match("/^{.*}$/", $pattern[$i])) {
                // Check if the pattern is a variable
                $varName = str_replace(["{", "}"], "", $pattern[$i]);
                $values[$varName] = $urlParts[$i];
            } elseif (preg_match("/^\(.*\)$/", $pattern[$i])) {
                // Check if the pattern is a regex
                $pattern[$i] = str_replace(["(", ")"], "", $pattern[$i]);
                if (!preg_match("/$pattern[$i]/", $urlParts[$i])) {
                    return false;
                }
            } elseif ($pattern[$i] === "*") {
                // Check if the pattern is a wildcard
                continue;
            } elseif ($pattern[$i] !== $urlParts[$i]) {
                // If none of the above, check for an exact match
                return false;
            }
        }
        $request->setArgs($values);
        return true;
    }
}
