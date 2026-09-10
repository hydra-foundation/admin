<?php

declare(strict_types=1);

namespace Hydra\Admin\Tests\Unit;

use Hydra\Admin\Contracts\CreateSourceInterface;
use Hydra\Admin\Contracts\UpdateSourceInterface;
use Hydra\Admin\Criteria;
use Hydra\Admin\ModuleRegistry;
use Hydra\Admin\Tests\Support\ArrayContainer;
use Hydra\Admin\Tests\Support\ArrayWritableSource;
use Hydra\Admin\Tests\Support\ArrayRowSource;
use Hydra\Admin\Tests\Support\ArraySource;
use Hydra\Admin\Tests\Support\EditableUsersModule;
use RuntimeException;
use Hydra\Admin\Tests\Support\UsersModule;
use Hydra\Admin\Tests\Support\ViewableUsersModule;
use PHPUnit\Framework\TestCase;

final class ModuleRegistryTest extends TestCase
{
    public function test_it_maps_a_request_path_back_to_its_module(): void
    {
        $registry = $this->registry();

        $this->assertSame('users', $registry->fromPath('/admin/users')?->slug);
        $this->assertSame('users', $registry->fromPath('/admin/users/42/edit')?->slug);
    }

    public function test_it_does_not_claim_paths_outside_the_prefix(): void
    {
        $registry = $this->registry();

        $this->assertNull($registry->fromPath('/admin'));
        $this->assertNull($registry->fromPath('/users'));
        $this->assertNull($registry->fromPath('/admin/unknown'));
    }

    public function test_a_service_id_source_is_resolved_at_request_time(): void
    {
        $registry = $this->registry();
        $blueprint = $registry->find('users');

        $this->assertNotNull($blueprint);
        $this->assertSame(2, $registry->source($blueprint)->page(new Criteria)->total);
    }

    public function test_a_placeholder_screen_resolves_a_concrete_path(): void
    {
        $registry = $this->editableRegistry();
        $blueprint = $registry->find('users');

        $this->assertNotNull($blueprint);
        $this->assertSame('edit', $registry->screenAt($blueprint, '/admin/users/42/edit')?->name());
        $this->assertSame('list', $registry->screenAt($blueprint, '/admin/users')?->name());
    }

    public function test_a_literal_path_wins_over_a_placeholder(): void
    {
        $registry = $this->editableRegistry();
        $blueprint = $registry->find('users');

        $this->assertNotNull($blueprint);
        $this->assertSame('new', $registry->screenAt($blueprint, '/admin/users/new')?->name());
    }

    public function test_a_path_that_matches_no_screen_stays_unresolved(): void
    {
        $registry = $this->editableRegistry();
        $blueprint = $registry->find('users');

        $this->assertNotNull($blueprint);
        $this->assertNull($registry->screenAt($blueprint, '/admin/users/42/edit/extra'));
        $this->assertNull($registry->screenAt($blueprint, '/admin/users/42/delete'));
    }

    public function test_a_read_only_source_cannot_serve_an_edit_screen(): void
    {
        $registry = $this->registry();
        $blueprint = $registry->find('users');

        $this->assertNotNull($blueprint);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('must implement');

        $registry->updateSource($blueprint);
    }

    public function test_a_source_that_reads_one_row_serves_a_show_screen_without_being_writable(): void
    {
        $registry = $this->viewableRegistry();
        $blueprint = $registry->find('users');

        $this->assertNotNull($blueprint);
        $this->assertSame('ada', $registry->rowSource($blueprint)->find('1')['username'] ?? null);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('must implement');

        $registry->updateSource($blueprint);
    }

    public function test_a_source_that_updates_is_not_thereby_allowed_to_create(): void
    {
        $registry = $this->editableRegistry();
        $blueprint = $registry->find('users');

        $this->assertNotNull($blueprint);
        $this->assertInstanceOf(UpdateSourceInterface::class, $registry->updateSource($blueprint));
        $this->assertInstanceOf(CreateSourceInterface::class, $registry->createSource($blueprint));

        $readOnly = $this->registry()->find('users');
        $this->assertNotNull($readOnly);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('create screen');

        $this->registry()->createSource($readOnly);
    }

    public function test_a_source_that_reads_only_pages_cannot_serve_a_screen_for_one_row(): void
    {
        $registry = $this->registry();
        $blueprint = $registry->find('users');

        $this->assertNotNull($blueprint);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('screen for one row');

        $registry->rowSource($blueprint);
    }

    private function viewableRegistry(): ModuleRegistry
    {
        return new ModuleRegistry(
            new ArrayContainer([
                ViewableUsersModule::class => new ViewableUsersModule,
                ArrayRowSource::class => new ArrayRowSource,
            ]),
            [ViewableUsersModule::class],
        );
    }

    private function editableRegistry(): ModuleRegistry
    {
        return new ModuleRegistry(
            new ArrayContainer([
                EditableUsersModule::class => new EditableUsersModule,
                ArrayWritableSource::class => new ArrayWritableSource,
            ]),
            [EditableUsersModule::class],
        );
    }

    private function registry(): ModuleRegistry
    {
        return new ModuleRegistry(
            new ArrayContainer([
                UsersModule::class => new UsersModule,
                ArraySource::class => new ArraySource([['id' => 1], ['id' => 2]]),
            ]),
            [UsersModule::class],
        );
    }
}
