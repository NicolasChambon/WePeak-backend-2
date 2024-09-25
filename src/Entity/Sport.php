<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\SportRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: SportRepository::class)]
class Sport
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Groups(['activity.list'])]
    private ?string $name = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * @var Collection<int, Activity>
     */
    #[ORM\OneToMany(targetEntity: Activity::class, mappedBy: 'sport')]
    private Collection $activities;

    /**
     * @var Collection<int, Difficulty>
     */
    #[ORM\OneToMany(targetEntity: Difficulty::class, mappedBy: 'sport')]
    private Collection $difficulties;

    public function __construct()
    {
        $this->activities = new ArrayCollection();
        $this->difficulties = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection<int, Activity>
     */
    public function getActivities(): Collection
    {
        return $this->activities;
    }

    public function addActivity(Activity $activity): static
    {
        if (!$this->activities->contains($activity)) {
            $this->activities->add($activity);
            $activity->setSport($this);
        }

        return $this;
    }

    public function removeActivity(Activity $activity): static
    {
        if ($this->activities->removeElement($activity)) {
            // set the owning side to null (unless already changed)
            if ($activity->getSport() === $this) {
                $activity->setSport(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Difficulty>
     */
    public function getDifficulties(): Collection
    {
        return $this->difficulties;
    }

    public function addDifficulty(Difficulty $difficulty): static
    {
        if (!$this->difficulties->contains($difficulty)) {
            $this->difficulties->add($difficulty);
            $difficulty->setSport($this);
        }

        return $this;
    }

    public function removeDifficulty(Difficulty $difficulty): static
    {
        if ($this->difficulties->removeElement($difficulty)) {
            // set the owning side to null (unless already changed)
            if ($difficulty->getSport() === $this) {
                $difficulty->setSport(null);
            }
        }

        return $this;
    }
}
