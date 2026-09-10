<?php

declare(strict_types=1);

namespace Hydra\Admin\Contracts;

use Hydra\Admin\Definition;

/**
 * Module interface
 *
 * define() must stay pure — no request, no database. That is what makes the
 * whole admin inspectable as data, cacheable, and testable without HTTP.
 */
interface ModuleInterface
{
    public function define(): Definition;
}
