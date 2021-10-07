<?php

namespace App\Controller\BackOffice;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;


/**
 * @Route("/backoffice/user", name="backoffice_user_")
 * @IsGranted("ROLE_ADMIN")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/", name="browse")
     */
    public function browse(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();
        return $this->render('backoffice/user/browse.html.twig', [
            'users' => $users
        ]);
    }

    /**
     * @Route("/{id}", name="read", requirements={"id"="\d+"})
     */
    public function read($id, UserRepository $userRepository)
    {
        $user = $userRepository->find($id);

        return $this->render('backoffice/user/read.html.twig', [
            'user' => $user
        ]);
    }

    /**
     * @Route("/edit/{id}", name="edit", methods={"GET", "POST"}, requirements={"id"="\d+"})
     */
    public function edit(Request $request, User $user, UserPasswordHasherInterface $passwordHasher): Response
    {
        $userForm = $this->createForm(UserType::class, $user);

        $userForm->handleRequest($request);

        if ($userForm->isSubmitted() && $userForm->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();

            $clearPassword = $request->request->get('user')['password']['first'];

            if (!empty($clearPassword)) {
                $hashedPassword = $passwordHasher->hashPassword($user, $clearPassword);
                $user->setPassword($hashedPassword);
            }
            $entityManager->flush();

            $this->addFlash('success', "`{$user->getPseudo()}` a bien été mis à jour");
            return $this->redirectToRoute('backoffice_user_browse');
        }

        // Comme le champ est différent entre l'ajout et la modification
        $userForm
            ->remove('password')
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'required' => false,
                'mapped' => false,
                'first_options'  => ['label' => 'Password'],
                'second_options' => ['label' => 'Repeat Password'],
            ]);

        return $this->render('backoffice/user/add.html.twig', [
            'user_form' => $userForm->createView(),
            'user' => $user,
            'page' => 'edit'
        ]);
    }

    /**
     * @Route("/add", name="add", methods={"GET", "POST"})
     */
    public function add(Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();

        $userForm = $this->createForm(UserType::class, $user);

        $userForm->handleRequest($request);

        if ($userForm->isSubmitted() && $userForm->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);

            $clearPassword = $request->request->get('user')['password']['first'];
            // si un mot de passe a été saisi
            if (!empty($clearPassword)) {
                // hashage du mot de passe écrit en clair
                $hashedPassword = $passwordHasher->hashPassword($user, $clearPassword);
                $user->setPassword($hashedPassword);
            }

            $entityManager->flush();

            $this->addFlash('success', "User `{$user->getPseudo()}` created successfully");

            return $this->redirectToRoute('backoffice_user_browse');
        }
        return $this->render('backoffice/user/add.html.twig', [
            'user_form' => $userForm->createView(),
            'page' => 'add',
        ]);
    }

    /**
     * @Route("/delete/{id}", name="delete", methods={"GET"}, requirements={"id"="\d+"})
     */
    public function delete(User $user, EntityManagerInterface $entityManager): Response
    {
        $this->addFlash('success', "User {$user->getId()} deleted");

        $entityManager->remove($user);
        $entityManager->flush();

        return $this->redirectToRoute('backoffice_user_browse');
    }
}
