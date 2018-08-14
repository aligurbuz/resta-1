<?php

namespace Resta\Config;

use Resta\Utils;
use Resta\StaticPathModel;
use Resta\Contracts\KernelBindContracts;
use Resta\GlobalLoaders\Config as ConfigGlobalInstance;

class ConfigLoader {

    /**
     * @param ConfigGlobalInstance $config
     */
    public function handle(ConfigGlobalInstance $config){

        //We run a glob function for all of the config files,
        //where we pass namespace and paths to a kernel object and process them.
        $configFiles=Utils::glob(app()->path()->config());

        //The config object is a kernel object
        //that can be used to call all class and array files in the config directory of the project.
        $config->setConfig($configFiles);

        // Finally, we will set
        // the application's timezone and encoding based on the configuration
        date_default_timezone_set(config('app.timezone'));
        mb_internal_encoding('UTF-8');
    }

}