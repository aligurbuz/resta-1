<?php

namespace Resta\Config;

use Resta\Support\Arr;
use Resta\Support\Str;
use Resta\Support\Utils;
use Resta\Traits\PropertyAccessibility;

class ConfigProcess implements \ArrayAccess
{
    //get property accessibility
    use PropertyAccessibility;

    /**
     * @var $config
     */
    protected $config = null;

    /**
     * @var array
     */
    protected $configList = [];

    /**
     * ConfigProcess constructor.
     * @param null $config
     */
    public function __construct($config=null)
    {
        if($this->config===null){
            $this->config = $config;
        }
    }

    /**
     * @return mixed|null
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public function get()
    {
        $kernelConfig = [];

        //we are getting the config data from the kernel object..
        if(isset(resta()->appConfig)){
            $kernelConfig = resta()->appConfig;
        }

        // if the config variable is not sent,
        // we print the kernel config data directly.
        if(null===$this->config) {
            return (count($kernelConfig)) ? $kernelConfig : [];
        }

        // we are starting a array of
        // point-based logical processes for config data processing.
        $this->configList = Str::stringToArray($this->config);

        //if the config object exists in the kernel, start the process.
        if(isset($kernelConfig[$config = current($this->configList)])){

            // get config data
            // we process and rotate on point basis.
            $configData = $this->getConfigData($kernelConfig,$config);
            return $this->configProcessResult($configData);
        }

        return [];
    }

    /**
     * @param $config
     * @return mixed
     */
    private function configProcessResult($config)
    {
        //config data if dotted.
        if(count($this->configList)){

            array_shift($this->configList);
            $configdotted = $config;

            //we apply the dotted-knit config dataset as nested.
            foreach ($this->configList as $key=>$value){
                $configdotted = Arr::isset($configdotted,$value);
            }
        }

        // if the config data is not dotted,normal data is printed
        // if not, the nested data is printed.
        return (isset($configdotted)) ? $configdotted : null;
    }

    /**
     * @param $kernelConfig
     * @param $config
     * @return mixed
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    private function getConfigData($kernelConfig,$config)
    {
        //if the config data is a class instance, we get it as an object.
        if(Utils::isNamespaceExists($configFile = $kernelConfig[$config]['namespace'])){
            $configData = Utils::makeBind($configFile)->handle();
        }

        //if the config data is just an array.
        if(!isset($configData) && file_exists($configFile=$kernelConfig[$config]['file'])){
            $configData = require($configFile);
        }

        //return config data
        return isset($configData) ? $configData : [];
    }
}