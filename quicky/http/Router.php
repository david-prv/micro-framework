<?php
/**
 * QuickyPHP - A handmade php micro-framework
 *
 * @author David Dewes <hello@david-dewes.de>
 *
 * Copyright - David Dewes (c) 2022
 */

declare(strict_types=1);

/**
 * Class Router
 */
class Router implements IDispatching
{
    /**
     * All existing routes
     * (Associative array)
     *
     * @var array
     */
    private array $routes;

    /**
     * Dispatching methods
     *
     * @var array
     */
    private array $dispatching;

    /**
     * Universal middleware
     *
     * @var array
     */
    private array $middleware;

    /**
     * Router constructor.
     */
    public function __construct()
    {
        $this->routes = array();
        $this->dispatching = array("router", "route", "useMiddleware");
        $this->middleware = array();
    }

    /**
     * Invoked method
     *
     * Finds a route depending on the request
     * and executes it with a proper response object
     *
     * @param Request $request
     * @param Response $response
     * @throws UnknownRouteException
     */
    public function __invoke(Request $request, Response $response)
    {
        if (count($this->routes) === 0) throw new UnknownRouteException($request->getUrl());
        $route = $this->findRoute($request);
        if (is_null($route)) throw new UnknownRouteException($request->getUrl());

        if (Quicky::session()->isSecure()) Quicky::session()->regenerateId();

        $route->execute($request, $response);
    }

    /**
     * Return router instance
     *
     * @return object|Router|null
     * @throws NetworkException
     */
    public static function router()
    {
        $instance = DynamicLoader::getLoader()->getInstance(Router::class);

        if ($instance instanceof Router) return $instance;
        else throw new NetworkException();
    }

    /**
     * Set universal middleware
     *
     * @param mixed ...$middleware
     */
    public function useMiddleware(...$middleware): void
    {
        $this->middleware = $middleware;
    }

    /**
     * Add GET route
     *
     * @param string $method
     * @param string $pattern
     * @param callable $callback
     * @param array $middleware
     */
    public function route(string $method, string $pattern, callable $callback, ...$middleware): void
    {
        $middleware = array_merge($middleware, $this->middleware);
        $route = new Route("GET", $pattern, $callback, $middleware);

        if (!$this->isRoute($route)) {
            $this->routes[$route->hashCode()] = $route;
        }
    }

    /**
     * Checks if a route is contained in
     * the routes array
     *
     * @param Route $route
     * @return bool
     */
    private function isRoute(Route $route): bool
    {
        foreach ($this->routes as $r) {
            if ($route->hashCode() === $r->hashCode()) return true;
        }
        return false;
    }

    /**
     * Finds route by hash-code
     *
     * @param string $hash
     * @return Route|null
     */
    private function findRouteByHash(string $hash): ?Route
    {
        return (isset($this->routes[$hash])) ? $this->routes[$hash] : null;
    }

    /**
     * Finds route that fits to requested url
     *
     * @param Request $request
     * @return Route|null
     */
    private function findRoute(Request $request): ?Route
    {
        $url = $request->getUrl();
        $method = $request->getMethod();

        // Trivial route
        if ($url === "/") {
            return $this->findRouteByHash(sha1($url . $method));
        }

        foreach ($this->routes as $route) {
            if ($route instanceof Route) {
                if ($route->match($url, $request)) return $route;
            }
        }
        return null;
    }

    /**
     * Checks if class is dispatching
     *
     * @param string $method
     * @return bool
     */
    public function dispatches(string $method): bool
    {
        return in_array($method, $this->dispatching);
    }
}