<?php

namespace Concrete\Package\Shortcodes\Controller\SinglePage\Dashboard\Shortcodes;

use Concrete\Core\Config\Repository\Repository;
use Concrete\Core\Page\Controller\DashboardPageController;
use Concrete\Core\Routing\Redirect;

final class Settings extends DashboardPageController
{
    public function view()
    {
        /** @var Repository $config */
        $config = $this->app->make(Repository::class);

        $this->set('isEnabled', (bool) $config->get('shortcodes.enabled', true));
        $this->set('trackUsage', (bool) $config->get('shortcodes.track_usage', false));
    }

    public function save()
    {
        if (!$this->token->validate('a3020.shortcodes.settings')) {
            $this->flash('error', $this->token->getErrorMessage());

            return Redirect::to('/dashboard/shortcodes/settings');
        }

        /** @var Repository $config */
        $config = $this->app->make(Repository::class);
        $config->save('shortcodes.enabled', (bool) $this->post('isEnabled'));
        $config->save('shortcodes.track_usage', (bool) $this->post('trackUsage'));

        $this->flash('success', t('Your settings have been saved.'));

        return Redirect::to('/dashboard/shortcodes/settings');
    }
}
