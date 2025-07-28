<?php /** @noinspection DuplicatedCode */

/** @noinspection PhpUndefinedMethodInspection */

namespace Bitter\Shortcodes\API\V1;

use Bitter\Shortcodes\Entity\Shortcode;
use Bitter\Shortcodes\Shortcode\Replacer;
use Concrete\Core\Application\ApplicationAwareInterface;
use Concrete\Core\Application\ApplicationAwareTrait;
use Concrete\Core\Application\EditResponse;
use Concrete\Core\Http\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Concrete\Core\Http\ResponseFactory;

class Shortcodes implements ApplicationAwareInterface
{
    use ApplicationAwareTrait;

    protected Request $request;
    protected ResponseFactory $responseFactory;
    protected Replacer $replacer;
    protected EntityManagerInterface $entityManager;

    public function __construct(
        Request                $request,
        ResponseFactory        $responseFactory,
        Replacer               $replacer,
        EntityManagerInterface $entityManager
    )
    {
        $this->request = $request;
        $this->responseFactory = $responseFactory;
        $this->replacer = $replacer;
        $this->entityManager = $entityManager;
    }

    public function getAllShortcodes(): Response
    {
        $shortcodes = [];

        foreach ($this->entityManager->getRepository(Shortcode::class)->findAll() as $shortcode) {
            if ($shortcode instanceof Shortcode) {
                $shortcodes[$shortcode->getShortcode()] = $this->replacer->findAndReplace("[[" . $shortcode->getShortcode() . "]]");
            }
        }

        $editResponse = new EditResponse();
        $editResponse->setAdditionalDataAttribute("shortcodes", $shortcodes);

        return $this->responseFactory->json($editResponse);
    }
}
