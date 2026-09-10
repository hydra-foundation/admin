<?php

declare(strict_types=1);

namespace Hydra\Admin\Contracts;

/**
 * Form source interface
 *
 * How a module writes. Separate from {@see SourceInterface} on purpose: reading
 * a table does not imply the admin may write to it, and a module that is a log
 * of what happened should not be able to rewrite it by implementing one method
 * too many. The row a form starts from is read through {@see RowSourceInterface}.
 */
interface FormSourceInterface extends RowSourceInterface
{
    /** @param array<string, mixed> $data the validated subset, keyed by input name */
    public function update(string $id, array $data): void;
}
