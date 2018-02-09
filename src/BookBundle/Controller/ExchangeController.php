<?php

namespace BookBundle\Controller;

use BookBundle\Entity\Book;
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
use JMS\Serializer\Exception\Exception as JmsException;

/**
 * Exchange controller.
 *
 * @Route("/api/v1/books")
 */
class ExchangeController extends Controller
{
    protected $invalidApiKey = false;

    public function setContainer(ContainerInterface $container = null)
    {
        parent::setContainer($container);

        $apiKey = $this->container->getParameter('api_key');
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $reqApiKey = $request->request->get('apiKey');

        if (empty($reqApiKey) || $reqApiKey != $apiKey) {
            $this->invalidApiKey = $this->invalidResponse('Invalid apiKey');
        }
    }

    /**
     * Get lists all book.
     *
     * @Route("/")
     *
     * @return Response
     */
    public function listAction()
    {
        if ($this->invalidApiKey) {
            return $this->invalidApiKey;
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
     * @param Request $request
     * @return Response
     */
    public function addAction(Request $request)
    {
        if ($this->invalidApiKey) {
            return $this->invalidApiKey;
        }

        $bookRequest = $request->request->get('book');
        $serializer = $this->container->get('jms_serializer');

        try {
            $bookCreate = $serializer->deserialize(
                $bookRequest,
                Book::class,
                'json',
                DeserializationContext::create()->setGroups(array('edit'))
            );
        } catch (JmsException $ex) {
            return $this->invalidResponse($ex->getMessage());
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($bookCreate);
        $em->flush();

        return $this->sucessfullResponse($bookCreate->getId());
    }

    /**
     * Edit book.
     *
     * @Route("/{id}/edit")
     *
     * @param Request $request
     * @return Response
     */
    public function editAction(Request $request, Book $book)
    {
        if ($this->invalidApiKey) {
            return $this->invalidApiKey;
        }

        $bookRequest = $request->request->get('book');
        $serializer = $this->container->get('jms_serializer');

        try {
            $bookEdit = $serializer->deserialize(
                $bookRequest,
                Book::class,
                'json',
                DeserializationContext::create()->setGroups(array('edit'))
            );
        } catch (JmsException $ex) {
            return $this->invalidResponse($ex->getMessage());
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

        return $this->sucessfullResponse($book->getId());
    }

    /**
     * Get invalid response.
     *
     * @param type $message
     * @return JsonResponse
     */
    protected function invalidResponse($message = "Unknown error")
    {
        return new JsonResponse([
            'success' => false,
            'errorMsg' => $message
        ]);
    }

    /**
     * Get successfull response.
     *
     * @param type $result
     * @return Response
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
        } catch (JmsException $ex) {
            return $this->invalidResponse($ex->getMessage());
        }

        return new Response($requestModel);
    }
}
