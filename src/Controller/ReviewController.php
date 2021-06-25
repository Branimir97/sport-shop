<?php

namespace App\Controller;

use App\Entity\Review;
use App\Form\ReviewType;
use App\Repository\ReviewRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route({
 *     "en": "/reviews",
 *     "hr": "/recenzije"
 *     })
 * @IsGranted("ROLE_ADMIN")
 */
class ReviewController extends AbstractController
{
    /**
     * @Route("/", name="review_index", methods={"GET"})
     */
    public function index(ReviewRepository $reviewRepository): Response
    {
        return $this->render('review/index.html.twig', [
            'reviews' => $reviewRepository->findBy([], ['id'=>'DESC']),
        ]);
    }

    /**
     * @Route({
     *     "en": "/{id}/edit",
     *     "hr": "/{id}/uredi"
     * }, name="review_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Review $review): Response
    {
        $form = $this->createForm(ReviewType::class, $review);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            if($this->isGranted("ROLE_ADMIN")) {
                $this->addFlash('success',
                    'Recenzija uspješno ažurirana.');
                return $this->redirectToRoute('review_index');
            } else {
                $this->addFlash('success',
                    'Recenzija uspješno ažurirana.');
                return $this->redirectToRoute('item_details',
                    ['id'=>$review->getItem()->getId()]);
            }
        }
        return $this->render('review/edit.html.twig', [
            'review' => $review,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="review_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Review $review): Response
    {
        if ($this->isCsrfTokenValid('delete'.$review->getId(),
            $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($review);
            $entityManager->flush();
        }

        if($this->isGranted("ROLE_ADMIN")) {
            $this->addFlash('success',
                'Recenzija uspješno obrisana.');
            return $this->redirectToRoute('review_index');
        } else {
            $this->addFlash('success',
                'Recenzija uspješno obrisana.');
            return $this->redirectToRoute('item_details',
                ['id'=>$review->getItem()->getId()]);
        }
    }
}
