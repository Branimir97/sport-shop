<?php

namespace App\Controller;

use App\Entity\PromoCodeUser;
use App\Repository\PromoCodeUserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route({
 *     "en": "/used/promo/codes",
 *     "hr": "/iskorišteni/promo/kodovi"
 * })
 * @IsGranted("ROLE_ADMIN")
 */
class PromoCodeUserController extends AbstractController
{
    /**
     * @Route("/", name="promo_code_user_index", methods={"GET"})
     */
    public function index(PromoCodeUserRepository $promoCodeUserRepository): Response
    {
        return $this->render('promo_code_user/index.html.twig', [
            'promo_code_users' => $promoCodeUserRepository->findBy([], ['id' => 'DESC']),
        ]);
    }

    /**
     * @Route("/{id}", name="promo_code_user_delete", methods={"DELETE"})
     */
    public function delete(Request $request, PromoCodeUser $promoCodeUser,
                           TranslatorInterface $translator): Response
    {
        if ($this->isCsrfTokenValid('delete'.$promoCodeUser->getId(),
            $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($promoCodeUser);

            $this->addFlash('danger',
                $translator->trans('flash_message.record_deleted',
                    [
                        '%user_name%' => $promoCodeUser->getUser()->getName(),
                        '%user_surname%' => $promoCodeUser->getUser()->getSurname()
                    ], 'promo_code_user'));
            $entityManager->flush();
        }
        return $this->redirectToRoute('promo_code_user_index');
    }
}
