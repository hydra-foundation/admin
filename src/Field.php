<?php

declare(strict_types=1);

namespace Hydra\Admin;

use Closure;

/**
 * Field
 *
 * One column of a module, declared once and projected onto every surface that
 * wants it: a table cell, a form control, an export column.
 */
final class Field
{
    private string $label;

    /** @var list<Surface> */
    private array $surfaces = [Surface::List, Surface::Form, Surface::Show];

    private bool $sortable = false;
    private bool $searchable = false;
    private bool $filterable = false;
    private ?Closure $formatter = null;

    /** @param array<string, string>|null $options */
    private function __construct(
        private readonly string $name,
        private readonly FieldType $type,
        private readonly ?array $options = null,
    ) {
        $this->label = ucfirst(str_replace('_', ' ', $name));
    }

    public static function id(string $name = 'id'): self
    {
        return new self($name, FieldType::Id);
    }

    public static function text(string $name): self
    {
        return new self($name, FieldType::Text);
    }

    /** @param array<string, string> $options */
    public static function select(string $name, array $options): self
    {
        return new self($name, FieldType::Select, $options);
    }

    public static function datetime(string $name): self
    {
        return new self($name, FieldType::DateTime);
    }

    public function label(string $label): self
    {
        $clone = clone $this;
        $clone->label = $label;

        return $clone;
    }

    public function sortable(bool $sortable = true): self
    {
        $clone = clone $this;
        $clone->sortable = $sortable;

        return $clone;
    }

    public function searchable(bool $searchable = true): self
    {
        $clone = clone $this;
        $clone->searchable = $searchable;

        return $clone;
    }

    public function filterable(bool $filterable = true): self
    {
        $clone = clone $this;
        $clone->filterable = $filterable;

        return $clone;
    }

    public function onlyOn(Surface ...$surfaces): self
    {
        $clone = clone $this;
        $clone->surfaces = array_values($surfaces);

        return $clone;
    }

    public function hiddenOn(Surface ...$surfaces): self
    {
        $clone = clone $this;
        $clone->surfaces = array_values(array_filter(
            $this->surfaces,
            static fn (Surface $surface): bool => !in_array($surface, $surfaces, true),
        ));

        return $clone;
    }

    /** @param Closure(mixed, array<string, mixed>): string $formatter */
    public function format(Closure $formatter): self
    {
        $clone = clone $this;
        $clone->formatter = $formatter;

        return $clone;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function type(): FieldType
    {
        return $this->type;
    }

    public function heading(): string
    {
        return $this->label;
    }

    /** @return array<string, string>|null */
    public function options(): ?array
    {
        return $this->options;
    }

    public function isSortable(): bool
    {
        return $this->sortable;
    }

    public function isSearchable(): bool
    {
        return $this->searchable;
    }

    public function isFilterable(): bool
    {
        return $this->filterable;
    }

    public function appearsOn(Surface $surface): bool
    {
        return in_array($surface, $this->surfaces, true);
    }

    /** @param array<string, mixed> $row */
    public function display(array $row): string
    {
        $value = $row[$this->name] ?? null;

        if ($this->formatter !== null) {
            return ($this->formatter)($value, $row);
        }

        if ($this->options !== null && is_scalar($value)) {
            return $this->options[(string) $value] ?? (string) $value;
        }

        return is_scalar($value) ? (string) $value : '';
    }
}
