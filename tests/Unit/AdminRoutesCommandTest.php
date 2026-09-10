<?php

declare(strict_types=1);

namespace Hydra\Admin\Tests\Unit;

use Hydra\Admin\Console\AdminRoutesCommand;
use Hydra\Admin\ModuleRegistry;
use Hydra\Admin\Tests\Support\ArrayContainer;
use Hydra\Admin\Tests\Support\ArrayWritableSource;
use Hydra\Admin\Tests\Support\CrudUserSource;
use Hydra\Admin\Tests\Support\CrudUsersModule;
use Hydra\Admin\Tests\Support\EditableUsersModule;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * The receipt for everything the admin generated on the application's behalf.
 * It is the only place a developer sees the compiled routes, so it has to name
 * all of them and say which ability each one answers to.
 */
final class AdminRoutesCommandTest extends TestCase
{
    public function test_it_lists_every_route_the_modules_compiled_to(): void
    {
        $output = $this->display(new CrudUsersModule, CrudUserSource::class, new CrudUserSource);

        foreach (['/admin/users', '/admin/users/new', '/admin/users/{id}', '/admin/users/{id}/edit', '/admin/users/{id}/delete'] as $path) {
            $this->assertStringContainsString($path, $output);
        }

        $this->assertStringContainsString('users.list', $output);
        $this->assertStringContainsString('users.delete', $output);
    }

    public function test_a_submittable_screen_is_shown_with_the_post_beside_it(): void
    {
        $output = $this->display(new CrudUsersModule, CrudUserSource::class, new CrudUserSource);

        $this->assertStringContainsString('users.edit.submit', $output);
        $this->assertStringContainsString('users.create.submit', $output);
    }

    public function test_each_route_is_shown_with_the_ability_it_answers_to(): void
    {
        // Including the POST beside a submittable screen, which answers at the
        // same screen and so answers to the same ability.
        $output = $this->display(new CrudUsersModule, CrudUserSource::class, new CrudUserSource);

        $this->assertSame(
            substr_count($output, 'users.'),
            substr_count($output, 'ManageUsers'),
            'every generated route should be listed with an ability',
        );
    }

    public function test_a_route_no_ability_guards_says_so_rather_than_leaving_a_gap(): void
    {
        $output = $this->display(new EditableUsersModule, ArrayWritableSource::class, new ArrayWritableSource);

        $this->assertStringContainsString('—', $output);
    }

    private function display(object $module, string $sourceId, object $source): string
    {
        $registry = new ModuleRegistry(
            new ArrayContainer([$module::class => $module, $sourceId => $source]),
            [$module::class],
        );

        $tester = new CommandTester(new AdminRoutesCommand($registry));
        $tester->execute([]);

        return $tester->getDisplay();
    }
}
