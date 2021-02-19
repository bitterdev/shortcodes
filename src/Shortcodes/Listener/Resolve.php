<?php

namespace A3020\Shortcodes\Listener;

use Concrete\Core\Support\Facade\Package;

class Resolve
{
    /**
     * This is an example listener to illustrate how shortcodes can be replaced by custom code
     *
     * If the shortcode is 'shortcodes_version', we'll set the pkg version.
     *
     * @param \A3020\Shortcodes\Event\Resolve $event
     */
    public function handle($event)
    {
        if ($event->getShortcode()->getShortcode() === 'shortcodes_version') {
            /** @var \Concrete\Core\Package\Package $pkg */
            $pkg = Package::getByHandle('shortcodes');

            $event->setReplacement($pkg->getPackageVersion());
        }
    }
}
