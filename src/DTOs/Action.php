<?php

namespace AdemtiApps\Supersonic\DTOs;

use function str_replace;

class Action
{
    /**
     * @var string
     */
    public string $searchName = '';

    /**
     * @param  string  $id
     * @param  string  $name
     * @param  string  $path
     * @param  array  $actions
     * @param  int  $depth
     * @param string $svgIcon
     */
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $path,
        public readonly array $actions,
        public readonly int $depth,
        public readonly string $svgIcon = ''
    ) {
        $this->searchName = ltrim(mb_strtolower(str_replace(' »', '', $path) . ' ' . $name));
    }
}
