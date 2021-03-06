<?php

namespace Resta\Config;

use Resta\Support\Str;
use Resta\Support\Utils;
use Resta\Foundation\ApplicationProvider;
use Resta\Foundation\PathManager\StaticPathList;

class ConfigKernelAssigner extends ApplicationProvider
{
    /**
     * @param array $files
     * @return void
     */
    public function setConfig($files=array())
    {
        // we are adding kernel variables
        $files['Kernel']    = path()->kernel().''.DIRECTORY_SEPARATOR.''.StaticPathList::$kernel.'.php';
        $files['Response']  = path()->storeConfigDir().''.DIRECTORY_SEPARATOR.'Response.php';

        // we are saving all paths in
        // the config directory of each application.
        foreach($files as $key=>$file){

            if(is_array($file)){

                $this->app->register('appConfig',Str::lower($key),[
                    'namespace' => null,
                    'file'      => null,
                    'data'      => $file
                ]);
            }

            elseif(file_exists($file)){

                $this->app->register('appConfig',Str::lower($key),[
                    'namespace' =>Utils::getNamespace($file),
                    'file'      =>$file
                ]);
            }
        }
    }
}