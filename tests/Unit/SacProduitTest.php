<?php

namespace App\Tests\Unit;

use App\Entity\SacProduit;
use PHPUnit\Framework\TestCase;

class SacProduitTest extends TestCase
{
    public function testQuantiteParDefautEstUn(): void
    {
        $sacProduit = new SacProduit();

        $this->assertSame(1, $sacProduit->getQuantite());
    }

    public function testQuantitePositive(): void
    {
        $sacProduit = new SacProduit();
        $sacProduit->setQuantite(3);

        $this->assertSame(3, $sacProduit->getQuantite());
        $this->assertGreaterThan(0, $sacProduit->getQuantite());
    }

    public function testAugmenterQuantite(): void
    {
        $sacProduit = new SacProduit();
        $sacProduit->setQuantite(2);
        $sacProduit->setQuantite($sacProduit->getQuantite() + 3);

        $this->assertSame(5, $sacProduit->getQuantite());
    }

    public function testReduireQuantiteRestePositive(): void
    {
        $sacProduit = new SacProduit();
        $sacProduit->setQuantite(5);
        $sacProduit->setQuantite($sacProduit->getQuantite() - 2);

        $this->assertSame(3, $sacProduit->getQuantite());
        $this->assertGreaterThan(0, $sacProduit->getQuantite());
    }
}
