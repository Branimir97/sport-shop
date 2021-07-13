<?php

namespace App\Controller;

use App\Entity\PromoCode;
use App\Form\PromoCodeType;
use App\Repository\PromoCodeRepository;
use App\Repository\SubscriberRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route({
 *     "en": "/promo/codes",
 *     "hr": "/promo/kodovi",
 * })
 * @IsGranted("ROLE_ADMIN")
 */
class PromoCodeController extends AbstractController
{
    /**
     * @Route("/", name="promo_code_index", methods={"GET"})
     */
    public function index(PromoCodeRepository $promoCodeRepository): Response
    {
        return $this->render('promo_code/index.html.twig', [
            'promo_codes' => $promoCodeRepository->findBy([], ['id' => 'DESC']),
        ]);
    }

    /**
     * @Route({
     *     "en": "/new",
     *     "hr": "/novi",
     * }, name="promo_code_new", methods={"GET","POST"})
     * @throws TransportExceptionInterface
     */
    public function new(Request $request,
                        PromoCodeRepository $promoCodeRepository,
                        TranslatorInterface $translator,
                        SubscriberRepository $subscriberRepository,
                        MailerInterface $mailer): Response
    {
        $promoCode = new PromoCode();
        $form = $this->createForm(PromoCodeType::class, $promoCode);
        $form->handleRequest($request);
        $promoCodesObj = $promoCodeRepository->findAll();
        $promoCodes = [];
        foreach($promoCodesObj as $promoCodeObj) {
            array_push($promoCodes, $promoCodeObj->getCode());
        }
        if ($form->isSubmitted() && $form->isValid()) {
            if(in_array($promoCode->getCode(), $promoCodes)) {
                $this->addFlash('danger',
                    $translator->trans('flash_message.promo_code_exists',
                        [], 'promo_code'));
                return $this->redirectToRoute('promo_code_new');
            }
            $entityManager = $this->getDoctrine()->getManager();
            $endDate = $form->get('endDate')->getData();
            $now = new \DateTime("now");
            if($endDate < $now) {
                $promoCode->setStatus("ISTEKAO");
            } else {
                $promoCode->setStatus("AKTIVAN");
            }
            $entityManager->persist($promoCode);
            $entityManager->flush();

            $subscribers = $subscriberRepository->findAll();
            $subject = $translator->trans('new_promo_code.subject',
                [], 'email');
            foreach($subscribers as $subscriber) {
                $receiverEmail = $subscriber->getEmail();
                $email = (new TemplatedEmail())
                    ->from('sport-shop@gmail.com')
                    ->to($receiverEmail)
                    ->subject($subject)
                    ->context([
                        'promoCode' => $promoCode->getCode(),
                        'discountPercentage' => $promoCode->getDiscountPercentage()
                    ])
                    ->htmlTemplate('email/new_promo_code.html.twig');
                $mailer->send($email);
            }

            $this->addFlash('success',
                $translator->trans('flash_message.promo_code_created',
                    [], 'promo_code'));
            return $this->redirectToRoute('promo_code_index');
        }

        return $this->render('promo_code/new.html.twig', [
            'promo_code' => $promoCode,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="promo_code_show", methods={"GET"})
     */
    public function show(PromoCode $promoCode): Response
    {
        return $this->render('promo_code/show.html.twig', [
            'promo_code' => $promoCode,
        ]);
    }

    /**
     * @Route({
     *     "en": "/{id}/edit",
     *     "hr": "/{id}/uredi",
     * }, name="promo_code_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, PromoCode $promoCode,
                         PromoCodeRepository $promoCodeRepository,
                         TranslatorInterface $translator): Response
    {
        $form = $this->createForm(PromoCodeType::class, $promoCode);
        $currentCode = $promoCode->getCode();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if($promoCode->getCode() !== $currentCode &&
            !is_null($promoCodeRepository->findOneBy(['code' => $promoCode->getCode()]))) {
                $this->addFlash('danger',
                    $translator->trans('flash_message.promo_code_exists',
                        [], 'promo_code'));
                return $this->redirectToRoute('promo_code_edit', ['id' => $promoCode->getId()]);
            }
            $endDate = $form->get('endDate')->getData();
            $now = new \DateTime("now");
            if($endDate < $now) {
                $promoCode->setStatus("ISTEKAO");
            } else {
                $promoCode->setStatus("AKTIVAN");
            }
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success',
                $translator->trans('flash_message.promo_code_edited',
                    [], 'promo_code'));
            return $this->redirectToRoute('promo_code_index');
        }

        return $this->render('promo_code/edit.html.twig', [
            'promo_code' => $promoCode,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="promo_code_delete", methods={"DELETE"})
     */
    public function delete(Request $request, PromoCode $promoCode,
                           TranslatorInterface $translator): Response
    {
        if ($this->isCsrfTokenValid('delete'.$promoCode->getId(),
            $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $promoCodeUsers = $promoCode->getPromoCodeUsers();
            foreach($promoCodeUsers as $promoCodeUser) {
                $entityManager->remove($promoCodeUser);
            }
            $entityManager->remove($promoCode);
            $entityManager->flush();
        }
        $this->addFlash('danger',
            $translator->trans('flash_message.promo_code_deleted',
                [], 'promo_code'));
        return $this->redirectToRoute('promo_code_index');
    }
}
