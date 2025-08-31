<?php
namespace App\Entity;

use App\Enum\SessionType;
use App\Repository\GradeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GradeRepository::class)]
class Grade
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'grades')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Course $course = null;

    #[ORM\Column(length: 180)]
    private string $label;

    #[ORM\Column(enumType: SessionType::class)]
    private SessionType $sessionType;

    // Note sur 20 (adapter si besoin)
    #[ORM\Column(type: 'float')]
    private float $score;

    // Coefficient (1 par défaut)
    #[ORM\Column(type: 'float')]
    private float $weight = 1.0;

    #[ORM\Column]
    private \DateTimeImmutable $gradedAt;

    public function getId(): ?int { return $this->id; }

    public function getCourse(): ?Course { return $this->course; }
    public function setCourse(?Course $course): self { $this->course = $course; return $this; }

    public function getLabel(): string { return $this->label; }
    public function setLabel(string $label): self { $this->label = $label; return $this; }

    public function getSessionType(): SessionType { return $this->sessionType; }
    public function setSessionType(SessionType $sessionType): self { $this->sessionType = $sessionType; return $this; }

    public function getScore(): float { return $this->score; }
    public function setScore(float $score): self { $this->score = $score; return $this; }

    public function getWeight(): float { return $this->weight; }
    public function setWeight(float $weight): self { $this->weight = $weight; return $this; }

    public function getGradedAt(): \DateTimeImmutable { return $this->gradedAt; }
    public function setGradedAt(\DateTimeImmutable $gradedAt): self { $this->gradedAt = $gradedAt; return $this; }
}
