<?php

declare(strict_types=1);

namespace Hydra\Admin\ViewModels;

/**
 * Screen view model
 *
 * The chrome around any admin screen: sidebar, breadcrumbs, title. It knows
 * nothing about tables or forms, so a plain controller action can render inside
 * the admin layout without pretending to be a module.
 */
final readonly class ScreenViewModel
{
    /**
     * @param list<array{slug: string, title: string, icon: ?string, url: string, active: bool}> $navigation
     * @param list<array{label: string, url: ?string}>                                           $breadcrumbs
     */
    public function __construct(
        public string $title,
        public array $navigation,
        public array $breadcrumbs,
    ) {}
}
