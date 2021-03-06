<?php

namespace Resta\Environment;

use Resta\Support\Utils;

class CheckEnvironmentFile
{
    /**
     * @method checkConfiguration
     * @return mixed
     */
    public function checkConfiguration()
    {
        //if the env file does not exist, we automatically detect
        //that the environment variable is in the production environment.
        if(!file_exists($this->getEnvironmentPath())){
            return [];
        }

        //if there is an env file for the application then
        //we parse this file which is detected as a patch file
        //and save the application to detect the environmental variables
        return $this->identifierEnvironment();

    }

    /**
     * @param bool $status
     * @return mixed
     */
    public function getEnvironment($status=false)
    {
        //If the status value is false then direct path is invoked. If true is sent, variables are sent.
        return (false === $status) ? $this->getEnvironmentPath() : $this->getEnvironmentVariables();
    }

    /**
     * @return mixed
     */
    private function getEnvironmentPath()
    {
        //We call environment path with this method
        return path()->environmentFile();
    }

    /**
     * @return mixed
     */
    private function getEnvironmentVariables()
    {
        //We call environment variables path with this method
        return Utils::yaml($this->getEnvironmentPath())->get();
    }

    /**
     * @return mixed
     */
    private function identifierEnvironment()
    {
        //We parse our environment variables through the yaml file.
        $environment=$this->getEnvironment(true);

        //the application will automatically throw an exception
        //if there is no env key in the parse variables.
        if(!isset($environment['env'])){
            exception()->invalidArgument('The env key missing on your environment');
        }

        //and finally save the environment
        return $environment;
    }
}