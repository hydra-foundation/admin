<?php

declare(strict_types=1);

namespace Hydra\Admin\Tests\Unit;

use Hydra\Admin\Definition;
use Hydra\Admin\Field;
use Hydra\Admin\Screens\ListScreen;
use Hydra\Admin\Surface;
use Hydra\Admin\Tests\Support\ArraySource;
use LogicException;
use PHPUnit\Framework\TestCase;

final class DefinitionTest extends TestCase
{
    public function test_it_titles_a_module_from_its_slug(): void
    {
        $this->assertSame('Audit log', Definition::make('audit-log')->screens(new ListScreen)->compile()->title);
    }

    public function test_a_source_and_fields_imply_a_list_screen(): void
    {
        $blueprint = Definition::make('users')
            ->source(new ArraySource)
            ->fields(Field::text('username'))
            ->compile();

        $this->assertCount(1, $blueprint->screens);
        $this->assertSame('list', $blueprint->screens[0]->name());
    }

    public function test_an_explicit_list_screen_is_not_duplicated(): void
    {
        $blueprint = Definition::make('users')
            ->source(new ArraySource)
            ->fields(Field::text('username'))
            ->screens(new ListScreen('App\Authorization\AccessAdmin'))
            ->compile();

        $this->assertCount(1, $blueprint->screens);
        $this->assertSame('App\Authorization\AccessAdmin', $blueprint->screens[0]->ability());
    }

    public function test_a_module_with_no_screens_is_a_programming_error(): void
    {
        $this->expectException(LogicException::class);

        Definition::make('users')->compile();
    }

    public function test_a_service_id_source_is_kept_unresolved(): void
    {
        $blueprint = Definition::make('users')
            ->source(ArraySource::class)
            ->fields(Field::text('username'))
            ->compile();

        $this->assertSame(ArraySource::class, $blueprint->source);
    }

    public function test_the_blueprint_projects_fields_onto_surfaces(): void
    {
        $blueprint = Definition::make('users')
            ->source(new ArraySource)
            ->fields(
                Field::text('username')->sortable()->searchable(),
                Field::select('role', ['admin' => 'Admin'])->filterable(),
                Field::text('password')->onlyOn(Surface::Form),
            )
            ->compile();

        $this->assertSame(['username', 'role'], array_map(
            static fn (Field $field): string => $field->name(),
            $blueprint->fieldsOn(Surface::List),
        ));
        $this->assertCount(1, $blueprint->sortable());
        $this->assertCount(1, $blueprint->searchable());
        $this->assertCount(1, $blueprint->filterable());
    }
}
