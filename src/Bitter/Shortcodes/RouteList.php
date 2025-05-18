<?php

namespace Bitter\Shortcodes;

use Concrete\Core\Routing\RouteListInterface;
use Concrete\Core\Routing\Router;

class RouteList implements RouteListInterface
{
    public function loadRoutes(Router $router)
    {
        $router->buildGroup()->setNamespace('Concrete\Package\Shortcodes\Controller\Dialog\Support')
            ->setPrefix('/ccm/system/dialogs/shortcodes')
            ->routes('dialogs/support.php', 'shortcodes');
    }
}