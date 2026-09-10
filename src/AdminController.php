<?php

declare(strict_types=1);

namespace Hydra\Admin;

use Hydra\Admin\Contracts\ScreenInterface;
use Hydra\Admin\Screens\PageScreen;
use Hydra\Admin\ViewModels\ListViewModel;
use Hydra\Authorization\Contracts\GateInterface;
use Hydra\Http\Exceptions\NotFoundException;
use Hydra\Http\Query;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Admin controller
 *
 * The shared handler behind the generated screen routes. It resolves the module
 * and screen from the path, enforces the screen's ability, and renders.
 */
final class AdminController
{
    public function __construct(
        private readonly ModuleRegistry $registry,
        private readonly Chrome $chrome,
        private readonly Renderer $renderer,
        private readonly GateInterface $gate,
    ) {}

    public function list(Request $request): Response
    {
        [$blueprint] = $this->resolve($request);
        $criteria = Criteria::fromQuery(Query::fromRequest($request), $blueprint);

        return $this->renderer->screen(
            $request,
            $this->chrome->module($blueprint),
            'admin/partials/table',
            [
                'vm' => new ListViewModel(
                    $blueprint,
                    $this->registry->source($blueprint)->page($criteria),
                    $this->registry->prefix(),
                ),
            ],
            toolbar: 'admin/partials/filters',
        );
    }

    public function page(Request $request): Response
    {
        [$blueprint, $screen] = $this->resolve($request);

        if (!$screen instanceof PageScreen) {
            throw new NotFoundException;
        }

        return $this->renderer->screen(
            $request,
            $this->chrome->module($blueprint, $screen->heading()),
            $screen->template(),
            $this->registry->present($screen),
        );
    }

    /** @return array{0: Blueprint, 1: ScreenInterface} */
    private function resolve(Request $request): array
    {
        $path = $request->getUri()->getPath();
        $blueprint = $this->registry->fromPath($path);
        $screen = $blueprint === null ? null : $this->registry->screenAt($blueprint, $path);

        if ($blueprint === null || $screen === null) {
            throw new NotFoundException;
        }

        $ability = $screen->ability() ?? $blueprint->ability;

        if ($ability !== null) {
            $this->gate->authorize($ability);
        }

        return [$blueprint, $screen];
    }
}
