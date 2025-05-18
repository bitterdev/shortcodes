<?php

namespace Bitter\Shortcodes\Provider;

use Bitter\Shortcodes\Entity\Shortcode;
use Bitter\Shortcodes\Entity\Usage;
use Bitter\Shortcodes\Listener\PageDelete;
use Bitter\Shortcodes\Listener\PageOutput;
use Bitter\Shortcodes\Listener\Resolve;
use Bitter\Shortcodes\RouteList;
use Bitter\Shortcodes\Shortcode\ShortcodeRepository;
use Bitter\Shortcodes\Usage\UsageRepository;
use Concrete\Core\Application\ApplicationAwareInterface;
use Concrete\Core\Application\ApplicationAwareTrait;
use Concrete\Core\Config\Repository\Repository;
use Concrete\Core\Routing\Router;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class ServiceProvider implements ApplicationAwareInterface
{
    use ApplicationAwareTrait;

    protected Repository $config;
    protected Router $router;

    public function __construct(
        Repository $repository,
        Router     $router
    )
    {
        $this->config = $repository;
        $this->router = $router;
    }

    public function register(): void
    {
        // If the add-on is disabled, we'll do nothing!
        if ((bool)$this->config->get('shortcodes.enabled', true) === false) {
            return;
        }

        $this->addBindings();
        $this->registerEventListeners();
        $this->registerRoutes();
    }

    protected function registerRoutes(): void
    {
        $list = new RouteList();
        $list->loadRoutes($this->router);
    }

    protected function addBindings(): void
    {
        // Make sure the shortcode repository is injected
        $this->app->when(ShortcodeRepository::class)
            ->needs(EntityRepository::class)
            ->give(function () {
                return $this->app->make(EntityManager::class)
                    ->getRepository(Shortcode::class);
            });

        // Make sure the usage repository is injected
        $this->app->when(UsageRepository::class)
            ->needs(EntityRepository::class)
            ->give(function () {
                return $this->app->make(EntityManager::class)
                    ->getRepository(Usage::class);
            });
    }

    protected function registerEventListeners(): void
    {
        /** @var EventDispatcherInterface $dispatcher */
        $dispatcher = $this->app['director'];

        $dispatcher->addListener('on_page_output', function ($event) {
            /** @var PageOutput $listener */
            $listener = $this->app->make(PageOutput::class);
            $listener->handle($event);
        });

        // Fired when a page is deleted from the trash can
        $dispatcher->addListener('on_page_delete', function ($event) {
            /** @var PageDelete $listener */
            $listener = $this->app->make(PageDelete::class);
            $listener->handle($event);
        });

        $dispatcher->addListener('on_shortcode_resolve', function ($event) {
            /** @var Resolve $listener */
            $listener = $this->app->make(Resolve::class);
            $listener->handle($event);
        });
    }
}
