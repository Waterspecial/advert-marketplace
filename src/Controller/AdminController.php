<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class AdminController extends AbstractController
{
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin', name: 'admin_dashboard')]
    public function admin(Request $request, EntityManagerInterface $em)
    {
        // Handle role update
        if ($request->isMethod('POST') && $request->request->has('update_role')) {

            $userId = $request->request->get('user_id');
            $newRole = $request->request->get('role');

            $user = $em->getRepository(User::class)->find($userId);

            if ($user) {
                $roleMap = [
                    'admin' => 'ROLE_ADMIN',
                    'moderator' => 'ROLE_MODERATOR',
                    'user' => 'ROLE_USER',   // ✔ fixed
                ];

                if (isset($roleMap[$newRole])) {
                    $user->setRoles([$roleMap[$newRole]]);
                    $em->flush();
                    $this->addFlash('success', 'User role updated!');
                }
            }

            return $this->redirectToRoute('admin_dashboard');
        }

        // Handle delete operation
        if ($request->query->has('delete')) {

            $userId = $request->query->get('delete');
            $user = $em->getRepository(User::class)->find($userId);

            if ($user) {
                $em->remove($user);
                $em->flush();
                $this->addFlash('danger', 'User deleted!');
            }

            return $this->redirectToRoute('admin_dashboard');
        }

        $users = $em->getRepository(User::class)->findAll();

        $adminsCount = 0;
        $moderatorsCount = 0;

        foreach ($users as $u) {
            $role = $u->getRoles()[0] ?? 'ROLE_USER';

            if ($role === 'ROLE_ADMIN') {
                $adminsCount++;
            }
            if ($role === 'ROLE_MODERATOR') {
                $moderatorsCount++;
            }
        }

        return $this->render('admin/dashboard.html.twig', [
            'users' => $users,
            'adminsCount' => $adminsCount,
            'moderatorsCount' => $moderatorsCount,
        ]);
    }
}
