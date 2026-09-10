<?php

declare(strict_types=1);

namespace Hydra\Admin\Contracts;

/**
 * Create source interface
 *
 * How a module adds a row. Separate from {@see UpdateSourceInterface} because
 * the two are separate permissions in every application that has ever had them:
 * a table whose rows may be corrected is not thereby a table anyone may add to.
 */
interface CreateSourceInterface
{
    /**
     * The id of the row written, so the screen can send the visitor to it.
     *
     * @param array<string, mixed> $data the validated subset, keyed by input name
     */
    public function create(array $data): string;
}
