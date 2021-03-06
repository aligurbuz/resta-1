<?php

namespace Resta\Middleware;

use Resta\Foundation\ApplicationProvider;

class MiddlewareKernelAssigner extends ApplicationProvider
{
    /**
     * @return void
     */
    public function setMiddleware()
    {
        //We are logging the kernel for the middleware class and the exclude class.
        $this->app->register('middlewareClass',$this->app->resolve(app()->namespace()->serviceMiddleware()));
        $this->app->register('excludeClass',$this->app->resolve(ExcludeMiddleware::class));
    }

    /**
     * @param $middleValue
     * @return void
     */
    public function pointer($middleValue)
    {
        if(isset($this->app['pointer']['middlewareList'])){

            $middlewareList = $this->app['pointer']['middlewareList'];

            if(is_array($middlewareList)){
                $middlewareList = array_merge($middlewareList,[$middleValue]);
                $this->app->register('pointer','middlewareList',$middlewareList);
            }
        }
        else{
            $this->app->register('pointer','middlewareList',[$middleValue]);
        }
    }
}