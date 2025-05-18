<?php

namespace Concrete\Package\Shortcodes\Controller\SinglePage\Dashboard\Shortcodes;

use Concrete\Core\Config\Repository\Repository;
use Concrete\Core\Http\Request;
use Concrete\Core\Http\ResponseFactoryInterface;
use Concrete\Core\Page\Controller\DashboardPageController;
use Symfony\Component\HttpFoundation\Response;

final class Settings extends DashboardPageController
{
    public function updated()
    {
        $this->setDefaults();
        $this->flash('success', t('Your settings have been saved.'));
    }

    private function setDefaults()
    {
        /** @var Repository $config */
        /** @noinspection PhpUnhandledExceptionInspection */
        $config = $this->app->make(Repository::class);
        $this->set('isEnabled', (bool)$config->get('shortcodes.enabled', true));
        $this->set('trackUsage', (bool)$config->get('shortcodes.track_usage', false));
    }

    public function view(): ?Response
    {
        /** @var Repository $config */
        /** @noinspection PhpUnhandledExceptionInspection */
        $config = $this->app->make(Repository::class);
        /** @noinspection PhpUnhandledExceptionInspection */
        /** @var ResponseFactoryInterface $responseFactory */
        $responseFactory = $this->app->make(ResponseFactoryInterface::class);
        /** @noinspection PhpUnhandledExceptionInspection */
        /** @var Request $request */
        $request = $this->app->make(Request::class);

        if ($request->getMethod() === "POST") {
            if (!$this->token->validate('update_settings')) {
                $this->flash('error', $this->token->getErrorMessage());
            } else {
                $config->save('shortcodes.enabled', (bool)$this->post('isEnabled'));
                $config->save('shortcodes.track_usage', (bool)$this->post('trackUsage'));

                return $responseFactory->redirect('/dashboard/shortcodes/settings/updated', Response::HTTP_TEMPORARY_REDIRECT);
            }
        }

        $this->setDefaults();

        return null;
    }
}
