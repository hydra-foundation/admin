<?php

declare(strict_types=1);

namespace Hydra\Admin;

use Hydra\Admin\Contracts\ModuleInterface;
use Hydra\Admin\Contracts\PresenterInterface;
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

    public function fromPath(string $path): ?Blueprint
    {
        $prefix = rtrim($this->prefix, '/');

        if (!str_starts_with($path, $prefix . '/')) {
            return null;
        }

        $slug = explode('/', trim(substr($path, strlen($prefix)), '/'))[0];

        return $slug === '' ? null : $this->find($slug);
    }

    /** The screen a request path resolves to inside its module, or null. */
    public function screenAt(Blueprint $blueprint, string $path): ?ScreenInterface
    {
        $root = rtrim($this->prefix, '/') . '/' . $blueprint->slug;
        $rest = trim(substr($path, strlen($root)), '/');

        foreach ($blueprint->screens as $screen) {
            if (trim($screen->path(), '/') === $rest) {
                return $screen;
            }
        }

        return null;
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

    public function source(Blueprint $blueprint): SourceInterface
    {
        $source = is_string($blueprint->source)
            ? $this->container->get($blueprint->source)
            : $blueprint->source;

        if (!$source instanceof SourceInterface) {
            throw new RuntimeException(sprintf(
                'Admin module "%s" needs a %s to serve this screen.',
                $blueprint->slug,
                SourceInterface::class,
            ));
        }

        return $source;
    }
}
