<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Habit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $title;

    #[ORM\Column(length: 20)]
    private string $periodicity;

    #[ORM\Column(nullable: true)]
    private ?int $goal = null;

    #[ORM\ManyToMany(targetEntity: Tag::class)]
    #[ORM\JoinTable(name: 'habit_tag')]
    private Collection $tags;

    /** @var Collection<int, HabitLog> */
    #[ORM\OneToMany(mappedBy: 'habit', targetEntity: HabitLog::class, orphanRemoval: true, cascade: ['persist'])]
    private Collection $logs;

    public function __construct()
    {
        $this->tags = new ArrayCollection();
        $this->logs = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getTitle(): string { return $this->title; }
    public function setTitle(string $title): self { $this->title = $title; return $this; }

    public function getPeriodicity(): string { return $this->periodicity; }
    public function setPeriodicity(string $periodicity): self { $this->periodicity = $periodicity; return $this; }

    public function getGoal(): ?int { return $this->goal; }
    public function setGoal(?int $goal): self { $this->goal = $goal; return $this; }

    /** @return Collection<int, Tag> */
    public function getTags(): Collection { return $this->tags; }
    public function addTag(Tag $tag): self { if (!$this->tags->contains($tag)) { $this->tags->add($tag); } return $this; }
    public function removeTag(Tag $tag): self { $this->tags->removeElement($tag); return $this; }

    /** @return Collection<int, HabitLog> */
    public function getLogs(): Collection { return $this->logs; }

    public function addLog(HabitLog $log): self
    {
        if (!$this->logs->contains($log)) {
            $this->logs->add($log);
            $log->setHabit($this);
        }
        return $this;
    }

    public function removeLog(HabitLog $log): self
    {
        if ($this->logs->removeElement($log)) {
            if ($log->getHabit() === $this) {
                $log->setHabit(null);
            }
        }
        return $this;
    }
}
