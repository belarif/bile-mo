<?php

namespace App\Entity\DTO;

use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @OA\Schema()
 */
class MemoryDTO
{
    /**
     * @OA\Property(type="integer")
     */
    public int $id;

    /**
     * @Assert\NotBlank(message="Le champs capacité mémoire ne peut pas être vide")
     * @Assert\Length(max=10, maxMessage="La capacité mémoire ne doit pas dépasser {{ limit }} caractères")
     *
     * @OA\Property(type="string")
     */
    public string $memoryCapacity;
}
