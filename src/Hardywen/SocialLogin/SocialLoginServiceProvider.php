<?php namespace Hardywen\SocialLogin;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Session;

class SocialLoginServiceProvider extends ServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->package('hardywen/social-login');

        $config = $this->app->config->get('social-login::config');

        if($config['auto_logout']){
            Event::listen('auth.logout',function(){
                Session::forget("social_login");
            });
        }

    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app['social-login'] = $this->app->share(function ($app) {
            return new SocialLogin();
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('social-login');
    }

}
