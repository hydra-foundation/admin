<?php

declare(strict_types=1);

namespace Hydra\Admin\Console;

use Hydra\Admin\Blueprint;
use Hydra\Admin\ModuleRegistry;
use Hydra\Admin\ModuleScanner;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Admin routes command
 *
 * Prints what the modules compiled to: the receipt for everything the admin
 * generated on your behalf.
 */
#[AsCommand(
    name: 'admin:routes',
    description: 'Show the routes and abilities the admin modules compile to',
)]
final class AdminRoutesCommand extends Command
{
    public function __construct(private readonly ModuleRegistry $registry)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $blueprints = $this->registry->all();
        $routes = (new ModuleScanner)->scan($blueprints, $this->registry->prefix());

        $io->table(
            ['Method', 'Path', 'Name', 'Ability'],
            array_map(
                fn (array $route): array => [
                    $route['method'],
                    $route['path'],
                    $route['name'],
                    $this->ability($blueprints, $route['name']) ?? '—',
                ],
                $routes,
            ),
        );

        return Command::SUCCESS;
    }

    /**
     * The route name is "slug.screen", plus ".submit" on the POST a submittable
     * screen adds — which answers at the same screen, so it answers to the same
     * ability.
     *
     * @param array<string, Blueprint> $blueprints
     */
    private function ability(array $blueprints, string $name): ?string
    {
        [$slug, $screen] = explode('.', $name, 2);
        $blueprint = $blueprints[$slug];

        if (str_ends_with($screen, '.submit')) {
            $screen = substr($screen, 0, -strlen('.submit'));
        }

        return $blueprint->screen($screen)?->ability() ?? $blueprint->ability;
    }
}
