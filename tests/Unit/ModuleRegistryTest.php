<?php

declare(strict_types=1);

namespace Hydra\Admin\Tests\Unit;

use Hydra\Admin\Criteria;
use Hydra\Admin\ModuleRegistry;
use Hydra\Admin\Tests\Support\ArrayContainer;
use Hydra\Admin\Tests\Support\ArraySource;
use Hydra\Admin\Tests\Support\UsersModule;
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
