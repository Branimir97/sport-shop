<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ResetPasswordType;
use App\Form\UserType;
use App\Repository\DeliveryAddressRepository;
use App\Repository\LoyaltyCardRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/user")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/", name="user_index", methods={"GET"})
     */
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findBy([], ['id'=>'DESC']),
        ]);
    }

    /**
     * @Route("/{id}", name="user_show", methods={"GET"})
     */
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="user_edit", methods={"GET","POST"})
     * @param Request $request
     * @param User $user
     * @return Response
     */
    public function edit(Request $request, User $user): Response
    {
        $form = $this->createForm(UserType::class, $user, ['isEditForm'=>true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'Podaci o korisniku "'.$user->getName().' '. $user->getSurname().'" uspješno ažurirani.');
            if($this->isGranted("ROLE_ADMIN")){
                return $this->redirectToRoute('user_index');
            } else {
                return $this->redirectToRoute('account_settings');
            }
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="user_delete", methods={"DELETE"})
     */
    public function delete(Request $request, User $user): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();
        }
        if($this->isGranted("ROLE_ADMIN")) {
            $this->addFlash('success', 'Korisnički račun uspješno obrisan.');
            return $this->redirectToRoute('user_index');
        }
        $this->addFlash('success', 'Korisnički račun uspješno obrisan.');
        return $this->redirectToRoute('home');
    }

    /**
     * @Route("/account/settings", name="account_settings", methods={"GET"})
     * @param DeliveryAddressRepository $deliveryAddressRepository
     * @return Response
     */
    public function settings(DeliveryAddressRepository $deliveryAddressRepository, LoyaltyCardRepository $loyaltyCardRepository): Response
    {
        $deliveryAddresses = $deliveryAddressRepository->findBy(['user'=>$this->getUser()]);
        $loyaltyCard = $loyaltyCardRepository->findOneBy(['user'=>$this->getUser()]);
        return $this->render('user/account_settings.html.twig', [
            'deliveryAddresses'=>$deliveryAddresses,
            'loyalty_card'=>$loyaltyCard
        ]);
    }

    /**
     * @Route("/{id}/password/reset", name="reset_password", methods={"GET", "POST"})
     */
    public function resetPassword(Request $request, UserRepository $userRepository, UserPasswordEncoderInterface $passwordEncoder)
    {
        $user = $userRepository->findOneBy(['id'=>$request->get('id')]);

        if(!is_null($user->getFacebookId()) || !is_null($user->getGoogleId())) {
            return $this->redirectToRoute('account_settings');
        }

        $form = $this->createForm(ResetPasswordType::class, null, ['isAdmin'=>$this->isGranted("ROLE_ADMIN")]);
        $form->handleRequest($request);

        /** @var User $user */
        if($form->isSubmitted() && $form->isValid())
        {
            if($this->isGranted("ROLE_ADMIN")){
                $newPassword =  $form->get('password')->get('first')->getData();
                $user->setPassword($passwordEncoder->encodePassword(
                    $user,
                    $newPassword
                ));

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'Lozinka uspješno promijenjena.');
                return $this->redirectToRoute('user_index');
            }
            else
            {
                $currentPassword = $form->get('current_password')->getData();
                $userPassword = $passwordEncoder->isPasswordValid($user, $currentPassword);
                if(!$userPassword)
                {
                    $form->get('current_password')->addError(new FormError('Neispravan unos trenutne lozinke.'));
                }
                $newPassword =  $form->get('password')->get('first')->getData();
                $user->setPassword($passwordEncoder->encodePassword(
                    $user,
                    $newPassword
                ));

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'Lozinka uspješno promijenjena.');
                return $this->redirectToRoute('account_settings');
            }
        }
        return $this->render('user/reset_password.html.twig', [
            'form' => $form->createView(),
            'user'=>$user,
        ]);
    }
}
