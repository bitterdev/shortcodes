<?php

namespace Bitter\Shortcodes\Entity;

use Concrete\Core\Support\Facade\Application;
use DateTime;
use Illuminate\Contracts\Container\BindingResolutionException;
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
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected ?int $id = null;

    /**
     * The shortcode, e.g. 'year'. The surrounding characters are not saved in this column.
     *
     * @ORM\Column(type="string", nullable=false, unique=true)
     */
    protected string $shortcode = '';

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
    protected string $resolveStrategy = '';

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    protected bool $isActive = true;

    /**
     * The replacement value
     *
     * If the strategy is 'string', this contains a fixed value, e.g. '2018'.
     * If the strategy is 'php', this can contain PHP code.
     * If the strategy is 'event', this remains null.
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected ?string $value = '';

    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    protected ?DateTime $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    protected ?DateTime $updatedAt;

    public function __construct()
    {
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
    }

    public function exists(): bool
    {
        return $this->getId() !== null;
    }


    public function getDisplayShortcode(): string
    {
        return '[[' . $this->getShortcode() . ']]';
    }

    /**
     * Returns true if the shortcode should be resolved via text
     *
     * @return bool
     */
    public function shouldResolveByString(): bool
    {
        return $this->getResolveStrategy() === self::RESOLVE_BY_STRING;
    }

    /**
     * Returns true if the shortcode should be resolved via PHP
     *
     * @return bool
     */
    public function shouldResolveByPhp(): bool
    {
        return $this->getResolveStrategy() === self::RESOLVE_BY_PHP;
    }

    /**
     * Returns true if the shortcode should be resolved via events
     *
     * @return bool
     */
    public function shouldResolveByEvent(): bool
    {
        return $this->getResolveStrategy() === self::RESOLVE_BY_EVENT;
    }


    /**
     * @param int $maxLength
     *
     * @return string|null
     * @throws BindingResolutionException
     */
    public function getDisplayValue(int $maxLength = 40): ?string
    {
        if ($this->getResolveStrategy() === self::RESOLVE_BY_PHP) {
            return t('Depends on PHP');
        }

        if ($this->getResolveStrategy() === self::RESOLVE_BY_EVENT) {
            return t('Depends on event');
        }

        $app = Application::getFacadeApplication();

        /** @noinspection PhpUnhandledExceptionInspection */
        return $app->make('helper/text')
            ->shorten($this->getValue(), $maxLength);
    }

    public function touch(): void
    {
        $this->updatedAt = new DateTime();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return Shortcode
     */
    public function setId(?int $id): Shortcode
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getShortcode(): string
    {
        return $this->shortcode;
    }

    /**
     * @param string $shortcode
     * @return Shortcode
     */
    public function setShortcode(string $shortcode): Shortcode
    {
        $this->shortcode = $shortcode;
        return $this;
    }

    /**
     * @return string
     */
    public function getResolveStrategy(): string
    {
        return $this->resolveStrategy;
    }

    /**
     * @param string $resolveStrategy
     * @return Shortcode
     */
    public function setResolveStrategy(string $resolveStrategy): Shortcode
    {
        $this->resolveStrategy = $resolveStrategy;
        return $this;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->isActive;
    }

    /**
     * @param bool $isActive
     * @return Shortcode
     */
    public function setIsActive(bool $isActive): Shortcode
    {
        $this->isActive = $isActive;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getValue(): ?string
    {
        return $this->value;
    }

    /**
     * @param string|null $value
     * @return Shortcode
     */
    public function setValue(?string $value): Shortcode
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param DateTime|null $createdAt
     * @return Shortcode
     */
    public function setCreatedAt(?DateTime $createdAt): Shortcode
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    /**
     * @param DateTime|null $updatedAt
     * @return Shortcode
     */
    public function setUpdatedAt(?DateTime $updatedAt): Shortcode
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

}
