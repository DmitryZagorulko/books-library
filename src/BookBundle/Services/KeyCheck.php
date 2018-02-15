<?php

namespace BookBundle\Services;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class KeyCheck
 * @package BookBundle\Services
 */
class KeyCheck
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var bool
     */
    protected $invalidApiKey = false;

    /**
     * KeyCheck constructor.
     * @param ContainerInterface $container
     * @param RequestStack $requestStack
     */
    public function __construct(ContainerInterface $container, RequestStack $requestStack)
    {
        $this->container = $container;
        $this->requestStack = $requestStack;
    }

    /**
     * @return bool|JsonResponse
     */
    public function checkKey()
    {
        $apiKey = $this->container->getParameter('api_key');
        $request = $this->requestStack->getCurrentRequest();
        $reqApiKey = $request->get('apiKey');

        if (empty($reqApiKey) || $reqApiKey != $apiKey) {
            $this->invalidApiKey = $this->invalidResponse('Invalid apiKey');
        }

        return $this->invalidApiKey;
    }

    /**
     * Get invalid response.
     *
     * @param string $message
     * @return array
     */
    public function invalidResponse($message = "Unknown error")
    {
        return [
            'success' => false,
            'errorMsg' => $message
        ];
    }
}