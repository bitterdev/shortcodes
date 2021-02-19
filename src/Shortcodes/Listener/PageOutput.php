<?php

namespace A3020\Shortcodes\Listener;

use A3020\Shortcodes\Shortcode\Replacer;
use A3020\Shortcodes\Usage\UsageTracker;
use Concrete\Core\Page\Page;

class PageOutput
{
    /**
     * @var Replacer
     */
    private $replacer;

    /**
     * @var \A3020\Shortcodes\Usage\UsageTracker
     */
    private $tracker;

    public function __construct(Replacer $replacer, UsageTracker $tracker)
    {
        $this->replacer = $replacer;
        $this->tracker = $tracker;
    }

    /**
     * @param \Symfony\Component\EventDispatcher\GenericEvent $event
     */
    public function handle($event)
    {
        /** @var Page $page */
        $page = Page::getCurrentPage();

        // Disable shortcodes in the dashboard area
        if ($page->isAdminArea()) {
            return;
        }

        $event->setArgument('contents',
            $this->replacer->findAndReplace(
                $event->getArgument('contents')
            )
        );

        $this->tracker->track(
            $page,
            $this->replacer->getResolved()
        );
    }
}
