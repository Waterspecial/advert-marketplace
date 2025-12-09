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
use Knp\Component\Pager\PaginatorInterface;

class AdvertController extends AbstractController
{
    #[Route('/', name: 'app_public_adverts')]
    public function publicAdverts(
        AdvertRepository $repo,
        Request $request,
        PaginatorInterface $paginator
    ): Response {
        $query = $repo->createQueryBuilder('a')
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery();

        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            6
        );

        return $this->render('advert.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/advert', name: 'app_user_adverts')]
    public function userAdverts(
        AdvertRepository $repo,
        Request $request,
        PaginatorInterface $paginator
    ): Response {
        $user = $this->getUser();

        $query = $repo->createQueryBuilder('a')
            ->where('a.user = :user')
            ->setParameter('user', $user)
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery();

        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            6
        );

        return $this->render('advert/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/advert/create', name: 'app_advert_create')]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $advert = new Advert();
        $advert->setCreatedAt(new \DateTimeImmutable());
        $advert->setUser($this->getUser());

        $form = $this->createForm(AdvertType::class, $advert);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($advert);
            $em->flush();

            return $this->redirectToRoute('app_user_adverts');
        }

        return $this->render('advert/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // ---------- VIEW ----------
    #[Route('/advert/{id}', name: 'app_advert_view', requirements: ['id' => '[0-9a-fA-F\-]{36}'])]
    public function view(Advert $advert): Response
    {
        return $this->render('advert/view.html.twig', [
            'advert' => $advert,
        ]);
    }

    // ---------- EDIT ----------
    #[IsGranted('ROLE_USER')]
    #[Route('/advert/{id}/edit', name: 'app_advert_edit', requirements: ['id' => '[0-9a-fA-F\-]{36}'])]
    public function edit(Request $request, Advert $advert, EntityManagerInterface $em): Response
    {
        if ($this->getUser() !== $advert->getUser() && !$this->isGranted('ROLE_MODERATOR')) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(AdvertType::class, $advert);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            return $this->redirectToRoute('app_advert_view', [
                'id' => $advert->getId() // now a string, no conversion needed
            ]);
        }

        return $this->render('advert/edit.html.twig', [
            'form' => $form->createView(),
            'advert' => $advert,
        ]);
    }

    // ---------- DELETE ----------
    #[IsGranted('ROLE_USER')]
    #[Route('/advert/{id}/delete', name: 'app_advert_delete', methods: ['POST'], requirements: ['id' => '[0-9a-fA-F\-]{36}'])]
    public function delete(Request $request, Advert $advert, EntityManagerInterface $em): Response
    {
        if ($this->getUser() !== $advert->getUser() && !$this->isGranted('ROLE_MODERATOR')) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('delete' . $advert->getId(), $request->request->get('_token'))) {
            $em->remove($advert);
            $em->flush();
        }

        return $this->redirectToRoute('app_user_adverts');
    }
}
