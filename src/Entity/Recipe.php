<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
class Recipe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $title;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $instructions = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $attachments = null;

    #[ORM\ManyToMany(targetEntity: Tag::class)]
    #[ORM\JoinTable(name: 'recipe_tag')]
    private Collection $tags;

    /** @var Collection<int, RecipeIngredient> */
    #[ORM\OneToMany(mappedBy: 'recipe', targetEntity: RecipeIngredient::class, orphanRemoval: true, cascade: ['persist'])]
    private Collection $ingredients;

    public function __construct()
    {
        $this->tags = new ArrayCollection();
        $this->ingredients = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getTitle(): string { return $this->title; }
    public function setTitle(string $title): self { $this->title = $title; return $this; }

    public function getInstructions(): ?string { return $this->instructions; }
    public function setInstructions(?string $instructions): self { $this->instructions = $instructions; return $this; }

    public function getAttachments(): ?array { return $this->attachments; }
    public function setAttachments(?array $attachments): self { $this->attachments = $attachments; return $this; }

    /** @return Collection<int, Tag> */
    public function getTags(): Collection { return $this->tags; }
    public function addTag(Tag $tag): self { if (!$this->tags->contains($tag)) { $this->tags->add($tag); } return $this; }
    public function removeTag(Tag $tag): self { $this->tags->removeElement($tag); return $this; }

    /** @return Collection<int, RecipeIngredient> */
    public function getIngredients(): Collection { return $this->ingredients; }

    public function addIngredient(RecipeIngredient $ri): self
    {
        if (!$this->ingredients->contains($ri)) {
            $this->ingredients->add($ri);
            $ri->setRecipe($this);
        }
        return $this;
    }

    public function removeIngredient(RecipeIngredient $ri): self
    {
        if ($this->ingredients->removeElement($ri)) {
            if ($ri->getRecipe() === $this) {
                $ri->setRecipe(null);
            }
        }
        return $this;
    }
}
