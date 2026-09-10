<?php

declare(strict_types=1);

namespace Hydra\Admin\Tests\Unit;

use Hydra\Admin\Field;
use Hydra\Admin\Surface;
use PHPUnit\Framework\TestCase;

final class FieldTest extends TestCase
{
    public function test_it_humanizes_the_name_into_a_default_heading(): void
    {
        $this->assertSame('Created at', Field::datetime('created_at')->heading());
    }

    public function test_fluent_calls_do_not_mutate_the_original(): void
    {
        $field = Field::text('username');

        $this->assertTrue($field->sortable()->isSortable());
        $this->assertFalse($field->isSortable());
    }

    public function test_a_field_appears_on_every_surface_by_default(): void
    {
        $field = Field::text('username');

        $this->assertTrue($field->appearsOn(Surface::List));
        $this->assertTrue($field->appearsOn(Surface::Form));
        $this->assertTrue($field->appearsOn(Surface::Show));
    }

    public function test_only_on_and_hidden_on_narrow_the_surfaces(): void
    {
        $only = Field::text('password')->onlyOn(Surface::Form);
        $hidden = Field::text('username')->hiddenOn(Surface::Form);

        $this->assertTrue($only->appearsOn(Surface::Form));
        $this->assertFalse($only->appearsOn(Surface::List));
        $this->assertFalse($hidden->appearsOn(Surface::Form));
        $this->assertTrue($hidden->appearsOn(Surface::List));
    }

    public function test_a_select_displays_the_option_label(): void
    {
        $field = Field::select('role', ['admin' => 'Administrator']);

        $this->assertSame('Administrator', $field->display(['role' => 'admin']));
        $this->assertSame('ghost', $field->display(['role' => 'ghost']));
    }

    public function test_a_formatter_wins_over_the_raw_value(): void
    {
        $field = Field::text('username')->format(static fn (mixed $value): string => strtoupper((string) $value));

        $this->assertSame('ADA', $field->display(['username' => 'ada']));
    }

    public function test_a_missing_value_displays_as_empty(): void
    {
        $this->assertSame('', Field::text('username')->display([]));
    }
}
