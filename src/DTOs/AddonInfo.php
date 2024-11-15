<?php

namespace AdemtiApps\Supersonic\DTOs;


use Exception;
use Statamic\Facades\Addon;

class AddonInfo
{
    /**
     * @var \Statamic\Extend\Addon
     */
    private \Statamic\Extend\Addon $addon;

    /**
     * Retrieve the add-on info.
     */
    public function __construct()
    {
        /**
         * Retrieve the add-on info.
         */
        $this->addon = Addon::get('ademti-apps/supersonic');

    }

    /**
     * Whether the Pro edition is active.
     *
     * @return bool
     * @throws Exception
     */
    public function isPro() {
        return $this->addon->edition() === 'pro';
    }
}
