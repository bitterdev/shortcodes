<?php

namespace Bitter\Shortcodes\API\V1\Middleware;

use Bitter\Shortcodes\API\V1\Serializer\SimpleSerializer;
use Concrete\Core\Http\Middleware\FractalNegotiatorMiddleware as CoreFractalNegotiatorMiddleware;

class FractalNegotiatorMiddleware extends CoreFractalNegotiatorMiddleware
{
    public function getSerializer(): SimpleSerializer
    {
        return new SimpleSerializer();
    }
}
