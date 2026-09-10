# Hydra Admin

A composable admin backend for Hydra apps. Modules declare *what* they are (fields, a source, screens, etc) 
and the package compiles that into ordinary routes, a gate-filtered sidebar, and htmx-driven screens.

The rule the design follows: **the admin may generate anything, as long as it can show you 
what it generated.** `php bin/console admin:routes` prints the receipt.

Hydra has no ORM, so nothing is inferred from a database. A module hands the 
admin a `SourceInterface` and owns its own SQL.

Fields declare display rules per surface, so one declaration can read differently
in a table cell and a detail row:

- `format()` replaces the value. A source matches what the column *stores*, so a
  `searchable()` field may not also `format()` — `compile()` rejects it.
- `decorate()` is a formatter that leaves the stored value findable in its output.
- `emptyAs()` stands in for a null value without being a formatter.

A formatter returns a string, which the template escapes, or a `HtmlView` to emit
markup.
