<?php

// src/Entity/SacProduit.php

namespace App\Entity;

use App\Repository\SacProduitRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SacProduitRepository::class)]
class SacProduit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    #[Assert\Positive(message: 'La quantité doit être positive.')]
    private int $quantite = 1;

    #[ORM\ManyToOne(targetEntity: Sac::class, inversedBy: 'sacProduits')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Sac $sac = null;

    #[ORM\ManyToOne(targetEntity: Produit::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Produit $produit = null;

    // Getters et Setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuantite(): int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): static
    {
        $this->quantite = $quantite;

        return $this;
    }

    public function getSac(): ?Sac
    {
        return $this->sac;
    }

    public function setSac(?Sac $sac): static
    {
        $this->sac = $sac;

        return $this;
    }

    public function getProduit(): ?Produit
    {
        return $this->produit;
    }

    public function setProduit(?Produit $produit): static
    {
        $this->produit = $produit;

        return $this;
    }
}
