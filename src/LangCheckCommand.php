<?php namespace Ottowayne\LangCheck;

use Illuminate\Console\Command;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Lang;

class LangCheckCommand extends Command implements SelfHandling {

    protected $name = 'lang:check';
    protected $description = 'Checks if all language files have the same entries';

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        // get paths of registered namespace hints
        // e.g user in @lang('user::myview') resolving to app/Modules/User/Resources
        $resDirs = Config::get('langcheck.usehints') ? Lang::getHints() : array();
        $resDirs[base_path() . '/resources/lang'] = 'app';

        // check each resource directory
        foreach ($resDirs as $path => $hint) {
            // skip vendor directories
            if (Config::get('langcheck.skipvendor') && strpos($path, "vendor/") !== false)
                continue;

            // generate path relative to project root
            $shortPath = substr($path, strlen(base_path().'/'));
            $this->info("Checking '$shortPath'...");

            // load translation files into arrays
            $langDirs = File::directories($path);
            $languageData = array();
            foreach ($langDirs as $langDir) {
                $langCode = basename($langDir);
                $arrays = File::files($langDir);
                foreach ($arrays as $file) {
                    $fileName = basename($file);
                    $languageData[$langCode][$fileName] = File::getRequire($file);
                }
            }

            // compare language arrays with each other and find missing entries
            foreach ($languageData as $langCodeA => $langArraysA) {
                foreach ($langArraysA as $fileNameA => $langArrayA) {
                    foreach ($languageData as $langCodeB => $langArraysB) {
                        if ($langCodeA == $langCodeB)
                            continue;

                        $result = $this->array_diff_key_recursive($langArrayA, $langArraysB[$fileNameA]);
                        if (!empty($result)) {
                            $keys = implode($this->arrayKeysRecursive($result), ', ');
                            $this->error(" * File '$fileNameA'");
                            $this->error("   - Locale '$langCodeB' missing [$keys] existing in locale '$langCodeA'");
                        }
                    }
                }
            }

            $this->info('');
        }
    }

    /**
     * @param $a1
     * @param $a2
     * @return array
     */
    private function array_diff_key_recursive($a1, $a2)
    {
        $r = array();

        foreach ($a1 as $k => $v)
        {
            if (is_array($v))
            {
                if (!isset($a2[$k]) || !is_array($a2[$k]))
                {
                    $r[$k] = $a1[$k];
                }
                else if ($diff = $this->array_diff_key_recursive($a1[$k], $a2[$k]))
                {
                    $r[$k] = $diff;
                }
            }
            else if (!isset($a2[$k]) || is_array($a2[$k]))
            {
                $r[$k] = $v;
            }
        }

        return $r;
    }

    /**
     * @param $arr
     * @return array
     */
    private function arrayKeysRecursive($arr)
    {
        $iterator = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($arr));
        $keys = array();
        foreach ($iterator as $key => $value) {
            // Build long key name based on parent keys
            for ($i = $iterator->getDepth() - 1; $i >= 0; $i--) {
                $key = $iterator->getSubIterator($i)->key() . '.' . $key;
            }
            $keys[] = $key;
        }
        return  $keys;
    }
}
