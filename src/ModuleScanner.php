<?php

declare(strict_types=1);

namespace Hydra\Admin;

/**
 * Module scanner
 *
 * Turns blueprints into the same plain route definitions RouteScanner emits for
 * controllers, so module screens are ordinary routes — listable and matchable
 * like any other.
 */
final class ModuleScanner
{
    /**
     * @param iterable<Blueprint>    $blueprints
     * @param list<class-string>     $middleware
     */
    public function scan(iterable $blueprints, string $prefix, array $middleware = []): array
    {
        $routes = [];

        foreach ($blueprints as $blueprint) {
            foreach ($blueprint->screens as $screen) {
                $routes[] = [
                    'method' => $screen->method(),
                    'path' => $this->path($prefix, $blueprint->slug, $screen->path()),
                    'handler' => $screen->handler(),
                    'middleware' => $middleware,
                    'name' => $blueprint->slug . '.' . $screen->name(),
                ];
            }
        }

        return $routes;
    }

    private function path(string $prefix, string $slug, string $path): string
    {
        return '/' . trim(rtrim($prefix, '/') . '/' . $slug . '/' . ltrim($path, '/'), '/');
    }
}
