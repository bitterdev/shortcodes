<?php

namespace A3020\Shortcodes\Usage;

use A3020\Shortcodes\Entity\Shortcode;
use Concrete\Core\Config\Repository\Repository;
use Concrete\Core\Page\Page;
use Exception;

class UsageTracker
{
    /**
     * @var Repository
     */
    private $config;

    /**
     * @var UsageRepository
     */
    private $usageRepository;

    public function __construct(Repository $config, UsageRepository $usageRepository)
    {
        $this->config = $config;
        $this->usageRepository = $usageRepository;
    }

    /**
     * @param Page $page
     * @param Shortcode[] $shortcodes
     *
     * @return void
     */
    public function track(Page $page, array $shortcodes)
    {
        // If there are no shortcodes, nothing needs to be tracked
        if (count($shortcodes) === 0) {
            return;
        }

        // Only track usage if a setting allows us to
        if (! (bool) $this->config->get('shortcodes.track_usage', false)) {
            return;
        }

        try {
            // Insert / update usage of each shortcode
            foreach ($shortcodes as $shortcode) {
                $this->usageRepository->createOrUpdate(
                    $page->getCollectionID(),
                    $shortcode
                );
            }

            $this->usageRepository->flush();
        } catch (Exception $e) {
            // Silently fail
        }
    }
}
