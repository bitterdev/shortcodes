<?php

namespace Bitter\Shortcodes;

use Bitter\Shortcodes\API\V1\Middleware\FractalNegotiatorMiddleware;
use Concrete\Core\Routing\RouteListInterface;
use Concrete\Core\Routing\Router;

class RouteList implements RouteListInterface
{
    public function loadRoutes(Router $router)
    {

        $router
            ->buildGroup()
            ->setPrefix('/api/v1')
            ->addMiddleware(FractalNegotiatorMiddleware::class)
            ->routes(function ($groupRouter) {
                /** @var $groupRouter Router */

                // Tasks Endpoint
                $groupRouter->all('/shortcodes/get_all_shortcodes', [\Bitter\Shortcodes\API\V1\Shortcodes::class, 'getAllShortcodes']);
            });

        $router->buildGroup()->setNamespace('Concrete\Package\Shortcodes\Controller\Dialog\Support')
            ->setPrefix('/ccm/system/dialogs/shortcodes')
            ->routes('dialogs/support.php', 'shortcodes');
    }
}