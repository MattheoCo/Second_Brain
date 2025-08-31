<?php
namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\User;

#[ORM\Entity]
class Course
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private string $name;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $code = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $ects = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    /** @var Collection<int, Grade> */
    #[ORM\OneToMany(mappedBy: 'course', targetEntity: Grade::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $grades;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: true, onDelete: 'CASCADE')]
    private ?User $user = null;


    public function __construct()
    {
        $this->grades = new ArrayCollection();
        $this->files = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getName(): string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }

    public function getCode(): ?string { return $this->code; }
    public function setCode(?string $code): self { $this->code = $code; return $this; }

    public function getEcts(): ?float { return $this->ects; }
    public function setEcts(?float $ects): self { $this->ects = $ects; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): self { $this->description = $description; return $this; }

    /** @return Collection<int, Grade> */
    public function getGrades(): Collection { return $this->grades; }

    public function addGrade(Grade $grade): self
    {
        if (!$this->grades->contains($grade)) {
            $this->grades->add($grade);
            $grade->setCourse($this);
        }
        return $this;
    }

    public function removeGrade(Grade $grade): self
    {
        if ($this->grades->removeElement($grade) && $grade->getCourse() === $this) {
            $grade->setCourse(null);
        }
        return $this;
    }

    public function getAverageScore(): ?float
    {
        if ($this->grades->isEmpty()) { return null; }
        $sum = 0.0; $weights = 0.0;
        foreach ($this->grades as $g) {
            $w = max(0.0, (float)$g->getWeight()) ?: 1.0;
            $sum += ((float)$g->getScore()) * $w;
            $weights += $w;
        }
        return $weights > 0 ? round($sum / $weights, 2) : null;
    }

    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $user): self { $this->user = $user; return $this; }

}
