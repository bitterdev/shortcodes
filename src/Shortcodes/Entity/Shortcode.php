<?php

namespace A3020\Shortcodes\Entity;

use Concrete\Core\Support\Facade\Application;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(
 *   name="ShortcodesEntries",
 *   indexes={
 *     @ORM\Index(name="id", columns={"id"}),
 *   }
 * )
 */
class Shortcode
{
    const RESOLVE_BY_STRING = 'string';
    const RESOLVE_BY_PHP = 'php';
    const RESOLVE_BY_EVENT = 'event';

    /**
     * @ORM\Id @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * The shortcode, e.g. 'year'. The surrounding characters are not saved in this column.
     *
     * @ORM\Column(type="string", nullable=false, unique=true)
     */
    protected $shortcode;

    /**
     * How the shortcode should be resolved
     *
     * Examples:
     * - via string value
     * - via php code
     * - via event
     *
     * @ORM\Column(type="string", nullable=false)
     */
    protected $resolveStrategy;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    protected $isActive = true;

    /**
     * The replacement value
     *
     * If the strategy is 'string', this contains a fixed value, e.g. '2018'.
     * If the strategy is 'php', this can contain PHP code.
     * If the strategy is 'event', this remains null.
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $value;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    protected $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    protected $updatedAt;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function exists()
    {
        return (bool) $this->getId();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return (int) $this->id;
    }

    /**
     * @return string
     */
    public function getShortcode()
    {
        return $this->shortcode;
    }

    public function getDisplayShortcode()
    {
        return '[[' . $this->getShortcode() . ']]';
    }

    /**
     * @param string $shortcode
     */
    public function setShortcode($shortcode)
    {
        $this->shortcode = (string) $shortcode;
    }

    /**
     * @return string
     */
    public function getResolveStrategy()
    {
        return $this->resolveStrategy;
    }

    /**
     * Returns true if the shortcode should be resolved via text
     *
     * @return bool
     */
    public function shouldResolveByString()
    {
        return $this->getResolveStrategy() === self::RESOLVE_BY_STRING;
    }

    /**
     * Returns true if the shortcode should be resolved via PHP
     *
     * @return bool
     */
    public function shouldResolveByPhp()
    {
        return $this->getResolveStrategy() === self::RESOLVE_BY_PHP;
    }

    /**
     * Returns true if the shortcode should be resolved via events
     *
     * @return bool
     */
    public function shouldResolveByEvent()
    {
        return $this->getResolveStrategy() === self::RESOLVE_BY_EVENT;
    }

    /**
     * @param string $resolveStrategy
     */
    public function setResolveStrategy($resolveStrategy)
    {
        $this->resolveStrategy = (string) $resolveStrategy;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param int $maxLength
     *
     * @return string|null
     */
    public function getDisplayValue($maxLength = 40)
    {
        if ($this->getResolveStrategy() === self::RESOLVE_BY_PHP) {
            return t('Depends on PHP');
        }

        if ($this->getResolveStrategy() === self::RESOLVE_BY_EVENT) {
            return t('Depends on event');
        }

        $app = Application::getFacadeApplication();

        return $app->make('helper/text')
            ->shorten($this->getValue(), $maxLength);
    }

    /**
     * @param string|null $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param DateTimeImmutable $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->isActive;
    }

    /**
     * @param bool $isActive
     */
    public function setIsActive($isActive)
    {
        $this->isActive = (bool) $isActive;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param DateTimeImmutable $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    public function touch()
    {
        $this->updatedAt = new DateTimeImmutable();
    }
}
