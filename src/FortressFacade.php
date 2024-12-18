<?php

declare(strict_types=1);

namespace Laravelplus\Fortress;

use Illuminate\Support\Facades\Facade;

/**
 * @see Skeleton\SkeletonClass
 */
final class FortressFacade extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'fortress';
    }
}
