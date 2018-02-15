<?php

namespace BookBundle\Controller;

use BookBundle\Entity\Book;
use BookBundle\Services\KeyCheck;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use JMS\Serializer\Expression\ExpressionEvaluator;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\DeserializationContext;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Validation;

/**
 * Api controller.
 *
 * @Route("/api/v1/books")
 */
class ApiController extends Controller
{
    protected $checkApi;

    /**
     * @param \BookBundle\Services\KeyCheck $check
     */
    public function __construct(KeyCheck $check)
    {
        $this->checkApi = $check;
    }

    /**
     * Get lists all book.
     *
     * @Route("")
     *
     * @Method("GET")
     * @return Response
     */
    public function listAction()
    {
        if ($this->checkApi->checkKey()) {
            return new JsonResponse($this->checkApi->checkKey());
        }

        $em = $this->getDoctrine()->getManager();
        $books = $em->getRepository('BookBundle:Book')->findBy([], ['readIt' => 'DESC']);

        return $this->sucessfullResponse($books);
    }

    /**
     * Add book.
     *
     * @Route("/add")
     *
     * @Method("POST")
     * @param Request $request
     * @return Response
     */
    public function addAction(Request $request)
    {
        if ($this->checkApi->checkKey()) {
            return new JsonResponse($this->checkApi->checkKey());
        }

        $bookRequest = $request->request->get('book');
        $serializer = $this->container->get('jms_serializer');

        try {
            $bookCreate = $serializer->deserialize(
                $bookRequest,
                Book::class,
                'json',
                DeserializationContext::create()->setGroups(['edit'])
            );
        } catch (\Throwable $ex) {
            return new JsonResponse($this->checkApi->invalidResponse($ex->getMessage()));
        }

        $validator = Validation::createValidator();
        $metadata = $validator->getMetadataFor(Book::class);
        $metadata->addGetterConstraint('name', new NotBlank(), new Type("string"));
        $metadata->addGetterConstraint('author', new NotBlank(), new Type("string"));
        $metadata->addGetterConstraint('readIt', new NotBlank(), new Date());
        $metadata->addGetterConstraint('allowDownload', new NotBlank(), new Type("bool"));
        $violations = $validator->validate($bookCreate);

        if (0 !== count($violations)) {
            $arViolations = [];

            foreach ($violations as $violation) {
                $arViolations[] = $violation->getPropertyPath() . ' : ' . $violation->getMessage();
            }

            return new JsonResponse($this->checkApi->invalidResponse($arViolations));
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($bookCreate);
        $em->flush();

        return $this->sucessfullResponse(["id" => $bookCreate->getId()]);
    }

    /**
     * Edit book.
     *
     * @Route("/{id}/edit")
     *
     * @Method("POST")
     * @param Request $request
     * @param Book $book
     * @return JsonResponse|Response
     */
    public function editAction(Request $request, Book $book)
    {
        if ($this->checkApi->checkKey()) {
            return new JsonResponse($this->checkApi->checkKey());
        }

        $bookRequest = $request->request->get('book');
        $serializer = $this->container->get('jms_serializer');

        try {
            $bookEdit = $serializer->deserialize(
                $bookRequest,
                Book::class,
                'json',
                DeserializationContext::create()->setGroups(['edit'])
            );
        } catch (\Throwable $ex) {
            return new JsonResponse($this->checkApi->invalidResponse($ex->getMessage()));
        }

        if (!empty($bookEdit->getName())) {
            $book->setName($bookEdit->getName());
        }

        if (!empty($bookEdit->getAuthor())) {
            $book->setAuthor($bookEdit->getAuthor());
        }

        if (!empty($bookEdit->getReadIt())) {
            $book->setReadIt($bookEdit->getReadIt());
        }

        if (!empty($bookEdit->getAllowDownload())) {
            $book->setAllowDownload($bookEdit->getAllowDownload());
        }

        $this->getDoctrine()->getManager()->flush();

        return $this->sucessfullResponse(["id" => $book->getId()]);
    }

    /**
     * Get successfull response.
     *
     * @param $result
     * @return JsonResponse|Response
     */
    protected function sucessfullResponse($result)
    {
        $response = [
            'success' => true,
            'response' => $result
        ];

        $serializer = SerializerBuilder::create()
            ->setExpressionEvaluator(new ExpressionEvaluator(new ExpressionLanguage()))
            ->build();

        try {
            $requestModel = $serializer->serialize($response, 'json');
        } catch (\Throwable $ex) {
            return new JsonResponse($this->checkApi->invalidResponse($ex->getMessage()));
        }

        return new Response($requestModel);
    }
}
