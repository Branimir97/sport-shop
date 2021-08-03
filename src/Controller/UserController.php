<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserProminentCategory;
use App\Form\ResetPasswordType;
use App\Form\UserType;
use App\Repository\DeliveryAddressRepository;
use App\Repository\LoyaltyCardRepository;
use App\Repository\SubscriberRepository;
use App\Repository\UserProminentCategoryRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserController extends AbstractController
{
    /**
     * @Route({
     *     "en": "/users",
     *     "hr": "korisnici"
     * }, name="user_index", methods={"GET"})
     */
    public function index(UserRepository $userRepository): Response
    {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findBy([], ['id' => 'DESC']),
        ]);
    }

    /**
     * @Route({
     *     "en": "/user/{id}",
     *     "hr": "/korisnik/{id}"
     * }, name="user_show", methods={"GET"})
     */
    public function show(User $user): Response
    {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route({
     *     "en": "/user/{id}/edit",
     *     "hr": "/korisnik/{id}/uredi"
     * }, name="user_edit", methods={"GET","POST"})
     * @param Request $request
     * @param User $user
     * @param TranslatorInterface $translator
     * @param UserProminentCategoryRepository $userProminentCategoryRepository
     * @return Response
     */
    public function edit(Request $request, User $user,
                         TranslatorInterface $translator,
                         UserProminentCategoryRepository $userProminentCategoryRepository): Response
    {
        $this->denyAccessUnlessGranted("ROLE_USER");
        $userProminentCategories =
            $userProminentCategoryRepository->findBy(['user' => $this->getUser()]);
        $form = $this->createForm(UserType::class, $user,
            ['isEditForm' => true, 'prominent_categories' => $userProminentCategories]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $userProminentCategories = $form->get('userProminentCategory')->getData();
            if(!(is_null($userProminentCategories))) {
                foreach($userProminentCategories as $category) {
                    $userProminentCategoryObj = new UserProminentCategory();
                    $userProminentCategoryObj->setUser($user);
                    $userProminentCategoryObj->setCategory($category);
                    $entityManager->persist($userProminentCategoryObj);
                }
            }
            $entityManager->flush();

            $this->addFlash('success',
                $translator->trans('flash_message.user_edited',
                    [
                        '%user_name%' => $user->getName(),
                        '%user_surname%' => $user->getSurname()
                    ], 'user'));
            if($this->isGranted("ROLE_ADMIN")){
                return $this->redirectToRoute('user_index');
            } else {
                return $this->redirectToRoute('account_settings');
            }
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'prominentCategories' => $userProminentCategories
        ]);
    }

    /**
     * @Route({
     *     "en": "/user/{id}",
     *     "hr": "/korisnik/{id}"
     * }, name="user_delete", methods={"DELETE"})
     */
    public function delete(Request $request, User $user,
                           TranslatorInterface $translator): Response
    {
        $this->denyAccessUnlessGranted("ROLE_USER");

        if ($this->isCsrfTokenValid('delete'.$user->getId(),
            $request->request->get('_token'))) {
            $session = new Session();
            $session->invalidate();
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();
        }

        $this->addFlash('danger',
            $translator->trans('flash_message.user_deleted',
                [], 'user'));
        if($this->isGranted("ROLE_ADMIN")) {
            return $this->redirectToRoute('user_index');
        }
        return $this->redirectToRoute('homepage');
    }

    /**
     * @Route({
     *     "en": "/account/settings",
     *     "hr": "/postavke/računa"
     * }, name="account_settings", methods={"GET"})
     * @param DeliveryAddressRepository $deliveryAddressRepository
     * @param LoyaltyCardRepository $loyaltyCardRepository
     * @param SubscriberRepository $subscriberRepository
     * @return Response
     */
    public function settings(DeliveryAddressRepository $deliveryAddressRepository,
                             LoyaltyCardRepository $loyaltyCardRepository,
                             SubscriberRepository $subscriberRepository): Response
    {
        $this->denyAccessUnlessGranted("ROLE_USER");
        $deliveryAddresses = $deliveryAddressRepository->findBy(['user' => $this->getUser()]);
        $loyaltyCard = $loyaltyCardRepository->findOneBy(['user' => $this->getUser()]);
        $subscribed = $subscriberRepository->findOneBy(['email' => $this->getUser()->getUsername()]);
        return $this->render('user/account_settings.html.twig', [
            'deliveryAddresses' => $deliveryAddresses,
            'loyalty_card' => $loyaltyCard,
            'subscribed' => $subscribed
        ]);
    }

    /**
     * @Route({
     *     "en": "/{id}/password/reset",
     *     "hr": "/{id}/resetiranje/lozinke"
     * }, name="reset_password", methods={"GET", "POST"})
     */
    public function resetPassword(Request $request,
                                  UserRepository $userRepository,
                                  UserPasswordEncoderInterface $passwordEncoder,
                                  TranslatorInterface $translator)
    {
        $this->denyAccessUnlessGranted("ROLE_USER");
        $user = $userRepository->findOneBy(['id' => $request->get('id')]);
        if(!is_null($user->getFacebookId()) || !is_null($user->getGoogleId())) {
            return $this->redirectToRoute('account_settings');
        }
        $form = $this->createForm(ResetPasswordType::class, null,
            ['isAdmin' => $this->isGranted("ROLE_ADMIN")]);
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

                $this->addFlash('success',
                    $translator->trans('flash_message.password_reset',
                        [], 'user'));
                return $this->redirectToRoute('user_index');
            }
            else
            {
                $currentPassword = $form->get('current_password')->getData();
                $userPassword = $passwordEncoder->isPasswordValid($user, $currentPassword);
                if(!$userPassword)
                {
                    $form->get('current_password')->addError(new FormError(
                        $translator->trans('flash_message.form_error',
                            [], 'user')));
                }
                $newPassword =  $form->get('password')->get('first')->getData();
                $user->setPassword($passwordEncoder->encodePassword(
                    $user,
                    $newPassword
                ));
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success',
                    $translator->trans('flash_message.password_reset',
                        [], 'user'));
                return $this->redirectToRoute('account_settings');
            }
        }
        return $this->render('user/reset_password.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    /**
     * @Route({
     *     "en": "/{id}/cancel/prominent/categories",
     *     "hr": "/{id}/resetiranje/istaknutih/kategorija"
     * }, name="reset_prominent_categories", methods={"GET", "POST"})
     */
    public function resetProminentCategories(Request $request,
                                             UserRepository $userRepository,
                                             TranslatorInterface $translator): RedirectResponse
    {
        $user = $userRepository->findOneBy(['id' => $request->get('id')]);
        $userProminentCategories = $user->getUserProminentCategories();
        $entityManager = $this->getDoctrine()->getManager();
        foreach($userProminentCategories as $userProminentCategory) {
            $entityManager->remove($userProminentCategory);
        }
        $entityManager->flush();

        $this->addFlash('success',
            $translator->trans('flash_message.cancel_prominent_categories',
                [], 'user'));
        return $this->redirectToRoute('user_edit', ['id' => $user->getId()]);
    }
}
