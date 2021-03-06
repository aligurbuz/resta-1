<?php

namespace Resta\Console\Source\Controller;

use Resta\Support\Utils;
use Resta\Config\Config;
use Resta\Console\ConsoleOutputter;
use Resta\Console\ConsoleListAccessor;
use Resta\Foundation\PathManager\StaticPathList;
use Resta\Foundation\PathManager\StaticPathModel;
use Resta\Services\Controller\Controller as CallController;

class Controller extends ConsoleOutputter {

    use ConsoleListAccessor;

    /**
     * @var $type
     */
    public $type='controller';

    /**
     * @var array
     */
    protected $runnableMethods = [
        'create'    => 'Creates a controller',
        'rename'    => 'Renames controller',
        'all'       => 'Lists all controller'
    ];

    /**
     * @var bool
     */
    protected $projectStatus = true;

    /**
     * @var array
     */
    protected $commandRule=[
        'create'    => ['controller'],
        'rename'    => ['controller','rename'],
        'all'       => [],
    ];

    public function all()
    {
        echo $this->info('All Route Controller Lists :');

        $this->table->setHeaders(['route','http','method','parameters','define','middleware','event','doc','status']);

        $controllers = (config('controller')===null) ? [] : config('controller');

        foreach ($controllers as $controller=>$detail){

            foreach ($detail as $namespace=>$permission){

                $call = new CallController($namespace);

                $methods = $call->getControllerMethods();

                foreach ($methods as $http=>$method){

                    $parameters = $call->getParameters($method['default']);

                    $this->table->addRow([
                        $controller,
                        $http,
                        $method['short'],
                        '/'.implode("/",$parameters['route']),
                        $parameters['define'],
                        '',
                        '',
                        '',
                        json_encode($permission)
                    ]);
                }


            }

        }

        echo $this->table->getTable();
    }

    /**
     * @method create
     * @return mixed
     */
    public function create()
    {
        $controller                     = $this->argument['controller'];
        $resourceInController           = $this->argument['resourceInController'] = StaticPathList::$resourceInController;
        $configurationInController      = $this->argument['configurationInController'] = StaticPathList::$configurationInController;


        //Processes related to argument variables via console.
        $this->argument['methodPrefix']         = StaticPathModel::$methodPrefix;
        $this->directory['endpoint']            = app()->path()->controller().''.DIRECTORY_SEPARATOR.''.$controller.''.StaticPathList::$controllerBundleName;
        $this->directory['resource']            = $this->directory['endpoint'].'/'.$resourceInController;
        $this->directory['policy']              = $this->directory['endpoint'].'/Policy';
        $this->directory['test']                = $this->directory['endpoint'].'/Test';
        $this->directory['configuration']       = $this->directory['endpoint'].'/'.$configurationInController;


        $this->argument['controllerNamespace']  = app()->namespace()->controller().'\\'.$controller.''.StaticPathList::$controllerBundleName;
        $this->argument['serviceClass']         = $controller;
        $this->argument['callClassPrefix']      = StaticPathModel::$callClassPrefix;
        $fullNamespaceForController             = $this->argument['controllerNamespace'].'\\'.$this->argument['serviceClass'].''.$this->argument['callClassPrefix'];

        if(!file_exists(config('kernel.paths.route'))){

            $this->directory['routes']       = config('kernel.paths.route');
        }

        // with the directory operation,
        // we get to the service directory, which is called the controller.
        $this->file->makeDirectory($this);

        if(isset($this->argument['resource']) && file_exists($this->directory['endpoint'])){

            $this->touch['service/resource']   = $this->directory['resource'].''.DIRECTORY_SEPARATOR.''.$this->argument['resource'].'.php';

            $this->file->touch($this);

            // and as a result we print the result on the console screen.
            echo $this->classical(' > Resource Controller called as "'.$this->argument['resource'].'" has been successfully created in the '.$this->directory['resource'].'');

        }
        else{

            // we process the processes related to file creation operations.
            // and then create files related to the touch method of the file object as it is in the directory process.
            $this->touch['service/endpoint']        = $this->directory['endpoint'].''.DIRECTORY_SEPARATOR.''.$this->argument['serviceClass'].''. $this->argument['callClassPrefix'].'.php';
            $this->touch['service/route']           = config('kernel.paths.route').''.DIRECTORY_SEPARATOR.''.$controller.'Route.php';
            $this->touch['service/resourceIndex']   = $this->directory['resource'].''.DIRECTORY_SEPARATOR.'index.html';
            $this->touch['service/app']             = $this->directory['endpoint'].''.DIRECTORY_SEPARATOR.'App.php';
            $this->touch['service/developer']       = $this->directory['configuration'].''.DIRECTORY_SEPARATOR.'Developer.php';
            $this->touch['service/conf']            = $this->directory['configuration'].''.DIRECTORY_SEPARATOR.'ServiceConf.php';
            $this->touch['service/dummy']           = $this->directory['configuration'].''.DIRECTORY_SEPARATOR.'Dummy.yaml';
            $this->touch['service/doc']             = $this->directory['configuration'].''.DIRECTORY_SEPARATOR.'Doc.yaml';
            $this->touch['service/readme']          = $this->directory['endpoint'].''.DIRECTORY_SEPARATOR.'README.md';
            $this->touch['service/testIndex']       = $this->directory['test'].''.DIRECTORY_SEPARATOR.'index.html';
            $this->touch['service/policy']          = $this->directory['policy'].''.DIRECTORY_SEPARATOR.''.$this->argument['serviceClass'].''. $this->argument['callClassPrefix'].'Policy.php';

            $this->file->touch($this,[
                'stub'=>'Controller_Create'
            ]);

            $this->docUpdate();

            Config::make('controller')->set([
                $this->argument['serviceClass']=> [$fullNamespaceForController=>['all'=>true]]
            ]);

            // and as a result we print the result on the console screen.
            echo $this->classical(' > Controller called as "'.$controller.'" has been successfully created in the '.app()->namespace()->call().'');


        }



    }

    /**
     * @return mixed
     *
     */
    public function rename()
    {
        $path = path()->controller().'/'.$this->argument['controller'].''.StaticPathList::$controllerBundleName;

        if(file_exists($path)){

            $newPathName = str_replace($this->argument['controller'],$this->argument['rename'],$path);


            rename($path,$newPathName);

            $getAllFiles = Utils::getAllFilesInDirectory($newPathName);

            $getPathWithPhpExtensions = Utils::getPathWithPhpExtension($getAllFiles,$newPathName);

            foreach ($getPathWithPhpExtensions as $getPathWithPhpExtension){

                $withoutFullPath = str_replace($newPathName,"",$getPathWithPhpExtension);

                $newName = $newPathName."".preg_replace("((.*)".$this->argument['controller'].")", "$1".$this->argument['rename'], $withoutFullPath);

                rename($getPathWithPhpExtension,$newName);

                Utils::changeClass($newName,
                    [
                       $this->argument['controller']=>$this->argument['rename']
                    ]);

            }

            echo $this->classical($this->argument['controller'].' Controller has been changed as '.$this->argument['rename'].' Controller');

        }
    }

    /**
     * @return void
     */
    private function docUpdate()
    {
        $docPath = $this->directory['configuration'] .'/Doc.yaml';

        $doc = Utils::yaml($docPath);

        $data = [

            'index'=>[
                'http'=>'get',
                'params'=>[
                    'page'=>[
                        'required'=>false,
                        'type'=>'numeric'
                    ]
                ],
                'headers'=>[],
            ]
        ];

        $doc->set($data);
    }
}