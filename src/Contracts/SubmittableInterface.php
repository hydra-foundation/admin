<?php

declare(strict_types=1);

namespace Hydra\Admin\Contracts;

/**
 * Submittable interface
 *
 * A screen that also answers a POST at its own URL. The scanner emits the second
 * route; nothing else about the screen changes, so a submission is still just a
 * request to the URL the form is already at.
 */
interface SubmittableInterface
{
    /** @return array{0: class-string, 1: string} */
    public function submitHandler(): array;
}
