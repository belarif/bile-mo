<?php

namespace App\Controller;

use App\Entity\DTO\ProductDTO;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\ProductManagement;
use Symfony\Component\HttpFoundation\Response;


/**
 * @Route("/products", name="api_")
 */
class ProductController extends AbstractController
{
    /**
     * @Route("", name="create_product", methods={"POST"})
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param ProductManagement $productManagement
     * @return JsonResponse
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function create(Request $request, SerializerInterface $serializer, ProductManagement $productManagement):JsonResponse
    {
        /**
         * @var ProductDTO $productDTO
         */
        $productDTO = $serializer->deserialize($request->getContent(), ProductDTO::class, 'json');
        $productManagement->createProduct($productDTO);

        return new JsonResponse('le produit a été créé avec succès','201');
    }

    /**
     * @Route("/{product_id}", name="show_product", methods={"GET"})
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param ProductManagement $productManagement
     * @return Response
     */
    public function show(Request $request, SerializerInterface $serializer, ProductManagement $productManagement): Response
    {
        $product = $productManagement->showProduct($request->get('product_id'));

        $response = new Response($serializer->serialize($product,'json'));
        $response->headers->set('content-type','application/json');

        return $response;

    }
}

