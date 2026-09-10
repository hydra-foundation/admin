# Hydra Admin

A composable admin backend for Hydra apps. Modules declare *what* they are (fields, a source, screens, etc) 
and the package compiles that into ordinary routes, a gate-filtered sidebar, and htmx-driven screens.

The rule the design follows: **the admin may generate anything, as long as it can show you 
what it generated.** `php bin/console admin:routes` prints the receipt.

Hydra has no ORM, so nothing is inferred from a database. A module hands the 
admin a `SourceInterface` and owns its own SQL.
