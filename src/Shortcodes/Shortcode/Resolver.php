<?php

namespace A3020\Shortcodes\Shortcode;

use A3020\Shortcodes\Entity\Shortcode;
use A3020\Shortcodes\Event\Resolve;
use Concrete\Core\Config\Repository\Repository;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Throwable;

class Resolver
{
    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var Repository
     */
    private $config;

    public function __construct(EventDispatcher $eventDispatcher, LoggerInterface $logger, Repository $config)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->logger = $logger;
        $this->config = $config;
    }

    /**
     * @param Shortcode $shortcode
     *
     * @return string|null
     */
    public function resolve(Shortcode $shortcode)
    {
        if ($shortcode->getResolveStrategy() === Shortcode::RESOLVE_BY_STRING) {
            return $shortcode->getValue();
        }

        if ($shortcode->getResolveStrategy() === Shortcode::RESOLVE_BY_PHP) {
            return $this->evaluatePhp($shortcode->getValue());
        }
        
        return $this->triggerEvent($shortcode);
    }

    /**
     * Fire an event and return the replacement
     *
     * If no other add-ons hook into this event,
     * it will simply return null.
     *
     * @param Shortcode
     *
     * @return string|null
     */
    private function triggerEvent($shortcodeEntity)
    {
        $event = new Resolve();
        $event->setShortcode($shortcodeEntity);

        $this->eventDispatcher->dispatch('on_shortcode_resolve', $event);

        return $event->getReplacement();
    }

    /**
     * @param string $php
     *
     * @return string|null
     */
    private function evaluatePhp($php)
    {
        ob_start();

        try {
            eval($php);
        } catch (Throwable $e) {
            $this->logger->debug(
                t('PHP shortcode error: %s.', $e->getMessage())
            );
        }

        return ob_get_clean();
    }
}
