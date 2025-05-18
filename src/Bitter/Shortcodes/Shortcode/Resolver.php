<?php

namespace Bitter\Shortcodes\Shortcode;

use Bitter\Shortcodes\Entity\Shortcode;
use Bitter\Shortcodes\Event\Resolve;
use Concrete\Core\Config\Repository\Repository;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Throwable;

class Resolver
{
    protected EventDispatcherInterface $eventDispatcher;
    protected LoggerInterface $logger;
    protected Repository $config;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        LoggerInterface          $logger,
        Repository               $config
    )
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
    public function resolve(Shortcode $shortcode): ?string
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
     * @param Shortcode $shortcodeEntity
     *
     * @return string|null
     */
    private function triggerEvent(Shortcode $shortcodeEntity): ?string
    {
        $event = new Resolve();
        $event->setShortcode($shortcodeEntity);

        $this->eventDispatcher->dispatch($event, 'on_shortcode_resolve');

        return $event->getReplacement();
    }

    /**
     * @param string $php
     *
     * @return string|null
     */
    private function evaluatePhp(string $php): ?string
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
