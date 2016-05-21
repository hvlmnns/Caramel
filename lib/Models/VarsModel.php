<?php

namespace Caramel\Models;


use Caramel\Repositories\StorageRepository;


/**
 * Class Vars
 *
 * @package Caramel
 */
class Vars extends StorageRepository
{
    public function set($path,$value,$cached = false)
    {
        // TODO: cache per session or cookie
        parent::set($path,$value);
    }
}