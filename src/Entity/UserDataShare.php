<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'user_data_share', uniqueConstraints: [
    new ORM\UniqueConstraint(name: 'uniq_share_owner_target_ns', columns: ['owner_id', 'target_id', 'namespace'])
])]
class UserDataShare
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_DECLINED = 'declined';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'owner_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?User $owner = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'target_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?User $target = null;

    #[ORM\Column(length: 32)]
    private string $namespace;

    #[ORM\Column(length: 16)]
    private string $status = self::STATUS_PENDING;

    #[ORM\Column(type: 'boolean')]
    private bool $canWrite = false;

    public function getId(): ?int { return $this->id; }
    public function getOwner(): ?User { return $this->owner; }
    public function setOwner(?User $u): self { $this->owner = $u; return $this; }
    public function getTarget(): ?User { return $this->target; }
    public function setTarget(?User $u): self { $this->target = $u; return $this; }
    public function getNamespace(): string { return $this->namespace; }
    public function setNamespace(string $ns): self { $this->namespace = strtolower($ns); return $this; }
    public function getStatus(): string { return $this->status; }
    public function setStatus(string $s): self { $this->status = $s; return $this; }
    public function canWrite(): bool { return $this->canWrite; }
    public function setCanWrite(bool $w): self { $this->canWrite = $w; return $this; }
}
