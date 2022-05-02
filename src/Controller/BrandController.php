<?php

namespace App\Controller;

use App\Entity\Brand;
use App\Entity\DTO\BrandDTO;
use App\Service\BrandManagement;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;

/**
 * @Route("/brands", "api_")
 */
class BrandController extends AbstractController
{
    /**
     * @Route("", name="create_brand", methods={"POST"})
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function create(Request $request, SerializerInterface $serializer, BrandManagement $brandManagement): JsonResponse
    {
        $brandDTO = $serializer->deserialize($request->getContent(),BrandDTO::class,'json');

        $brandManagement->createBrand($brandDTO);

        return $this->json('La marque a été ajouté avec succès',200,['Content-Type' => 'text/plain']);
    }

    /**
     * @Route("", name="brands_list", methods={"GET"})
     */
    public function list(BrandManagement $brandManagement): JsonResponse
    {
        return $this->json($brandManagement->brandsList(),200,['Content-Type' => 'application/json']);
    }

    /**
     * @Route("/{id}", name="show_brand", methods={"GET"}, requirements={"id"="\d+"})
     */
    public function show(Brand $brand): JsonResponse
    {
        return $this->json($brand,'200',['Content-Type' => 'application/json']);
    }

    /**
     * @Route("/{id}", name="update_brand", methods={"PUT"}, requirements={"id"="\d+"})
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function update(Request $request, Brand $brand, BrandManagement $brandManagement, SerializerInterface $serializer): JsonResponse
    {
        $brandDTO = $serializer->deserialize($request->getContent(),BrandDTO::class,'json');
        $brandManagement->updateBrand($brand,$brandDTO);

        return $this->json('La marque a été modifié avec succès',200,['Content-Type' => 'text/plain']);
    }

    /**
     * @Route("/{id}", name="delete_brand", methods={"DELETE"}, requirements={"id"="\d+"})
     * @Entity("brand", expr="repository.getBrand(id)")
     * @param Brand $brand
     * @param BrandManagement $brandManagement
     * @return JsonResponse
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function delete(Brand $brand, BrandManagement $brandManagement): JsonResponse
    {
        $brandManagement->deleteBrand($brand);

        return $this->json('La marque a été supprimé avec succès',200,['Content-Type' => 'text/plain']);
    }

}