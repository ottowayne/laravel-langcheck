<?php namespace Ottowayne\LangCheck;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

class Translator extends \Illuminate\Translation\Translator
{

    /**
     * @var array
     */
    protected $checkConfig = array();

    /**
     * @var array
     */
    protected $hints = array();

    /**
     * Get the translation for the given key.
     *
     * @param  string  $key
     * @param  array   $replace
     * @param  string  $locale
     * @return string
     */
    public function get($key, array $replace = array(), $locale = null)
    {
        $result = parent::get($key, $replace, $locale);

        if ($result === $key) {
            $locale = ($locale ?: app()->getLocale());

            Event::fire('LangCheck::missingtranslation', [$key, $locale]);

            if ($this->checkConfig['exception'])
                throw new \InvalidArgumentException("Key '$key' not available for locale '$locale'");

            if ($this->checkConfig['log'])
                Log::error("Key '$key' not available for locale '$locale'");
        }

        return $result;
    }

    /**
     * Add a new namespace to the loader.
     *
     * @param  string  $namespace
     * @param  string  $hint
     * @return void
     */
    public function addNamespace($namespace, $hint)
    {
        parent::addNamespace($namespace, $hint);

        $this->hints[$hint] = $namespace;
    }

    /**
     * @return array
     */
    public function getHints()
    {
        return $this->hints;
    }

    /**
     * @param $config
     */
    public function setConfig($config)
    {
        $this->checkConfig = $config;
    }
}
