<?php

namespace App\Entity;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class ShoppingItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $quantity = null;

    #[ORM\Column]
    private bool $checked = false;

    #[ORM\Column(length: 20)]
    private string $source;

    #[ORM\ManyToOne(targetEntity: Recipe::class)]
    private ?Recipe $recipe = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getName(): string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }

    public function getQuantity(): ?string { return $this->quantity; }
    public function setQuantity(?string $quantity): self { $this->quantity = $quantity; return $this; }

    public function isChecked(): bool { return $this->checked; }
    public function setChecked(bool $checked): self { $this->checked = $checked; return $this; }

    public function getSource(): string { return $this->source; }
    public function setSource(string $source): self { $this->source = $source; return $this; }

    public function getRecipe(): ?Recipe { return $this->recipe; }
    public function setRecipe(?Recipe $recipe): self { $this->recipe = $recipe; return $this; }

    public function getCreatedAt(): DateTimeImmutable { return $this->createdAt; }
    public function setCreatedAt(DateTimeImmutable $createdAt): self { $this->createdAt = $createdAt; return $this; }
}
