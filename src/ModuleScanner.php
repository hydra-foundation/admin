<?php

declare(strict_types=1);

namespace Hydra\Admin;

use Hydra\Admin\Contracts\SubmittableInterface;

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
                $path = $this->path($prefix, $blueprint->slug, $screen->path());
                $name = $blueprint->slug . '.' . $screen->name();

                $routes[] = [
                    'method' => $screen->method(),
                    'path' => $path,
                    'handler' => $screen->handler(),
                    'middleware' => $middleware,
                    'name' => $name,
                ];

                if ($screen instanceof SubmittableInterface) {
                    $routes[] = [
                        'method' => 'POST',
                        'path' => $path,
                        'handler' => $screen->submitHandler(),
                        'middleware' => $middleware,
                        'name' => $name . '.submit',
                    ];
                }
            }
        }

        return $routes;
    }

    private function path(string $prefix, string $slug, string $path): string
    {
        return '/' . trim(rtrim($prefix, '/') . '/' . $slug . '/' . ltrim($path, '/'), '/');
    }
}
