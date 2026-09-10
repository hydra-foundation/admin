# Hydra Admin

A composable admin backend for Hydra apps. Modules declare *what* they are (fields, a source, screens, etc) 
and the package compiles that into ordinary routes, a gate-filtered sidebar, and htmx-driven screens.

## Views

The package ships the templates it renders, in `views/`. Hand that directory to
the view as a fallback and the admin renders without any templates of your own:

```php
new PhpView(
    __DIR__ . '/views',
    $csrf,
    fallbacks: [AdminServiceProvider::views()],
);
```

To change one of them, put a file of the same name in your own views directory —
`views/admin/partials/table.php` replaces the shipped table, and everything else
still comes from the package. Nothing is registered or published for that to
work; the file simply wins.

Two things the package deliberately does not ship, because they belong to the
site rather than to the admin:

- `layouts/admin` — the shell the admin hangs off. It is where the sidebar is
  placed and where `#admin-frame` (the element htmx swaps) is declared, so it is
  also the seam at which the admin attaches to your own page chrome.
- The templates your own `PageScreen`s name, such as a dashboard.
