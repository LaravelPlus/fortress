<?php

namespace Laravelplus\Fortress;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Laravelplus\Fortress\Skeleton\SkeletonClass
 */
class FortressFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'fortress';
    }
}
