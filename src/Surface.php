<?php

declare(strict_types=1);

namespace Hydra\Admin;

/**
 * Surface
 *
 * Where a field appears. One field declaration projects onto many surfaces.
 */
enum Surface: string
{
    case List = 'list';
    case Form = 'form';
    case Show = 'show';
}
