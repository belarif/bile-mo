<?php

namespace App\Controller;

use App\Entity\Country;
use App\Entity\DTO\CountryDTO;
use App\Service\CountryManagement;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\SerializerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/countries", name="api_")
 */
class CountryController extends AbstractController
{
    /**
     * @Route("", name="create_country", methods={"POST"})
     */
    public function create(
        Request $request,
        SerializerInterface $serializer,
        CountryManagement $countryManagement,
        ValidatorInterface $validator
    ): JsonResponse
    {
        try {
            $countryDTO = $serializer->deserialize($request->getContent(),CountryDTO::class,'json');

            $errors = $validator->validate($countryDTO);

            if($errors->count()) {
                return $this->json($errors[0]->getMessage(),Response::HTTP_CONFLICT);
            }

            return $this->json($countryManagement->createCountry($countryDTO),Response::HTTP_CREATED);

        } catch (NotEncodableValueException $e) {
            return $this->json([
                'status' => Response::HTTP_BAD_REQUEST,
                'message' => $e->getMessage()],
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    /**
     * @Route("", name="countries_list", methods={"GET"})
     */
    public function list(CountryManagement $countryManagement): JsonResponse
    {
        return $this->json($countryManagement->countriesList(),Response::HTTP_OK);
    }

    /**
     * @Route("/{id}", name="show_country", methods={"GET"}, requirements={"id"="\d+"})
     *
     * @Entity("country", expr="repository.getCountry(id)")
     */
    public function show(Country $country): JsonResponse
    {
        return $this->json($country,Response::HTTP_OK);
    }

    /**
     * @Route("/{id}", name="update_country", methods={"PUT"}, requirements={"id"="\d+"})
     *
     * @Entity("country", expr="repository.getCountry(id)")
     */
    public function update(
        Request $request,
        Country $country,
        CountryManagement $countryManagement,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ): JsonResponse
    {
        try {
            $countryDTO = $serializer->deserialize($request->getContent(),CountryDTO::class,'json');

            $errors = $validator->validate($countryDTO);

            if($errors->count()) {
                return $this->json($errors[0]->getMessage(),Response::HTTP_CONFLICT);
            }

            return $this->json($countryManagement->updateCountry($country,$countryDTO),Response::HTTP_CREATED);
        } catch (NotEncodableValueException $e) {
            return $this->json([
                'status' => Response::HTTP_BAD_REQUEST,
                'message' => $e->getMessage()],
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    /**
     * @Route("/{id}", name="delete_country", methods={"DELETE"}, requirements={"id"="\d+"})
     *
     * @Entity("country", expr="repository.getCountry(id)")
     */
    public function delete(Country $country, CountryManagement $countryManagement): JsonResponse
    {
        $countryManagement->deleteCountry($country);

        return $this->json('La pays a été supprimé avec succès',Response::HTTP_OK);
    }
}


