<?php

namespace App\Controller;

use App\Entity\PromoCodeUser;
use App\Form\PromoCodeUserType;
use App\Repository\PromoCodeRepository;
use App\Repository\PromoCodeUserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/used/promo/codes")
 */
class PromoCodeUserController extends AbstractController
{
    /**
     * @Route("/", name="promo_code_user_index", methods={"GET"})
     */
    public function index(PromoCodeUserRepository $promoCodeUserRepository): Response
    {
        return $this->render('promo_code_user/index.html.twig', [
            'promo_code_users' => $promoCodeUserRepository->findBy([], ['id'=>'DESC']),
        ]);
    }

    /**
     * @Route("/new", name="promo_code_user_new", methods={"GET","POST"})
     */
    public function new(Request $request, PromoCodeRepository $promoCodeRepository, PromoCodeUserRepository $promoCodeUserRepository): RedirectResponse
    {
        $promoCodeUser = new PromoCodeUser();
        $form = $this->createForm(PromoCodeUserType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $promoCodeObj = $promoCodeRepository->findOneBy(['code'=>$form->get('code')->getData()]);

            if(is_null($promoCodeObj)) {
                $this->addFlash('danger', 'Unijeli ste promo kod koji ne postoji.');
                return $this->redirectToRoute('promo_code_user_index');
            }

            $promoCodeuserObj = $promoCodeUserRepository->findOneBy(['promoCode'=>$promoCodeObj, 'user'=>$this->getUser()]);
            if(!is_null($promoCodeuserObj)) {
                $this->addFlash('danger', 'Unijeli ste promo kod koji ne postoji.');
                return $this->redirectToRoute('promo_code_user_index');
            } else {
                $promoCodeUser->setUser($this->getUser());
                $promoCodeUser->setPromoCode($promoCodeObj);
                $entityManager->persist($promoCodeUser);
                $entityManager->flush();

                return $this->redirectToRoute('promo_code_user_index');
            }
        }
    }

    /**
     * @Route("/{id}", name="promo_code_user_delete", methods={"DELETE"})
     */
    public function delete(Request $request, PromoCodeUser $promoCodeUser): Response
    {
        if ($this->isCsrfTokenValid('delete'.$promoCodeUser->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($promoCodeUser);

            $this->addFlash('danger',
                'Uspješno obrisana evidencija o iskorištenom promo kodu od strane korisnika 
                    '.$promoCodeUser->getUser()->getName()).' '.$promoCodeUser->getUser()->getSurname().".";
            $entityManager->flush();
        }
        return $this->redirectToRoute('promo_code_user_index');
    }
}
