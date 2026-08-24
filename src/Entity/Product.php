<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'La référence est obligatoire.')]
    #[Assert\Length(
        min: 2,
        max: 50,
        minMessage: 'La référence doit faire au moins {{ limit }} caractères',
        maxMessage: 'La référence ne peut pas faire plus de {{ limit }} caractères',
    )]
    private ?string $reference = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'La designation est obligatoire.')]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'La designation doit faire au moins {{ limit }} caractères',
        maxMessage: 'La designation ne peut pas faire plus de {{ limit }} caractères',
    )]
    private ?string $designation = null;

    #[ORM\Column(type: 'json')]
    private array $images = [];

    #[ORM\Column(length: 255)]
    #[Assert\Length(
        max: 255,
        maxMessage: 'La description ne peut pas faire plus de {{ limit }} caractères',
    )]
    private ?string $description = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): static
    {
        $this->reference = $reference;

        return $this;
    }

    public function getDesignation(): ?string
    {
        return $this->designation;
    }

    public function setDesignation(string $designation): static
    {
        $this->designation = $designation;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getImages(): array
    {
        return $this->images;
    }

    /**
     * @param string[] $images
     */
    public function setImages(array $images): static
    {
        $this->images = $images;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }
}
