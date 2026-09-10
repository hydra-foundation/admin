<?php

declare(strict_types=1);

namespace Hydra\Admin;

use Hydra\Authorization\Contracts\GateInterface;

/**
 * Navigation
 *
 * The sidebar, built from the same blueprints the routes came from and filtered
 * through the gate — so a link can never appear for a screen that would 403.
 */
final class Navigation
{
    public function __construct(
        private readonly ModuleRegistry $registry,
        private readonly GateInterface $gate,
    ) {}

    /** @return list<array{slug: string, title: string, icon: ?string, url: string, active: bool}> */
    public function items(?string $current = null): array
    {
        $items = [];

        foreach ($this->registry->all() as $blueprint) {
            if ($blueprint->ability !== null && $this->gate->denies($blueprint->ability)) {
                continue;
            }

            $items[] = [
                'slug' => $blueprint->slug,
                'title' => $blueprint->title,
                'icon' => $blueprint->icon,
                'url' => rtrim($this->registry->prefix(), '/') . '/' . $blueprint->slug,
                'active' => $blueprint->slug === $current,
            ];
        }

        return $items;
    }
}
