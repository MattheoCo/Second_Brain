<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'user_data', uniqueConstraints: [
    new ORM\UniqueConstraint(name: 'uniq_user_ns', columns: ['user_id', 'namespace'])
])]
class UserData
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\Column(length: 32)]
    private string $namespace;

    #[ORM\Column(type: 'text')]
    private string $data;

    public function __construct(User $user = null, string $namespace = 'default', string $data = '{}')
    {
        if ($user) { $this->user = $user; }
        $this->namespace = $namespace;
        $this->data = $data;
    }

    public function getId(): ?int { return $this->id; }

    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getNamespace(): string { return $this->namespace; }
    public function setNamespace(string $namespace): self { $this->namespace = $namespace; return $this; }

    public function getData(): string { return $this->data; }
    public function setData(string $data): self { $this->data = $data; return $this; }
}
