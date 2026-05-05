<?php

// src/Entity/Sac.php

namespace App\Entity;

use App\Repository\SacRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SacRepository::class)]
class Sac
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\OneToOne(inversedBy: 'sac', targetEntity: Livreur::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Livreur $livreur = null;

    #[ORM\OneToMany(mappedBy: 'sac', targetEntity: SacProduit::class, orphanRemoval: true)]
    private Collection $sacProduits;

    public function __construct()
    {
        $this->sacProduits = new ArrayCollection();
    }

    // Getters et Setters
    public function getId(): ?int
    {
        return $this->id;
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

    public function getLivreur(): ?Livreur
    {
        return $this->livreur;
    }

    public function setLivreur(Livreur $livreur): static
    {
        $this->livreur = $livreur;

        return $this;
    }

    /**
     * @return Collection<int, SacProduit>
     */
    public function getSacProduits(): Collection
    {
        return $this->sacProduits;
    }

    public function addSacProduit(SacProduit $sacProduit): static
    {
        if (!$this->sacProduits->contains($sacProduit)) {
            $this->sacProduits->add($sacProduit);
            $sacProduit->setSac($this);
        }

        return $this;
    }

    public function removeSacProduit(SacProduit $sacProduit): static
    {
        if ($this->sacProduits->removeElement($sacProduit)) {
            if ($sacProduit->getSac() === $this) {
                $sacProduit->setSac(null);
            }
        }

        return $this;
    }
}
