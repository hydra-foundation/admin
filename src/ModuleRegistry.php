<?php

declare(strict_types=1);

namespace Hydra\Admin;

use Hydra\Admin\Contracts\FormSourceInterface;
use Hydra\Admin\Contracts\ModuleInterface;
use Hydra\Admin\Contracts\PresenterInterface;
use Hydra\Admin\Contracts\RowSourceInterface;
use Hydra\Admin\Contracts\ScreenInterface;
use Hydra\Admin\Contracts\SourceInterface;
use Hydra\Admin\Screens\PageScreen;
use Hydra\Core\Contracts\ContainerInterface;
use LogicException;
use RuntimeException;

/**
 * Module registry
 *
 * Resolves the registered modules to their blueprints once per request, and
 * maps a request path back to the module that owns it.
 */
final class ModuleRegistry
{
    /** @var array<string, Blueprint>|null */
    private ?array $blueprints = null;

    /** @param list<class-string<ModuleInterface>> $modules */
    public function __construct(
        private readonly ContainerInterface $container,
        private readonly array $modules,
        private readonly string $prefix = '/admin',
    ) {}

    public function prefix(): string
    {
        return $this->prefix;
    }

    /** @return array<string, Blueprint> */
    public function all(): array
    {
        if ($this->blueprints !== null) {
            return $this->blueprints;
        }

        $blueprints = [];

        foreach ($this->modules as $class) {
            $module = $this->container->get($class);

            if (!$module instanceof ModuleInterface) {
                throw new LogicException(sprintf(
                    '%s must implement %s to be registered as an admin module.',
                    $class,
                    ModuleInterface::class,
                ));
            }

            $blueprint = $module->define()->compile();

            if (isset($blueprints[$blueprint->slug])) {
                throw new LogicException("Two admin modules claim the slug \"{$blueprint->slug}\".");
            }

            $blueprints[$blueprint->slug] = $blueprint;
        }

        return $this->blueprints = $blueprints;
    }

    public function find(string $slug): ?Blueprint
    {
        return $this->all()[$slug] ?? null;
    }

    /** The URL a module's own screens hang under. */
    public function root(Blueprint $blueprint): string
    {
        return rtrim($this->prefix, '/') . '/' . $blueprint->slug;
    }

    public function fromPath(string $path): ?Blueprint
    {
        $prefix = rtrim($this->prefix, '/');

        if (!str_starts_with($path, $prefix . '/')) {
            return null;
        }

        $slug = explode('/', trim(substr($path, strlen($prefix)), '/'))[0];

        return $slug === '' ? null : $this->find($slug);
    }

    /**
     * The screen a request path resolves to inside its module, or null. A literal
     * path wins over one with a {placeholder}, so a screen at "new" is still
     * reachable when a sibling sits at "{id}".
     */
    public function screenAt(Blueprint $blueprint, string $path): ?ScreenInterface
    {
        $root = $this->root($blueprint);
        $rest = trim(substr($path, strlen($root)), '/');

        foreach ($blueprint->screens as $screen) {
            if (trim($screen->path(), '/') === $rest) {
                return $screen;
            }
        }

        foreach ($blueprint->screens as $screen) {
            if ($this->pathMatches(trim($screen->path(), '/'), $rest)) {
                return $screen;
            }
        }

        return null;
    }

    /** Segment-wise match of a screen path against a request path. */
    private function pathMatches(string $declared, string $actual): bool
    {
        if ($declared === '' || $actual === '') {
            return $declared === $actual;
        }

        $expected = explode('/', $declared);
        $given = explode('/', $actual);

        if (count($expected) !== count($given)) {
            return false;
        }

        foreach ($expected as $index => $segment) {
            $isPlaceholder = str_starts_with($segment, '{') && str_ends_with($segment, '}');

            if ($isPlaceholder ? $given[$index] === '' : $segment !== $given[$index]) {
                return false;
            }
        }

        return true;
    }

    /** @return array<string, mixed> */
    public function present(PageScreen $screen): array
    {
        $service = $screen->presenterService();

        if ($service === null) {
            return [];
        }

        $presenter = $this->container->get($service);

        if (!$presenter instanceof PresenterInterface) {
            throw new RuntimeException(sprintf(
                '%s must implement %s to present a page screen.',
                $service,
                PresenterInterface::class,
            ));
        }

        return $presenter->present();
    }

    public function formSource(Blueprint $blueprint): FormSourceInterface
    {
        $source = $this->sourceFor($blueprint);

        if (!$source instanceof FormSourceInterface) {
            throw new RuntimeException(sprintf(
                'Admin module "%s" has a form screen, so its source must implement %s.',
                $blueprint->slug,
                FormSourceInterface::class,
            ));
        }

        return $source;
    }

    public function rowSource(Blueprint $blueprint): RowSourceInterface
    {
        $source = $this->sourceFor($blueprint);

        if (!$source instanceof RowSourceInterface) {
            throw new RuntimeException(sprintf(
                'Admin module "%s" has a screen for one row, so its source must implement %s.',
                $blueprint->slug,
                RowSourceInterface::class,
            ));
        }

        return $source;
    }

    public function source(Blueprint $blueprint): SourceInterface
    {
        $source = $this->sourceFor($blueprint);

        if (!$source instanceof SourceInterface) {
            throw new RuntimeException(sprintf(
                'Admin module "%s" needs a %s to serve this screen.',
                $blueprint->slug,
                SourceInterface::class,
            ));
        }

        return $source;
    }

    /** The module's declared source, resolved from the container when it is a service id. */
    private function sourceFor(Blueprint $blueprint): mixed
    {
        return is_string($blueprint->source)
            ? $this->container->get($blueprint->source)
            : $blueprint->source;
    }
}
