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
 *
 * @dispatch router
 * @dispatch get
 * @dispatch post
 * @dispatch put
 * @dispatch update
 * @dispatch delete
 * @dispatch patch
 */
class Router
{
    /**
     * All existing routes
     * (Associative array)
     *
     * @var array
     */
    private array $routes;

    /**
     * Router constructor.
     */
    public function __construct()
    {
        $this->routes = array();
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
     * Getter for router instance
     *
     * @return object|Router|null
     * @throws NetworkException
     */
    public function router()
    {
        $instance = DynamicLoader::getLoader()->getInstance(Router::class);

        if ($instance instanceof Router) return $instance;
        else throw new NetworkException();
    }

    /**
     * Dispatched GET route
     *
     * @param string $pattern
     * @param callable $callback
     */
    public function get(string $pattern, callable $callback): void
    {
        $route = new Route("GET", $pattern, $callback);

        if (!$this->isRoute($route)) {
            $this->routes[$route->hashCode()] = $route;
        }
    }

    /**
     * Dispatched POST route
     *
     * @param string $pattern
     * @param callable $callback
     */
    public function post(string $pattern, callable $callback): void
    {
        $route = new Route("POST", $pattern, $callback);

        if (!$this->isRoute($route)) {
            $this->routes[$route->hashCode()] = $route;
        }
    }

    /**
     * Dispatched PUT route
     *
     * @param string $pattern
     * @param callable $callback
     */
    public function put(string $pattern, callable $callback): void
    {
        $route = new Route("PUT", $pattern, $callback);

        if (!$this->isRoute($route)) {
            $this->routes[$route->hashCode()] = $route;
        }
    }

    /**
     * Dispatched UPDATE route
     *
     * @param string $pattern
     * @param callable $callback
     */
    public function update(string $pattern, callable $callback): void
    {
        $route = new Route("UPDATE", $pattern, $callback);

        if (!$this->isRoute($route)) {
            $this->routes[$route->hashCode()] = $route;
        }
    }

    /**
     * Dispatched DELETE route
     *
     * @param string $pattern
     * @param callable $callback
     */
    public function delete(string $pattern, callable $callback): void
    {
        $route = new Route("DELETE", $pattern, $callback);

        if (!$this->isRoute($route)) {
            $this->routes[$route->hashCode()] = $route;
        }
    }

    /**
     * Dispatched PATCH route
     *
     * @param string $pattern
     * @param callable $callback
     */
    public function patch(string $pattern, callable $callback): void
    {
        $route = new Route("PATCH", $pattern, $callback);

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
}