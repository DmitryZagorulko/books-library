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
    protected $apiKey;

    public function setContainer(ContainerInterface $container = null)
    {
        parent::setContainer($container);

        $this->apiKey = $this->container->getParameter('api_key');
    }

    /**
     * Get lists all book.
     *
     * @Route("/")
     *
     * @param Request $request
     * @return Response
     */
    public function listAction(Request $request)
    {
        $reqApiKey = $request->query->get('apiKey');

        if (empty($reqApiKey) || $reqApiKey != $this->apiKey) {
            return $this->invalidResponse('Invalid apiKey');
        }

        $em = $this->getDoctrine()->getManager();
        $books = $em->getRepository('BookBundle:Book')->findAll();

        return $this->sucessfullResponse($books);
    }

    /**
     * Add book.
     *
     * @Route("/add ")
     *
     * @param Request $request
     * @return Response
     */
    public function addAction(Request $request)
    {
    }

    /**
     * Edit book.
     *
     * @Route("/{id}/edit")
     *
     * @param Request $request
     * @return Response
     */
    public function editAction(Request $request)
    {
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
