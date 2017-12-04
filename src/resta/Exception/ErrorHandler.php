<?php

namespace Resta\Exception;

use Resta\ApplicationProvider;
use Resta\StaticPathModel;

class ErrorHandler extends ApplicationProvider {

    /**
     * @method handle
     * return void
     */
    public function handle(){

        //This function can be used for defining your own way of handling errors during runtime,
        //for example in applications in which you need to do cleanup of data/files when a critical error happens,
        //or when you need to trigger an error under certain conditions (using trigger_error()).
        set_error_handler([$this,'setErrorHandler']);

        //Registers a callback to be executed after script execution finishes or exit() is called.
        //Multiple calls to register_shutdown_function() can be made, and each will be called in the same order as
        //they were registered. If you call exit() within one registered shutdown function,
        //processing will stop completely and no other registered shutdown functions will be called.
        register_shutdown_function([$this,'fatalErrorShutdownHandler']);
    }

    /**
     * @param $errNo null
     * @param $errStr null
     * @param $errFile null
     * @param $errLine null
     * @param $errContext null
     * @return mixed
     */
    public function setErrorHandler($errNo=null, $errStr=null, $errFile=null, $errLine=null, $errContext=null){

        /**
         * @var $exception \Store\Config\Exception
         * get App Exception Config Class
         */
        $exception=StaticPathModel::$store.'\Config\Exception';

        //set as the success object is false
        $appExceptionSuccess=['success'=>false];

        //constant object
        $errType='Undefined';
        $errStrReal='';

        //Catch exception via preg match
        if(preg_match('@(.*?):@is',$errStr,$errArr)){
            $errType=trim(str_replace('Uncaught','',$errArr[1]));
        }

        if(preg_match('@(.*?):(.*?)in.\/@is',$errStr,$errStrRealArray)){
            $errStrReal=trim($errStrRealArray[2]);
        }

        if($errType==="Undefined"){
            $errStrReal=$errStr;
        }
        else{
            $errContext['trace']=$errStr;
        }

        //finally,set object for exception
        $appException=$appExceptionSuccess+$exception::handler($errNo,$errStrReal,$errFile,$errLine,$errType,$errContext);

        //set json app exception
        $this->app->kernel()->responseSuccess=false;
        $this->app->kernel()->responseStatus=$exception::exceptionTypeCodes($errType);
        $this->app->kernel()->router=$appException;
        echo $this->app->kernel()->out->handle();
        exit();


    }


    /**
     * @method fatalErrorShutdownHandler
     */
    public function fatalErrorShutdownHandler(){

        $last_error = error_get_last();
        if ($last_error['type'] === E_ERROR) {
            // fatal error
            $this->setErrorHandler(E_ERROR, $last_error['message'], $last_error['file'], $last_error['line'],[]);
        }
    }

}