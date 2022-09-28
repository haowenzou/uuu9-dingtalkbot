<?php

namespace U9\DingtalkBot\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class DingtalkBot
 *
 * @method static bool send(string $message, string $to = 'default', array $atMobiles = [], $isAtAll = false)
 *
 * @package U9\DingtalkBot\Facades
 */
class DingtalkBot extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'dingtalkbot';
    }
}
