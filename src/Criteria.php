<?php

declare(strict_types=1);

namespace Hydra\Admin;

use Hydra\Http\Query;

/**
 * Criteria
 *
 * A list screen's request state: which page, which order, which filters. Built
 * only through fromQuery(), which whitelists sort and filter keys against the
 * blueprint — a source therefore never receives a column name it did not declare.
 */
final readonly class Criteria
{
    /** @param array<string, string> $filters */
    public function __construct(
        public int $page = 1,
        public int $perPage = 25,
        public ?string $sort = null,
        public string $direction = 'asc',
        public array $filters = [],
        public ?string $search = null,
    ) {}

    public static function fromQuery(Query $query, Blueprint $blueprint): self
    {
        $sortable = array_map(static fn (Field $field): string => $field->name(), $blueprint->sortable());
        $requested = $query->string('sort');
        $sort = in_array($requested, $sortable, true) ? $requested : $blueprint->defaultSort;

        $filters = [];

        foreach ($blueprint->filterable() as $field) {
            $value = $query->string($field->name());
            $options = $field->options();

            if ($value !== '' && ($options === null || array_key_exists($value, $options))) {
                $filters[$field->name()] = $value;
            }
        }

        $direction = strtolower($query->string('dir'));
        $search = trim($query->string('q'));

        return new self(
            page: max(1, $query->int('page', 1) ?? 1),
            perPage: $blueprint->perPage,
            sort: $sort,
            direction: in_array($direction, ['asc', 'desc'], true) ? $direction : $blueprint->defaultDirection,
            filters: $filters,
            search: $search === '' ? null : $search,
        );
    }

    public function offset(): int
    {
        return ($this->page - 1) * $this->perPage;
    }

    /** @return array<string, string> */
    public function toQuery(): array
    {
        $params = [
            'q' => $this->search,
            'sort' => $this->sort,
            'dir' => $this->sort === null ? null : $this->direction,
            'page' => $this->page > 1 ? (string) $this->page : null,
        ];

        return array_filter([...$params, ...$this->filters], static fn (?string $value): bool => $value !== null);
    }
}
