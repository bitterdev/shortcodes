<?php

namespace Bitter\Shortcodes\Listener;

use Concrete\Core\Package\PackageService;
use Bitter\Shortcodes\Event\Resolve as ResolveEvent;

class Resolve
{
    protected PackageService $packageService;

    public function __construct(
        PackageService $packageService
    )
    {
        $this->packageService = $packageService;
    }

    /**
     * This is an example listener to illustrate how shortcodes can be replaced by custom code
     *
     * If the shortcode is 'shortcodes_version', we'll set the pkg version.
     *
     * @param ResolveEvent $event
     */
    public function handle(ResolveEvent $event): void
    {
        if ($event->getShortcode()->getShortcode() === 'shortcodes_version') {
            $pkg = $this->packageService->getByHandle("shortcodes");
            $event->setReplacement($pkg->getPackageVersion());
        }
    }
}
