<?php

namespace App\Entity;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class HabitLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Habit::class, inversedBy: 'logs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Habit $habit = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private DateTimeImmutable $date;

    #[ORM\Column]
    private bool $completed = false;

    #[ORM\Column(nullable: true)]
    private ?int $value = null;

    public function getId(): ?int { return $this->id; }

    public function getHabit(): ?Habit { return $this->habit; }
    public function setHabit(?Habit $habit): self { $this->habit = $habit; return $this; }

    public function getDate(): DateTimeImmutable { return $this->date; }
    public function setDate(DateTimeImmutable $date): self { $this->date = $date; return $this; }

    public function isCompleted(): bool { return $this->completed; }
    public function setCompleted(bool $completed): self { $this->completed = $completed; return $this; }

    public function getValue(): ?int { return $this->value; }
    public function setValue(?int $value): self { $this->value = $value; return $this; }
}
