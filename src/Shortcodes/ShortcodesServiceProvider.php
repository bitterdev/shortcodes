<?php

namespace A3020\Shortcodes;

use A3020\Shortcodes\Listener\PageDelete;
use A3020\Shortcodes\Listener\PageOutput;
use A3020\Shortcodes\Listener\Resolve;
use Concrete\Core\Application\ApplicationAwareInterface;
use Concrete\Core\Application\ApplicationAwareTrait;
use Concrete\Core\Config\Repository\Repository;
use Doctrine\ORM\EntityManager;

final class ShortcodesServiceProvider implements ApplicationAwareInterface
{
    use ApplicationAwareTrait;

    /**
     * @var Repository
     */
    private $config;

    public function __construct(Repository $repository)
    {
        $this->config = $repository;
    }

    public function register()
    {
        // If the add-on is disabled, we'll do nothing!
        if ((bool) $this->config->get('shortcodes.enabled', true) === false) {
            return;
        }

        $this->bindings();
        $this->listeners();
    }

    private function bindings()
    {
        // Make sure the shortcode repository is injected
        $this->app->when(\A3020\Shortcodes\Shortcode\ShortcodeRepository::class)
            ->needs(\Doctrine\ORM\EntityRepository::class)
            ->give(function(){
                return $this->app->make(EntityManager::class)
                    ->getRepository(\A3020\Shortcodes\Entity\Shortcode::class);
            });

        // Make sure the usage repository is injected
        $this->app->when(\A3020\Shortcodes\Usage\UsageRepository::class)
            ->needs(\Doctrine\ORM\EntityRepository::class)
            ->give(function(){
                return $this->app->make(EntityManager::class)
                    ->getRepository(\A3020\Shortcodes\Entity\Usage::class);
            });
    }

    private function listeners()
    {
        /** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher */
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
