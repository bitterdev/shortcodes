<?php

namespace Bitter\Shortcodes\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(
 *   name="ShortcodesUsage",
 *   uniqueConstraints={
 *      @ORM\UniqueConstraint(name="page_shortcode",
 *          columns={"shortcodeId", "pageId"}
 *      )
 *   },
 *   indexes={
 *     @ORM\Index(name="id", columns={"id"}),
 *   }
 * )
 */
class Usage
{
    /**
     * @ORM\Id @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected ?int $id;

    /**
     * @ORM\ManyToOne(targetEntity="Shortcode")
     * @ORM\JoinColumn(name="shortcodeId", referencedColumnName="id")
     **/
    protected ?Shortcode $shortcode;

    /**
     * @ORM\Column(type="integer", options={"unsigned":true}, nullable=false)
     */
    protected int $pageId;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    protected DateTime $activeAt;

    public function __construct()
    {
        $this->activeAt = new DateTime();
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
     * @return Usage
     */
    public function setId(?int $id): Usage
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return Shortcode|null
     */
    public function getShortcode(): ?Shortcode
    {
        return $this->shortcode;
    }

    /**
     * @param Shortcode|null $shortcode
     * @return Usage
     */
    public function setShortcode(?Shortcode $shortcode): Usage
    {
        $this->shortcode = $shortcode;
        return $this;
    }

    /**
     * @return int
     */
    public function getPageId(): int
    {
        return $this->pageId;
    }

    /**
     * @param int $pageId
     * @return Usage
     */
    public function setPageId(int $pageId): Usage
    {
        $this->pageId = $pageId;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getActiveAt(): DateTime
    {
        return $this->activeAt;
    }

    /**
     * @param DateTime $activeAt
     * @return Usage
     */
    public function setActiveAt(DateTime $activeAt): Usage
    {
        $this->activeAt = $activeAt;
        return $this;
    }

    public function touch(): void
    {
        $this->updatedAt = new DateTime();
    }
}
