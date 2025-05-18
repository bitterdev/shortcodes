<?php

namespace Bitter\Shortcodes\Listener;

use Bitter\Shortcodes\Shortcode\Replacer;
use Bitter\Shortcodes\Usage\UsageTracker;
use Concrete\Core\Page\Page;
use Symfony\Component\EventDispatcher\GenericEvent;

class PageOutput
{
    protected Replacer $replacer;
    protected UsageTracker $tracker;

    public function __construct(
        Replacer     $replacer,
        UsageTracker $tracker
    )
    {
        $this->replacer = $replacer;
        $this->tracker = $tracker;
    }

    public function handle(GenericEvent $event): void
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
