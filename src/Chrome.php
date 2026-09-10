<?php

declare(strict_types=1);

namespace Hydra\Admin;

use Hydra\Admin\ViewModels\ScreenViewModel;

/**
 * Chrome
 *
 * Builds the frame around a screen. Modules get it automatically; an ordinary
 * controller asks for one and renders into the same layout.
 */
final class Chrome
{
    public function __construct(
        private readonly ModuleRegistry $registry,
        private readonly Navigation $navigation,
    ) {}

    /** A screen sitting at the admin root, with nothing above it. */
    public function root(string $title): ScreenViewModel
    {
        return new ScreenViewModel(
            $title,
            $this->navigation->items(),
            [['label' => $title, 'url' => null]],
        );
    }

    /** A module's own screen, hung under the admin root. */
    public function module(Blueprint $blueprint, ?string $title = null, ?string $notice = null): ScreenViewModel
    {
        return new ScreenViewModel(
            $title ?? $blueprint->title,
            $this->navigation->items($blueprint->slug),
            [
                $this->home(),
                ['label' => $title ?? $blueprint->title, 'url' => null],
            ],
            $notice,
        );
    }

    /**
     * A screen below a module, with the module's own root above it. The crumb
     * names the row where the title names the task: "Edit 42" under "Edit user".
     */
    public function screen(Blueprint $blueprint, string $title, ?string $crumb = null, ?string $notice = null): ScreenViewModel
    {
        return new ScreenViewModel(
            $title,
            $this->navigation->items($blueprint->slug),
            [
                $this->home(),
                ['label' => $blueprint->title, 'url' => $this->registry->root($blueprint)],
                ['label' => $crumb ?? $title, 'url' => null],
            ],
            $notice,
        );
    }

    /**
     * The root crumb points at the landing module rather than the prefix, which
     * only redirects there — and htmx answers a redirect by reloading the page.
     *
     * @return array{label: string, url: string}
     */
    private function home(): array
    {
        return ['label' => 'Admin', 'url' => $this->navigation->home()];
    }
}
