<?php

namespace Modules\User\Providers;

use Dingo\Api\Routing\Router;
use Modules\Core\Providers\RouteServiceProvider as CoreRouteServiceProvider;

class RouteServiceProvider extends CoreRouteServiceProvider
{
    /**
     * The module namespace to assume when generating URLs to actions.
     *
     * @var string
     */
    protected $moduleNamespace = 'Modules\User\Http\Controllers\Api';

    protected function getApiRoute(Router $api)
    {
        $api->version('v1', ['namespace' => $this->moduleNamespace], function ($api) {
            include  __DIR__ . '/../Routes/api.php';
        });
    }
}
