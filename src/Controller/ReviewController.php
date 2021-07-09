<?php

namespace App\Controller;

use App\Entity\Review;
use App\Form\ReviewType;
use App\Repository\ReviewRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route({
 *     "en": "/reviews",
 *     "hr": "/recenzije"
 *     })
 */
class ReviewController extends AbstractController
{
    /**
     * @Route("/", name="review_index", methods={"GET"})
     */
    public function index(ReviewRepository $reviewRepository): Response
    {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        return $this->render('review/index.html.twig', [
            'reviews' => $reviewRepository->findBy([], ['id' => 'DESC']),
        ]);
    }

    /**
     * @Route({
     *     "en": "/{id}/edit",
     *     "hr": "/{id}/uredi"
     * }, name="review_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Review $review,
                         TranslatorInterface $translator): Response
    {
        $this->denyAccessUnlessGranted("ROLE_USER");
        $form = $this->createForm(ReviewType::class, $review);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success',
                $translator->trans('flash_message.review_edited',
                    [], 'review'));
            if($this->isGranted("ROLE_ADMIN")) {
                return $this->redirectToRoute('review_index');
            } else {
                return $this->redirectToRoute('item_details',
                    ['id' => $review->getItem()->getId()]);
            }
        }
        return $this->render('review/edit.html.twig', [
            'review' => $review,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route({
     *     "en": "/{id}/valid",
     *     "hr": "/{id}/valjana"
     * }, name="review_valid", methods={"GET", "PATCH"})
     */
    public function setReviewValid(Request $request,
                                        ReviewRepository $reviewRepository,
                                        TranslatorInterface $translator): RedirectResponse
    {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        $review = $reviewRepository->findOneBy(['id' => $request->get('id')]);
        $entityManager = $this->getDoctrine()->getManager();
        $review->setValid(true);
        $entityManager->persist($review);
        $entityManager->flush();
        $this->addFlash('success',
            $translator->trans('flash_message.review_valid',
                [], 'review'));
        return $this->redirectToRoute('review_index');
    }

    /**
     * @Route({
     *     "en": "/{id}/invalid",
     *     "hr": "/{id}/nevaljana"
     * }, name="review_invalid", methods={"GET", "PATCH"})
     */
    public function setReviewInvalid(Request $request,
                                   ReviewRepository $reviewRepository,
                                   TranslatorInterface $translator): RedirectResponse
    {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        $review = $reviewRepository->findOneBy(['id' => $request->get('id')]);
        $entityManager = $this->getDoctrine()->getManager();
        $review->setValid(false);
        $entityManager->persist($review);
        $entityManager->flush();
        $this->addFlash('danger',
            $translator->trans('flash_message.review_invalid',
                [], 'review'));
        return $this->redirectToRoute('review_index');
    }

    /**
     * @Route("/{id}", name="review_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Review $review,
                           TranslatorInterface $translator): Response
    {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        if ($this->isCsrfTokenValid('delete'.$review->getId(),
            $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($review);
            $entityManager->flush();
        }
        $this->addFlash('danger',
            $translator->trans('flash_message.review_deleted',
                [], 'review'));
        if($this->isGranted("ROLE_ADMIN")) {
            return $this->redirectToRoute('review_index');
        } else {

            return $this->redirectToRoute('item_details',
                ['id' => $review->getItem()->getId()]);
        }
    }
}
