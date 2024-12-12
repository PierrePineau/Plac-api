<?php

namespace App\Entity;

use App\Repository\ProjectFileRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProjectFileRepository::class)]
class ProjectFile
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'projectFiles')]
    private ?Project $project = null;

    #[ORM\ManyToOne(inversedBy: 'projectFiles')]
    private ?File $file = null;

    #[ORM\Column(nullable: true)]
    private ?float $position = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): static
    {
        $this->project = $project;

        return $this;
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(?File $file): static
    {
        $this->file = $file;

        return $this;
    }

    public function getPosition(): ?float
    {
        return $this->position;
    }

    public function setPosition(?float $position): static
    {
        $this->position = $position;

        return $this;
    }
}
