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
    public function module(Blueprint $blueprint, ?string $title = null): ScreenViewModel
    {
        return new ScreenViewModel(
            $title ?? $blueprint->title,
            $this->navigation->items($blueprint->slug),
            [
                ['label' => 'Admin', 'url' => rtrim($this->registry->prefix(), '/')],
                ['label' => $title ?? $blueprint->title, 'url' => null],
            ],
        );
    }
}
