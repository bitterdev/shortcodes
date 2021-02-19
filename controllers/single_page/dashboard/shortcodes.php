<?php

namespace Concrete\Package\Shortcodes\Controller\SinglePage\Dashboard;

use Concrete\Core\Page\Controller\DashboardPageController;
use Concrete\Core\Routing\Redirect;

final class Shortcodes extends DashboardPageController
{
    public function view()
    {
        return Redirect::to('/dashboard/shortcodes/search');
    }
}
