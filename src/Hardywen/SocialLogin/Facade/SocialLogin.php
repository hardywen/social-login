<?php namespace Hardywen\SocialLogin\Facade;

use Illuminate\Support\Facades\Facade;

class SocialLogin extends Facade 
{

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() { return 'social-login'; }

}