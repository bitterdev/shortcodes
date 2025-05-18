<?php

namespace Bitter\Shortcodes\Listener;

use Bitter\Shortcodes\Usage\UsageRepository;
use Concrete\Core\Page\DeletePageEvent;
use Exception;

class PageDelete
{
    protected UsageRepository $usageRepository;

    public function __construct(
        UsageRepository $usageRepository
    )
    {
        $this->usageRepository = $usageRepository;
    }

    public function handle(DeletePageEvent $event): void
    {
        try {
            $this->usageRepository->deleteByPage(
                $event->getPageObject()->getCollectionID()
            );
        } catch (Exception) {
            // Silently fail
        }
    }
}
