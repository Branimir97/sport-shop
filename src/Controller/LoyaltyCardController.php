<?php

namespace App\Controller;

use App\Entity\LoyaltyCard;
use App\Form\LoyaltyCardType;
use App\Repository\LoyaltyCardRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route({
 *     "en": "/loyalty/cards",
 *     "hr": "/kartice/vjernosti"
 * })
 */
class LoyaltyCardController extends AbstractController
{
    /**
     * @Route("/", name="loyalty_card_index", methods={"GET"})
     */
    public function index(LoyaltyCardRepository $loyaltyCardRepository): Response
    {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        return $this->render('loyalty_card/index.html.twig', [
            'loyalty_cards' => $loyaltyCardRepository->findBy([], ['id' => 'DESC']),
        ]);
    }

    /**
     * @Route({
     *     "en": "/new",
     *     "hr": "/nova"
     * }, name="loyalty_card_new", methods={"GET", "POST"})
     */
    public function new(TranslatorInterface $translator): Response
    {
        $this->denyAccessUnlessGranted("ROLE_USER");
        $loyaltyCard = new LoyaltyCard();
        $loyaltyCard->setUser($this->getUser());
        $randomNumber = substr(str_shuffle("012345678912345"), 0, 15);
        $loyaltyCard->setNumber($randomNumber);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($loyaltyCard);
        $entityManager->flush();

        $this->addFlash('success',
            $translator->trans('flash_message.loyalty_card_created',
                [], 'loyalty_card'));
        return $this->redirectToRoute('account_settings');
    }

    /**
     * @Route({
     *     "en": "/new/administrator",
     *     "hr": "/nova/administrator"
     * }, name="loyalty_card_new_admin", methods={"GET", "POST"})
     */
    public function newByAdmin(Request $request,
                               UserRepository $userRepository,
                               TranslatorInterface $translator): Response
    {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        $users = $userRepository->findAll();
        $usersWithoutLoyaltyCard = [];
        foreach($users as $user) {
            if(!is_null($user->getLoyaltyCard())) {
                array_push($usersWithoutLoyaltyCard, $user);
            }
        }
        $loyaltyCard = new LoyaltyCard();
        $form = $this->createForm(LoyaltyCardType::class, $loyaltyCard,
            ['users'=>$usersWithoutLoyaltyCard]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->get('user')->getData();
            $loyaltyCard->setUser($user);
            $randomNumber = substr(str_shuffle("012345678912345"), 0, 15);
            $loyaltyCard->setNumber($randomNumber);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($loyaltyCard);
            $entityManager->flush();
            $this->addFlash('success',
                $translator->trans('flash_message.loyalty_card_created',
                    [], 'loyalty_card'));
            return $this->redirectToRoute('loyalty_card_index');
        }
        return $this->render('loyalty_card/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="loyalty_card_show", methods={"GET"})
     */
    public function show(LoyaltyCard $loyaltyCard): Response
    {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        return $this->render('loyalty_card/show.html.twig', [
            'loyalty_card' => $loyaltyCard,
        ]);
    }

    /**
     * @Route({
     *     "en": "/{id}/edit",
     *     "hr": "/{id}/uredi"
     * }, name="loyalty_card_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, LoyaltyCard $loyaltyCard,
                         TranslatorInterface $translator): Response
    {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        $form = $this->createForm(LoyaltyCardType::class, $loyaltyCard, ['isEdit'=>true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success',
                $translator->trans('flash_message.loyalty_card_edited',
                    [], 'loyalty_card'));
            return $this->redirectToRoute('loyalty_card_index');
        }

        return $this->render('loyalty_card/edit.html.twig', [
            'loyalty_card' => $loyaltyCard,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="loyalty_card_delete", methods={"DELETE"})
     */
    public function delete(Request $request, LoyaltyCard $loyaltyCard,
                           TranslatorInterface $translator): Response
    {
        $this->denyAccessUnlessGranted("ROLE_USER");
        if ($this->isCsrfTokenValid('delete'.$loyaltyCard->getId(),
            $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();

            $entityManager->persist($loyaltyCard->getUser());
            $entityManager->remove($loyaltyCard);
            $entityManager->flush();
        }
        if($this->isGranted("ROLE_ADMIN")) {
            $this->addFlash('danger',
                $translator->trans('flash_message.loyalty_card_deleted',
                    [], 'loyalty_card'));
            return $this->redirectToRoute('loyalty_card_index');
        } else {
            $this->addFlash('danger',
                $translator->trans('flash_message.loyalty_card_deleted',
                    [], 'loyalty_card'));
            return $this->redirectToRoute('account_settings');
        }
    }
}
