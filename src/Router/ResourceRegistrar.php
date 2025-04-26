<?php


namespace NoamanAhmed\ApiCrudGenerator\Router;

use Illuminate\Routing\ResourceRegistrar as BaseResourceRegistrar;
use NoamanAhmed\Contracts\BaseRegistrarContract;

class ResourceRegistrar extends BaseResourceRegistrar  implements BaseRegistrarContract
{
    protected $resourceDefaults = [
        'index','analytics','dropdown','dropdownForStatus','export','import','show','store','update','destroy','destroyMulti','updateStatus',
    ];

    /**
     * Add the get dropdown method for a resourceful route.
     *
     * @param  string  $name
     * @param  string  $base
     * @param  string  $controller
     * @param  array   $options
     * @return \Illuminate\Routing\Route
     */
    public function addResourceDropdown($name, $base, $controller, $options)
    {
        $uri = $this->getResourceUri($name).'/dropdown';

        $action = $this->getResourceAction($name, $controller, 'dropdown', $options);

        return $this->router->get($uri, $action);
    }

    /**
     * Add get status for dropdown method for a resourceful route.
     *
     * @param  string  $name
     * @param  string  $base
     * @param  string  $controller
     * @param  array   $options
     * @return \Illuminate\Routing\Route
     */
    public function addResourceDropdownForStatus($name, $base, $controller, $options)
    {
        $uri = $this->getResourceUri($name).'/status/dropdown';

        $action = $this->getResourceAction($name, $controller, 'dropdownForStatus', $options);

        return $this->router->get($uri, $action);
    }

    /**
     * Add the updateStatus method for a resourceful route.
     *
     * @param  string  $name
     * @param  string  $base
     * @param  string  $controller
     * @param  array   $options
     * @return \Illuminate\Routing\Route
     */
    public function addResourceUpdateStatus($name, $base, $controller, $options)
    {
        $uri = $this->getResourceUri($name).'/update-status';

        $action = $this->getResourceAction($name, $controller, 'updateStatus', $options);

        return $this->router->patch($uri, $action);
    }


        /**
     * Add the ajax method for a resourceful route.
     *
     * @param  string  $name
     * @param  string  $base
     * @param  string  $controller
     * @param  array   $options
     * @return \Illuminate\Routing\Route
     */
    public function addResourceDestroyMulti($name, $base, $controller, $options)
    {
        $uri = $this->getResourceUri($name).'/multi-delete';

        $action = $this->getResourceAction($name, $controller, 'multiDestroy', $options);

        return $this->router->delete($uri, $action);
    }
    public function addResourceImport($name, $base, $controller, $options)
    {
        $uri = $this->getResourceUri($name).'/import';

        $action = $this->getResourceAction($name, $controller, 'import', $options);

        return $this->router->post($uri, $action);
    }

    public function addResourceExport($name, $base, $controller, $options)
    {
        $uri = $this->getResourceUri($name).'/export';

        $action = $this->getResourceAction($name, $controller, 'export', $options);

        return $this->router->post($uri, $action);
    }

    public function addResourceAnalytics($name, $base, $controller, $options)
    {
        $uri = $this->getResourceUri($name).'/analytics';

        $action = $this->getResourceAction($name, $controller, 'analytics', $options);

        return $this->router->post($uri, $action);
    }

    public function getResourceDefaults() : array {
        return $this->resourceDefaults;
    }
}
