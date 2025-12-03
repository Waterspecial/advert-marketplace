<?php

namespace App\Controller;

use App\Entity\Advert;
use App\Form\AdvertType;
use App\Repository\AdvertRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

#[Route('/advert')]
class AdvertController extends AbstractController
{
    #[Route('/', name: 'app_advert_index')]
    public function index(AdvertRepository $repo): Response
    {
        return $this->render('advert/index.html.twig', [
            'adverts' => $repo->findAll(),
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/create', name: 'app_advert_create')]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('You must be logged in to create an advert.');
        }

        $advert = new Advert();
        $advert->setCreatedAt(new \DateTimeImmutable());
        $advert->setUser($user);

        $form = $this->createForm(AdvertType::class, $advert);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($advert);
            $em->flush();

            return $this->redirectToRoute('app_advert_index');
        }

        return $this->render('advert/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_advert_view', requirements: ['id' => '\d+'])]
    public function view(Advert $advert): Response
    {
        return $this->render('advert/view.html.twig', [
            'advert' => $advert
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/{id}/edit', name: 'app_advert_edit', requirements: ['id' => '\d+'])]
    public function edit(Request $request, Advert $advert, EntityManagerInterface $em): Response
    {
        if ($this->getUser() !== $advert->getUser() && !$this->isGranted('ROLE_MODERATOR')) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(AdvertType::class, $advert);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return $this->redirectToRoute('app_advert_view', ['id' => $advert->getId()]);
        }

        return $this->render('advert/edit.html.twig', [
            'form' => $form->createView(),
            'advert' => $advert
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/{id}/delete', name: 'app_advert_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Request $request, Advert $advert, EntityManagerInterface $em): Response
    {
        if ($this->getUser() !== $advert->getUser() && !$this->isGranted('ROLE_MODERATOR')) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('delete'.$advert->getId(), $request->request->get('_token'))) {
            $em->remove($advert);
            $em->flush();
        }

        return $this->redirectToRoute('app_advert_index');
    }
}
