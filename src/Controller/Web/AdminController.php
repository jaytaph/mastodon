<?php

declare(strict_types=1);

namespace App\Controller\Web;

use App\Entity\Config;
use App\Entity\User;
use App\Form\InstanceConfigType;
use App\Form\RegistrationFormType;
use App\Service\ConfigService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    #[Route('/admin/first-time', name: 'admin_first_time')]
    public function first(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        // Make sure we cannot access this page if there is already an admin user
        if ($entityManager->getRepository(User::class)->count([]) !== 0) {
            return $this->redirectToRoute('admin_config');
        }

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    strval($form->get('plainPassword')->getData())
                )
            );

            $user->setRoles(['ROLE_ADMIN']);
            $user->setIsVerified(true);

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('admin_config');
        }

        return $this->render('admin/first-time.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/admin/config', name: 'admin_config')]
    public function config(Request $request, ConfigService $configService): Response
    {
        $form = $this->createForm(InstanceConfigType::class, $configService->getConfig());

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Config $config */
            $config = $form->getData();
            $configService->saveConfig($config);
            $this->addFlash('success', 'Config saved');
        }

        return $this->render('admin/config.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
