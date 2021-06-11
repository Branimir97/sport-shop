<?php

namespace App\Controller;

use App\Entity\PromoCode;
use App\Form\PromoCodeType;
use App\Repository\PromoCodeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/promo/code")
 */
class PromoCodeController extends AbstractController
{
    /**
     * @Route("/", name="promo_code_index", methods={"GET"})
     */
    public function index(PromoCodeRepository $promoCodeRepository): Response
    {
        return $this->render('promo_code/index.html.twig', [
            'promo_codes' => $promoCodeRepository->findBy([], ['id'=>'DESC']),
        ]);
    }

    /**
     * @Route("/new", name="promo_code_new", methods={"GET","POST"})
     */
    public function new(Request $request, PromoCodeRepository $promoCodeRepository): Response
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
                $this->addFlash('danger', 'Već postoji takav promo kod.');
                return $this->redirectToRoute('promo_code_index');
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

            $this->addFlash('success', 'Promo kod uspješno generiran.');
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
     * @Route("/{id}/edit", name="promo_code_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, PromoCode $promoCode, PromoCodeRepository $promoCodeRepository): Response
    {
        $form = $this->createForm(PromoCodeType::class, $promoCode);
        $form->handleRequest($request);
        $promoCodesObj = $promoCodeRepository->findAll();
        $promoCodes = [];
        foreach($promoCodesObj as $promoCodeObj) {
            array_push($promoCodes, $promoCodeObj->getCode());
        }
        if ($form->isSubmitted() && $form->isValid()) {
            if(in_array($promoCode->getCode(), $promoCodes)) {
                $this->addFlash('danger', 'Već postoji takav promo kod.');
                return $this->redirectToRoute('promo_code_index');
            }
            $endDate = $form->get('endDate')->getData();
            $now = new \DateTime("now");
            if($endDate < $now) {
                $promoCode->setStatus("ISTEKAO");
            } else {
                $promoCode->setStatus("AKTIVAN");
            }
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', 'Promo kod uspješno ažuriran.');
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
    public function delete(Request $request, PromoCode $promoCode): Response
    {
        if ($this->isCsrfTokenValid('delete'.$promoCode->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $promoCodeUsers = $promoCode->getPromoCodeUsers();
            foreach($promoCodeUsers as $promoCodeUser) {
                $entityManager->remove($promoCodeUser);
            }
            $entityManager->remove($promoCode);
            $entityManager->flush();
        }
        $this->addFlash('danger', 'Promo kod uspješno obrisan.');
        return $this->redirectToRoute('promo_code_index');
    }
}
