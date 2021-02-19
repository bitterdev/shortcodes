<?php

namespace A3020\Shortcodes\Entity;

use DateTimeImmutable;
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
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Shortcode")
     * @ORM\JoinColumn(name="shortcodeId", referencedColumnName="id")
     **/
    protected $shortcode;

    /**
     * @ORM\Column(type="integer", options={"unsigned":true}, nullable=false)
     */
    protected $pageId;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    protected $activeAt;

    public function __construct()
    {
        $this->activeAt = new DateTimeImmutable();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Shortcode
     */
    public function getShortcode()
    {
        return $this->shortcode;
    }

    /**
     * @param Shortcode $shortcode
     */
    public function setShortcode(Shortcode $shortcode)
    {
        $this->shortcode = $shortcode;
    }

    /**
     * @return int
     */
    public function getPageId()
    {
        return $this->pageId;
    }

    /**
     * @param int $pageId
     */
    public function setPageId($pageId)
    {
        $this->pageId = $pageId;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getActiveAt()
    {
        return $this->activeAt;
    }

    /**
     * @param DateTimeImmutable $activeAt
     */
    public function setActiveAt($activeAt)
    {
        $this->activeAt = $activeAt;
    }

    public function touch()
    {
        $this->activeAt = new DateTimeImmutable();
    }
}
