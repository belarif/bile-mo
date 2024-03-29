<?php

namespace App\Controller;

use App\Entity\DTO\UserDTO;
use App\Exception\CustomerException;
use App\Exception\UserException;
use App\Repository\CustomerRepository;
use App\Repository\UserRepository;
use App\Service\UserManagement;
use Exception;
use Hateoas\HateoasBuilder;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/customers/{customer_id}/visitors", name="api_", requirements={"customer_id"="\d+"})
 *
 * @IsGranted("ROLE_CUSTOMER")
 */
class VisitorController extends AbstractController
{
    /**
     * @Route("", name="create_visitor", methods={"POST"})
     *
     * @OA\Post(
     *     path="/customers/{customer_id}/visitors",
     *     summary="Create a new visitor",
     *     tags={"Visitors management"},
     *     @OA\Parameter(
     *         name="customer_id",
     *         in="path",
     *         description="customer ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="email",
     *                 type="string"
     *             ),
     *             @OA\Property(
     *                 property="password",
     *                 type="string"
     *             ),
     *             @OA\Property(
     *                 property="roles",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(
     *                         property="id",
     *                         type="integer",
     *                      ),
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="201",
     *         description="HTTP_CREATED",
     *         @OA\JsonContent(
     *             type="string",
     *             description="Created"
     *         )
     *     ),
     *     @OA\Response(
     *         response="409",
     *         description="HTTP_CONFLICT",
     *         @OA\JsonContent(
     *             type="string",
     *             description="Conflict"
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="HTTP_BAD_REQUEST",
     *         @OA\JsonContent(
     *             type="string",
     *             description="Bad request"
     *         )
     *     )
     * )
     */
    public function create(
        int $customer_id,
        Request $request,
        SerializerInterface $serializer,
        UserManagement $userManagement,
        CustomerRepository $customerRepository,
        ValidatorInterface $validator
    ): JsonResponse {
        try {
            /**
             * @var UserDTO $userDTO
             */
            $userDTO = $serializer->deserialize($request->getContent(), UserDTO::class, 'json');

            $errors = $validator->validate($userDTO);
            if ($errors->count()) {
                return $this->json(
                    [
                        'success' => false,
                        'message' => $errors[0]->getMessage(),
                    ],
                    Response::HTTP_BAD_REQUEST
                );
            }

            return $this->hateoasResponse($userManagement->createUser($userDTO, $customerRepository->getCustomer($customer_id)));
        } catch (Exception $e) {
            return $this->json(
                [
                    'success' => false,
                    'message' => $e->getMessage(),
                ],
                Response::HTTP_CONFLICT
            );
        }
    }

    /**
     * @Route("", name="visitors_list", methods={"GET"})
     *
     * @OA\Get(
     *     path="/customers/{customer_id}/visitors",
     *     summary="Returns list of visitors",
     *     tags={"Visitors management"},
     *     @OA\Parameter(
     *         name="customer_id",
     *         in="path",
     *         description="customer ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="HTTP_OK",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/UserDTO"),
     *             description="Ok"
     *         )
     *     )
     * )
     */
    public function list(int $customer_id, UserManagement $userManagement, CustomerRepository $customerRepository): JsonResponse
    {
        try {
            return $this->hateoasResponse($userManagement->users($customerRepository->getCustomer($customer_id)));
        } catch (Exception $e) {
            return $this->json(
                [
                    'success' => false,
                    'message' => $e->getMessage(),
                ],
                Response::HTTP_NOT_FOUND
            );
        }
    }

    /**
     * @Route("/{visitor_id}", name="show_user", methods={"GET"}, requirements={"visitor_id"="\d+"})
     *
     * @OA\Get(
     *     path="/customers/{customer_id}/visitors/{visitor_id}",
     *     summary="Returns visitor by id",
     *     tags={"Visitors management"},
     *     @OA\Parameter(
     *         name="customer_id",
     *         in="path",
     *         description="customer ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="visitor_id",
     *         in="path",
     *         description="visitor ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="HTTP_OK",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/UserDTO"),
     *             description="Ok"
     *         )
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="HTTP_NOT_FOUND",
     *         @OA\JsonContent(
     *             type="string",
     *             description="Not found"
     *         )
     *     )
     * )
     */
    public function show(int $customer_id, int $visitor_id, CustomerRepository $customerRepository, UserRepository $userRepository): JsonResponse
    {
        try {
            try {
                $customer = $customerRepository->getCustomer($customer_id);
            } catch (CustomerException $e) {
                return $this->json(
                    [
                        'success' => false,
                        'message' => $e->getMessage(),
                    ],
                    Response::HTTP_NOT_FOUND
                );
            }

            return $this->hateoasResponse($this->visitorUser($userRepository, $visitor_id, $customer));
        } catch (UserException $e) {
            return $this->json(
                [
                    'success' => false,
                    'message' => $e->getMessage(),
                ],
                Response::HTTP_NOT_FOUND
            );
        }
    }

    /**
     * @Route("/{visitor_id}", name="update_visitor", methods={"PUT"}, requirements={"visitor_id"="\d+"})
     *
     * @OA\Put(
     *     path="/customers/{customer_id}/visitors/{visitor_id}",
     *     summary="Updates a visitor by id",
     *     tags={"Visitors management"},
     *     @OA\Parameter(
     *         name="customer_id",
     *         in="path",
     *         description="customer ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="visitor_id",
     *         in="path",
     *         description="visitor ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="email",
     *                 type="string"
     *             ),
     *             @OA\Property(
     *                 property="password",
     *                 type="string"
     *             ),
     *             @OA\Property(
     *                 property="roles",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(
     *                         property="id",
     *                         type="integer",
     *                      ),
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="201",
     *         description="HTTP_CREATED",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/UserDTO"),
     *             description="Created"
     *         )
     *     ),
     *     @OA\Response(
     *         response="409",
     *         description="HTTP_CONFLICT",
     *         @OA\JsonContent(
     *             type="string",
     *             description="Conflict"
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="HTTP_BAD_REQUEST",
     *         @OA\JsonContent(
     *             type="string",
     *             description="Bad request"
     *         )
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="HTTP_NOT_FOUND",
     *         @OA\JsonContent(
     *             type="string",
     *             description="Not found"
     *         )
     *     )
     * )
     */
    public function update(
        int $visitor_id,
        int $customer_id,
        Request $request,
        SerializerInterface $serializer,
        UserManagement $userManagement,
        UserRepository $userRepository,
        CustomerRepository $customerRepository
    ): JsonResponse {
        try {
            $userDTO = $serializer->deserialize($request->getContent(), UserDTO::class, 'json');

            $customer = $customerRepository->getCustomer($customer_id);

            return $this->hateoasResponse($userManagement->updateUser($userDTO, $this->visitorUser($userRepository, $visitor_id, $customer), $customer));
        } catch (UserException $e) {
            return $this->json(
                [
                    'success' => false,
                    'message' => $e->getMessage(),
                ],
                Response::HTTP_NOT_FOUND
            );
        } catch (Exception $e) {
            return $this->json(
                [
                    'success' => false,
                    'message' => $e->getMessage(),
                ],
                Response::HTTP_CONFLICT
            );
        }
    }

    /**
     * @Route("/{visitor_id}", name="delete_user", methods={"DELETE"}, requirements={"visitor_id"="\d+"})
     *
     * @OA\Delete(
     *     path="/customers/{customer_id}/visitors/{visitor_id}",
     *     summary="Deletes a visitor by id",
     *     tags={"Visitors management"},
     *     @OA\Parameter(
     *         name="customer_id",
     *         in="path",
     *         description="customer ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="visitor_id",
     *         in="path",
     *         description="visitor ID",
     *         required=true
     *     ),
     *     @OA\Response(
     *         response="204",
     *         description="HTTP_NO_CONTENT",
     *         @OA\JsonContent(
     *             type="string",
     *             description="No content"
     *         )
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="HTTP_NOT_FOUND",
     *         @OA\JsonContent(
     *             type="string",
     *             description="Not found"
     *         )
     *     )
     * )
     *
     * @throws CustomerException
     */
    public function delete(
        int $visitor_id,
        int $customer_id,
        UserRepository $userRepository,
        CustomerRepository $customerRepository,
        UserManagement $userManagement
    ): JsonResponse {
        try {
            $userManagement->deleteUser($this->visitorUser($userRepository, $visitor_id, $customerRepository->getCustomer($customer_id)));

            return $this->json('Le visiteur a été supprimé avec succès', Response::HTTP_NO_CONTENT);
        } catch (UserException $e) {
            return $this->json(
                [
                    'success' => false,
                    'message' => $e->getMessage(),
                ],
                Response::HTTP_NOT_FOUND
            );
        }
    }

    private function hateoasResponse($data): JsonResponse
    {
        $hateoas = HateoasBuilder::create()->build();

        return new JsonResponse($hateoas->serialize($data, 'json'), Response::HTTP_OK, [], 'json');
    }

    private function visitorUser($userRepository, $visitor_id, $customer)
    {
        return $userRepository->getVisitorOfCustomer($visitor_id, $customer);
    }
}
