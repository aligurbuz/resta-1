<?php

namespace Resta\Container;

use Resta\Support\Utils;
use Resta\Console\ConsoleBindings;
use Resta\Contracts\ContainerContracts;
use Resta\Container\ContainerKernelAssigner;
use Resta\Container\ContainerKernelAssignerForBind as GlobalAssignerForBind;

class Container implements ContainerContracts,\ArrayAccess
{
    /**
     * @var $singleton
     */
    public $singleton=false;

    /**
     * @var $kernel
     */
    public $kernel;

    /**
     * @var array  $instance
     */
    private static $instance=[];

    /**
     * @var array $instances
     */
    private $instances = [];

    /**
     * @var array $bindParams
     */
    private static $bindParams=[];

    /**
     * @var $unregister
     */
    protected $unregister;

    /**
     * @var array
     */
    protected $values=[];

    /**
     * @return mixed
     */
    public function kernel()
    {
        //The kernel object system is the container backbone.
        //Binding binding and container loads are done with
        //the help of this object and distributed to the system.
        return $this->kernel;
    }

    /**
     * @return mixed
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public function kernelAssigner()
    {
        //We will use the kernelAssigner class to resolve the singleton object state.
        return $this->resolve(ContainerKernelAssigner::class);
    }

    /**
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    private function serviceContainerObject()
    {
        //Since the objects that come to the build method are objects from the container method,
        //we need to automatically create a kernel object named serviceContainer in this method.
        $this->kernelAssigner()->container();
    }

    /**
     * Register an existing instance as shared in the container.
     *
     * @param  string  $abstract
     * @param  mixed   $instance
     * @return mixed
     */
    public function instance($abstract, $instance)
    {
        // we'll check to determine if this type has been bound before, and if it has
        // we will fire the rebound callbacks registered with the container and it
        // can be updated with consuming classes that have gotten resolved here.
        $this->instances[$abstract] = $instance;
    }

    /**
     * @param null $object
     * @param null $callback
     * @param bool $container
     * @return mixed
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public function bind($object=null,$callback=null,$container=false)
    {
        //we check whether the boolean value of the singleton variable used
        //for booting does not reset every time the object variable to be assigned to the kernel variable is true
        $this->singleton();

        //The console share is evaluated as a true variable to be assigned as the 3rd parameter in the classes to be bound.
        //The work to be done here is to bind the classes to be included in the console share privately.
        if($container){
            $this->consoleShared($object,$callback);
        }

        //If the third parameter passed to the bind method carries a container value,
        //then you will not be able to fire the build method instead of the make method.
        $makeBuild=($container==="container") ? 'build' : 'make';

        //If the bind method does not have parameters object and callback, the value is directly assigned to the kernel object.
        //Otherwise, when the bind object and callback are sent, the closure class inherits
        //the applicationProvider object and the resolve method is called
        return ($object===null) ? $this->kernel() : $this->{$makeBuild}($object,$callback);

    }

    /**
     * @param null $object
     * @param null $callback
     * @return mixed
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public function container($object=null,$callback=null)
    {
        //If the bind method does not have parameters object and callback, the value is directly assigned to the kernel object.
        //Otherwise, when the bind object and callback are sent, the closure class inherits
        //the applicationProvider object and the resolve method is called
        return $this->bind($object,$callback,'container');
    }

    /**
     * @param $eventName
     * @param $object
     * @return mixed
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public function addEvent($eventName,$object)
    {
        //Since the objects that come to the build method are objects from the container method,
        //we need to automatically create a kernel object named serviceContainer in this method.
        $this->kernelAssigner()->event();

        //If the bind method does not have parameters object and callback, the value is directly assigned to the kernel object.
        //Otherwise, when the bind object and callback are sent, the closure class inherits
        //the applicationProvider object and the resolve method is called
        return $this->bind($eventName,$object,'container');

    }

    /**
     * @method singleton
     */
    public function singleton()
    {
        if($this->singleton===false){

            //after first initializing, the singleton variable is set to true,
            //and subsequent incoming classes can inherit the loaded object.
            $this->singleton=true;
            $this->kernel=\application::kernelBindObject();
        }

        //kernel object taken over
        return $this->kernel();
    }

    /**
     * @param $object
     * @param $callback
     * @param bool $sync
     * @return mixed
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    private function make($object,$callback,$sync=false)
    {
        //If the console object returns true,
        //we do not cancel binding operations
        //We are getting what applies to console with consoleKernelObject.
        if($sync===false) return $this->consoleKernelObjectChecker($object,$callback);

        //we automatically load a global loaders for the bind method
        //and assign it to the object name in the kernel object with bind,
        //which you can easily use in the booted classes for kernel object assignments.
        $this->globalAssignerForBind($object,$callback);

        //the value corresponding to the bind value for the global object is assigned and
        //the resolve method is called for the dependency injection.
        $this->kernelAssigner()->setKernelObject($object,$callback);

        //return kernel object
        return $this->kernel();
    }

    /**
     * @param $object
     * @param bool $container
     * @return mixed
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    private function consoleKernelObject($object,$container=false)
    {
        //we use the console bindings class to specify the classes to be preloaded in the console application.
        //Thus, classes that can not be bound with http are called without closure in global loaders directory.
        $this->resolve(ConsoleBindings::class)->console($object,$container);

        //The console application must always return the kernel method.
        return $this->kernel();
    }

    /**
     * @param $object
     * @param $callback
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    private function consoleShared($object,$callback)
    {
        //The console share is evaluated as a true variable to be assigned as the 3rd parameter in the classes to be bound.
        //The work to be done here is to bind the classes to be included in the console share privately.
        $this->kernelAssigner()->consoleShared($object,$callback);
    }

    /**
     * @param $object
     * @param $callback
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    private function globalAssignerForBind($object,$callback)
    {
        //we automatically load a global loaders for the bind method
        //and assign it to the object name in the kernel object with bind,
        //which you can easily use in the booted classes for kernel object assignments.
        $this->resolve(GlobalAssignerForBind::class)->getAssigner($object,$callback);

    }

    /**
     * @param $object
     * @param $callback
     * @param bool $sync
     * @return mixed
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public function build($object,$callback,$sync=false)
    {
        //If the console object returns true,
        //we do not cancel binding operations
        //We are getting what applies to console with consoleKernelObject.
        if($sync===false) return $this->consoleKernelObjectChecker($object,$callback,true);

        //Since the objects that come to the build method are objects from the container method,
        //we need to automatically create a kernel object named serviceContainer in this method.
        $this->serviceContainerObject();

        //the value corresponding to the bind value for the global object is assigned and
        //the resolve method is called for the dependency method.
        $this->kernelAssigner()->setKernelObject($object,$callback,'serviceContainer');

        //return kernel object
        return $this->kernel();
    }

    /**
     * @param $object
     * @param $callback
     * @param bool $container
     * @return mixed
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    private function consoleKernelObjectChecker($object,$callback,$container=false)
    {
        //we check whether the callback value is a callable function.
        $isCallableForCallback=is_callable($callback);

        //we automatically load a global loaders for the bind method
        //and assign it to the object name in the kernel object with bind,
        //which you can easily use in the booted classes for kernel object assignments.
        $this->globalAssignerForBind($object,$callback);

        //If the console object returns true,
        //we do not cancel binding operations
        //We are getting what applies to console with consoleKernelObject.
        if($this->console() AND $isCallableForCallback) return $this->consoleKernelObject($object,$container);

        //If the application is not a console operation, we re-bind to existing methods synchronously.
        return ($container) ? $this->build($object,$callback,true) : $this->make($object,$callback,true);
    }

    /**
     * @param $class
     * @param array $bind
     * @return mixed
     *
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public function resolve($class,$bind=array())
    {
        //the context bind objects are checked again and the bind sequence submitted by
        //the user is checked and forced to re-instantiate the object.
        $this->contextualBindCleaner($class,$bind);

        //We do an instance check to get the static instance values of
        //the classes to be resolved with the make bind method.
        if(!isset(self::$instance[$class])){

            //bind params object
            self::$bindParams[$class]=$bind;

            //By singleton checking, we solve the dependency injection of the given class.
            //Thus, each class can be called together with its dependency.
            self::$instance[$class]=DIContainerManager::make($class,$this->applicationProviderBinding($this,self::$bindParams[$class]));
            $this->singleton()->resolve[class_basename($class)]=self::$instance[$class];

            //return resolve class
            return self::$instance[$class];
        }

        //if the class to be resolved has already been loaded,
        //we get the instance value that was saved to get the recurring instance.
        return self::$instance[$class];

    }

    /**
     * @param $class
     * @param $bind
     */
    private function contextualBindCleaner($class,$bind)
    {
        //the context bind objects are checked again and the bind sequence submitted by
        //the user is checked and forced to re-instantiate the object.
        if(isset(self::$instance[$class]) && self::$bindParams[$class]!==$bind){
            unset(self::$instance[$class]);
            unset(self::$bindParams[$class]);
        }
    }

    /**
     * @param $make
     * @param array $bind
     * @return array
     */
    public function applicationProviderBinding($make,$bind=array())
    {
        //service container is an automatic application provider
        //that we can bind to the special class di in the dependency condition.
        //This method is automatically added to the classes resolved by the entire make bind method.
        return array_merge($bind,['app'=>$make]);
    }

    /**
     * @param $key
     * @param $object
     * @param null $concrete
     * @return bool|mixed
     */
    public function register($key,$object,$concrete=null)
    {
        // we assign the values ​​required
        // for register to the global value variable.
        $this->values['key']        = $key;
        $this->values['object']     = $object;
        $this->values['concrete']   = $concrete;

        // If there is an instance of the application class,
        // the register method is saved both in this example and in the global.
        if(defined('appInstance')){

            // where we will assign both the global instance
            // and the registered application object.
            $this->setAppInstance($this->singleton());
            $this->setAppInstance(core());

            return false;
        }

        // we are just doing global instance here.
        $this->setAppInstance($this->singleton());
    }

    /**
     * @param $instance
     * @param bool $withConcrete
     * @return bool
     */
    private function registerProcess($instance,$withConcrete=false)
    {
        // values recorded without concrete.
        // or values deleted
        if(false===$withConcrete){

            //values registered without concrete
            $instance->{$this->values['key']}=$this->values['object'];
            return false;
        }

        //values registered with concrete
        $instance->{$this->values['key']}[$this->values['object']]=$this->values['concrete'];
    }

    /**
     * @param $instance
     * @return bool
     */
    private function setAppInstance($instance)
    {
        // for application instance
        // if the values ​​to be saved are to be saved without the concrete,
        // if it is an array.
        if($this->values['concrete']===null) {

            // Without concrete,
            // the saved value will be saved
            // if the it does not exist in application instance.
            if(!isset($instance->{$this->values['key']})) {
                $this->registerProcess($instance);
            }
            return false;
        }

        // We send concrete values to be recorded with concrete as true.
        // these values will be recorded as a array.
        $this->registerProcess($instance,true);
    }

    /**
     * @param $instance
     * @param $key
     * @param null $object
     * @return mixed
     */
    public function terminate($key,$object=null)
    {
        // object null is
        // sent to just terminate a key.
        if($object===null){
            unset(core()->{$key});
            return false;
        }

        // It is used to delete
        // both key and sequence members.
        unset(core()->{$key}[$object]);
    }

    /**
     * @param $offset
     * @param $value
     */
    public function offsetSet($offset, $value) {

    }

    /**
     * @param $offset
     * @return bool
     */
    public function offsetExists($offset) {
        return isset($this->container[$offset]);
    }

    /**
     * @param $offset
     */
    public function offsetUnset($offset) {
        unset($this->container[$offset]);
    }

    /**
     * @param $offset
     * @return null
     */
    public function offsetGet($offset) {

        return $this->resolve($this->instances['containerInstanceResolve'],[
            'instances' => $this->instances
        ])->{$offset}();
    }

    /**
     * Dynamically access container services.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this[$key];
    }
    /**
     * Dynamically set container services.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function __set($key, $value)
    {
        $this[$key] = $value;
    }
}