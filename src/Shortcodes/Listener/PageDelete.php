<?php

namespace A3020\Shortcodes\Listener;

use A3020\Shortcodes\Usage\UsageRepository;
use Concrete\Core\Page\DeletePageEvent;
use Exception;

class PageDelete
{
    /**
     * @var UsageRepository
     */
    private $usageRepository;

    public function __construct(UsageRepository $usageRepository)
    {
        $this->usageRepository = $usageRepository;
    }

    /**
     * @param DeletePageEvent $event
     */
    public function handle($event)
    {
        try {
            $this->usageRepository->deleteByPage(
                $event->getPageObject()->getCollectionID()
            );
        } catch (Exception $e) {
            // Silently fail
        }
    }
}
