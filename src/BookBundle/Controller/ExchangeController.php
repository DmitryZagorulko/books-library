<?php

namespace BookBundle\Controller;

use BookBundle\Entity\Book;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

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

        $bookCreate = $serializer->deserialize(
            $bookRequest,
            Book::class,
            'json'
        );

        $em = $this->getDoctrine()->getManager();
        $em->persist($bookCreate);
        $em->flush();

        return new JsonResponse($bookRequest);
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

        $bookCreate = $serializer->deserialize(
            $bookRequest,
            Book::class,
            'json'
        );

        if (!empty($bookCreate->getName())) {
            $book->setName($bookCreate->getName());
        }

        if (!empty($bookCreate->getAuthor())) {
            $book->setAuthor($bookCreate->getAuthor());
        }

        if ($bookCreate->getReadIt()) {
            $book->setReadIt($bookCreate->getReadIt());
        }

        if ($bookCreate->getAllowDownload()) {
            $book->setAllowDownload($bookCreate->getAllowDownload());
        }

        $this->getDoctrine()->getManager()->flush();

        return new Response($book->getId());
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

        $serializer = $this->container->get('jms_serializer');
        $requestModel = $serializer->serialize($response, 'json');

        return new Response($requestModel);
    }
}
