<?php

namespace App\Controller;

use App\Entity\Livreur;
use App\Entity\Produit;
use App\Entity\Sac;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $passwordHasher,
        private MailerInterface $mailer
    ) {}

    #[Route('/', name: 'home')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'nbProduits' => count($this->em->getRepository(Produit::class)->findAll()),
            'nbLivreurs' => count($this->em->getRepository(Livreur::class)->findAll()),
        ]);
    }

    // ── PRODUITS ──────────────────────────────────────────────

    #[Route('/produits', name: 'web_produits')]
    public function produits(): Response
    {
        return $this->render('produit/index.html.twig', [
            'produits' => $this->em->getRepository(Produit::class)->findAll(),
        ]);
    }

    #[Route('/produits/create', name: 'web_produits_create', methods: ['POST'])]
    public function produitsCreate(Request $request): Response
    {
        $produit = new Produit();
        $produit->setNom($request->request->get('nom'));
        $produit->setPrix((float) $request->request->get('prix'));
        $produit->setDescription($request->request->get('description') ?: null);
        $produit->setCreatedAt(new \DateTimeImmutable());
        $produit->setUpdatedAt(new \DateTimeImmutable());

        $this->em->persist($produit);
        $this->em->flush();

        $this->addFlash('success', 'Produit "' . $produit->getNom() . '" ajouté avec succès.');
        return $this->redirectToRoute('web_produits');
    }

    #[Route('/produits/{id}/update', name: 'web_produits_update', methods: ['POST'])]
    public function produitsUpdate(int $id, Request $request): Response
    {
        $produit = $this->em->getRepository(Produit::class)->find($id);
        if (!$produit) {
            $this->addFlash('error', 'Produit introuvable.');
            return $this->redirectToRoute('web_produits');
        }

        $produit->setNom($request->request->get('nom'));
        $produit->setPrix((float) $request->request->get('prix'));
        $produit->setDescription($request->request->get('description') ?: null);
        $produit->setUpdatedAt(new \DateTimeImmutable());

        $this->em->flush();

        $this->addFlash('success', 'Produit mis à jour avec succès.');
        return $this->redirectToRoute('web_produits');
    }

    #[Route('/produits/{id}/delete', name: 'web_produits_delete', methods: ['POST'])]
    public function produitsDelete(int $id): Response
    {
        $produit = $this->em->getRepository(Produit::class)->find($id);
        if ($produit) {
            $this->em->remove($produit);
            $this->em->flush();
            $this->addFlash('success', 'Produit supprimé.');
        }
        return $this->redirectToRoute('web_produits');
    }

    // ── LIVREURS ──────────────────────────────────────────────

    #[Route('/livreurs', name: 'web_livreurs')]
    public function livreurs(): Response
    {
        return $this->render('livreur/index.html.twig', [
            'livreurs' => $this->em->getRepository(Livreur::class)->findAll(),
        ]);
    }

    #[Route('/livreurs/create', name: 'web_livreurs_create', methods: ['POST'])]
    public function livreursCreate(Request $request): Response
    {
        $livreur = new Livreur();
        $livreur->setNom($request->request->get('nom'));
        $livreur->setEmail($request->request->get('email'));
        $livreur->setCreatedAt(new \DateTimeImmutable());

        $password = bin2hex(random_bytes(8));
        $livreur->setPassword($this->passwordHasher->hashPassword($livreur, $password));

        $this->em->persist($livreur);
        $this->em->flush();

        $sac = new Sac();
        $sac->setLivreur($livreur);
        $sac->setCreatedAt(new \DateTimeImmutable());
        $this->em->persist($sac);
        $this->em->flush();

        $email = (new Email())
            ->from('noreply@citylunch.com')
            ->to($livreur->getEmail())
            ->subject('Vos identifiants CityLunch')
            ->text("Bonjour {$livreur->getNom()},\n\nVotre mot de passe est : $password\n\nCordialement,\nL'équipe CityLunch");
        $this->mailer->send($email);

        $this->addFlash('success', 'Livreur "' . $livreur->getNom() . '" créé. Un email avec le mot de passe a été envoyé.');
        return $this->redirectToRoute('web_livreurs');
    }

    #[Route('/livreurs/{id}/update', name: 'web_livreurs_update', methods: ['POST'])]
    public function livreursUpdate(int $id, Request $request): Response
    {
        $livreur = $this->em->getRepository(Livreur::class)->find($id);
        if (!$livreur) {
            $this->addFlash('error', 'Livreur introuvable.');
            return $this->redirectToRoute('web_livreurs');
        }

        $livreur->setNom($request->request->get('nom'));
        $livreur->setEmail($request->request->get('email'));
        $this->em->flush();

        $this->addFlash('success', 'Livreur mis à jour avec succès.');
        return $this->redirectToRoute('web_livreurs');
    }

    #[Route('/livreurs/{id}/delete', name: 'web_livreurs_delete', methods: ['POST'])]
    public function livreursDelete(int $id): Response
    {
        $livreur = $this->em->getRepository(Livreur::class)->find($id);
        if ($livreur) {
            $this->em->remove($livreur);
            $this->em->flush();
            $this->addFlash('success', 'Livreur supprimé.');
        }
        return $this->redirectToRoute('web_livreurs');
    }
}
