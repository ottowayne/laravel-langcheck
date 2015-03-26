<?php namespace Ottowayne\LangCheck;

use Illuminate\Translation\TranslationServiceProvider;

class LangCheckServiceProvider extends TranslationServiceProvider {

    public function boot()
    {
        $configPath = __DIR__ . '/config/langcheck.php';
        $this->mergeConfigFrom($configPath, 'langcheck');
        $this->publishes([$configPath => config_path('langcheck.php')]);
    }

    public function register()
    {
        parent::register();

        $this->commands(['Ottowayne\LangCheck\LangCheckCommand']);

        $this->app->singleton('translator', function($app) {
            $loader = $app['translation.loader'];

            $locale = $app['config']['app.locale'];

            $trans = new Translator($loader, $locale);

            $trans->setConfig($app['config']['langcheck']);

            return $trans;
        });
    }
}
