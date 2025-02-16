<?php

namespace App\Entity;

use App\Repository\TaskRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TaskRepository::class)]
class Task
{
    #[ORM\ManyToOne(targetEntity: Client::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Client $user = null;
    public const STATUS_TODO = 'todo';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_DONE = 'done';

    public const STATUSES = [
        self::STATUS_TODO => 'À faire',
        self::STATUS_IN_PROGRESS => 'En cours',
        self::STATUS_DONE => 'Terminé',
    ];

    public const PRIORITY_1 = 'p1';
    public const PRIORITY_2 = 'p2';
    public const PRIORITY_3 = 'p3';

    public const PRIORITIES = [
        self::PRIORITY_1 => 'Priorité 1',
        self::PRIORITY_2 => 'Priorité 2',
        self::PRIORITY_3 => 'Priorité 3',
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(length: 20)]
    private ?string $status = self::STATUS_TODO;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $priority = null;

    #[ORM\Column]
    private ?float $progress = 0;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dueDate = null;

    #[ORM\Column]
    private ?bool $isDefault = false;

    #[ORM\ManyToOne(inversedBy: 'tasks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Channel $channel = null;

    /**
     * @var Collection<int, Subtask>
     */
    #[ORM\OneToMany(mappedBy: 'task', targetEntity: Subtask::class, orphanRemoval: true)]
    private Collection $subtasks;

    public function __construct()
    {
        $this->subtasks = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getPriority(): ?string
    {
        return $this->priority;
    }

    public function setPriority(?string $priority): static
    {
        if ($priority === '') {
            $priority = null;
        }
        if ($priority !== null && !isset(self::PRIORITIES[$priority])) {
            throw new \InvalidArgumentException(sprintf('Invalid priority "%s"', $priority));
        }
        $this->priority = $priority;
        return $this;
    }

    public function getProgress(): ?float
    {
        return $this->progress;
    }

    public function setProgress(float $progress): static
    {
        $this->progress = $progress;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getDueDate(): ?\DateTimeInterface
    {
        return $this->dueDate;
    }

    public function setDueDate(?\DateTimeInterface $dueDate): static
    {
        $this->dueDate = $dueDate;
        return $this;
    }

    public function getChannel(): ?Channel
    {
        return $this->channel;
    }

    public function setChannel(?Channel $channel): static
    {
        $this->channel = $channel;
        return $this;
    }

    public function isDefault(): ?bool
    {
        return $this->isDefault;
    }

    public function setIsDefault(bool $isDefault): static
    {
        $this->isDefault = $isDefault;
        return $this;
    }

    /**
     * @return Collection<int, Subtask>
     */
    public function getSubtasks(): Collection
    {
        return $this->subtasks;
    }

    public function addSubtask(Subtask $subtask): static
    {
        if (!$this->subtasks->contains($subtask)) {
            $this->subtasks->add($subtask);
            $subtask->setTask($this);
        }
        return $this;
    }

    public function removeSubtask(Subtask $subtask): static
    {
        if ($this->subtasks->removeElement($subtask)) {
            if ($subtask->getTask() === $this) {
                $subtask->setTask(null);
            }
        }
        return $this;
    }

    public function getUser(): ?Client
    {
        return $this->user;
    }

    public function setUser(?Client $user): static
    {
        $this->user = $user;
        return $this;
    }
}
