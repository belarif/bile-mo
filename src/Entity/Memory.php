<?php

namespace App\Entity;

use App\Repository\MemoryRepository;
use Doctrine\ORM\Mapping as ORM;
use Hateoas\Configuration\Annotation as Hateoas;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=MemoryRepository::class)
 * @UniqueEntity("memoryCapacity")
 *
 * @Hateoas\Relation(
 *     "self",
 *     href = "expr('/bile-mo-api/v1/memories/' ~ object.getId())"
 * )
 * @Hateoas\Relation(
 *     "create",
 *     href = "expr('/bile-mo-api/v1/memories')"
 * )
 * @Hateoas\Relation(
 *     "list",
 *     href = "expr('/bile-mo-api/v1/memories')"
 * )
 * @Hateoas\Relation(
 *     "update",
 *     href = "expr('/bile-mo-api/v1/memories/' ~ object.getId())"
 * )
 * @Hateoas\Relation(
 *     "delete",
 *     href = "expr('/bile-mo-api/v1/memories/' ~ object.getId())"
 * )
 */
class Memory
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected int $id;

    /**
     * @ORM\Column(type="string", length=10, unique=true)
     */
    private string $memoryCapacity;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMemoryCapacity(): ?string
    {
        return $this->memoryCapacity;
    }

    public function setMemoryCapacity(string $memoryCapacity): self
    {
        $this->memoryCapacity = $memoryCapacity;

        return $this;
    }
}
