<?php

declare(strict_types=1);

namespace Hydra\Admin\ViewModels;

use Hydra\Admin\Blueprint;
use Hydra\Admin\Field;
use Hydra\Admin\Page;
use Hydra\Admin\Surface;

/**
 * List view model
 *
 * Everything a table reads: its columns, its rows, and the links that carry list
 * state through the query string so every view of the table is its own URL. The
 * surrounding chrome is {@see ScreenViewModel}'s job.
 */
final readonly class ListViewModel
{
    public function __construct(
        public Blueprint $blueprint,
        public Page $page,
        public string $prefix,
    ) {}

    public function url(): string
    {
        return rtrim($this->prefix, '/') . '/' . $this->blueprint->slug;
    }

    /** @return list<Field> */
    public function columns(): array
    {
        return $this->blueprint->fieldsOn(Surface::List);
    }

    /** @return list<Field> */
    public function filters(): array
    {
        return $this->blueprint->filterable();
    }

    public function isSearchable(): bool
    {
        return $this->blueprint->searchable() !== [];
    }

    public function filterValue(Field $field): string
    {
        return $this->page->criteria->filters[$field->name()] ?? '';
    }

    public function search(): string
    {
        return $this->page->criteria->search ?? '';
    }

    /** @param array<string, mixed> $row */
    public function cell(Field $field, array $row): string
    {
        return $field->display($row);
    }

    /** 'asc' or 'desc' when the table is ordered by this field, null otherwise. */
    public function sortedBy(Field $field): ?string
    {
        return $this->page->criteria->sort === $field->name()
            ? $this->page->criteria->direction
            : null;
    }

    public function sortLink(Field $field): string
    {
        return $this->link([
            'sort' => $field->name(),
            'dir' => $this->sortedBy($field) === 'asc' ? 'desc' : 'asc',
            'page' => null,
        ]);
    }

    public function pageLink(int $page): string
    {
        return $this->link(['page' => $page > 1 ? (string) $page : null]);
    }

    /** The page numbers worth rendering around the current one. @return list<int> */
    public function pageWindow(int $radius = 2): array
    {
        $last = $this->page->pages();
        $current = $this->page->criteria->page;

        return range(max(1, min($current - $radius, $last - $radius * 2)), min($last, max($current + $radius, $radius * 2 + 1)));
    }

    /** @param array<string, string|null> $overrides */
    private function link(array $overrides): string
    {
        $params = array_filter(
            [...$this->page->criteria->toQuery(), ...$overrides],
            static fn (?string $value): bool => $value !== null && $value !== '',
        );

        return $params === [] ? $this->url() : $this->url() . '?' . http_build_query($params);
    }
}
