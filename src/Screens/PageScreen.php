<?php

declare(strict_types=1);

namespace Hydra\Admin\Screens;

use Hydra\Admin\AdminController;
use Hydra\Admin\Contracts\ScreenInterface;

/**
 * Page screen
 *
 * A screen that is just a template: a dashboard, a report, a settings page.
 * Left alone it renders through the admin's own controller; handedBy() points it
 * at one of your controller actions instead, which keeps the layout, breadcrumbs
 * and ability while the action is ordinary Hydra code.
 */
final class PageScreen implements ScreenInterface
{
    private string $path = '';
    private ?string $title = null;
    private ?string $ability = null;
    private ?string $presenter = null;

    /** @var array{0: class-string, 1: string}|null */
    private ?array $handler = null;

    private function __construct(
        private readonly string $name,
        private readonly string $template,
    ) {}

    public static function make(string $name, string $template): self
    {
        return new self($name, $template);
    }

    /** Path relative to the module root; '' is the module root itself. */
    public function at(string $path): self
    {
        $clone = clone $this;
        $clone->path = $path;

        return $clone;
    }

    public function title(string $title): self
    {
        $clone = clone $this;
        $clone->title = $title;

        return $clone;
    }

    /** @param class-string|null $ability */
    public function requires(?string $ability): self
    {
        $clone = clone $this;
        $clone->ability = $ability;

        return $clone;
    }

    /** @param class-string $presenter a PresenterInterface service id */
    public function presenter(string $presenter): self
    {
        $clone = clone $this;
        $clone->presenter = $presenter;

        return $clone;
    }

    /** @param array{0: class-string, 1: string} $handler */
    public function handledBy(array $handler): self
    {
        $clone = clone $this;
        $clone->handler = $handler;

        return $clone;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function method(): string
    {
        return 'GET';
    }

    public function path(): string
    {
        return $this->path;
    }

    public function handler(): array
    {
        return $this->handler ?? [AdminController::class, 'page'];
    }

    public function ability(): ?string
    {
        return $this->ability;
    }

    public function template(): string
    {
        return $this->template;
    }

    public function heading(): ?string
    {
        return $this->title;
    }

    /** @return class-string|null */
    public function presenterService(): ?string
    {
        return $this->presenter;
    }
}
