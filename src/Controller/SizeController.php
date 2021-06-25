<?php

namespace App\Controller;

use App\Entity\Size;
use App\Form\SizeType;
use App\Repository\SizeRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route({
 *     "en": "/sizes",
 *     "hr": "/veličine",
 * })
 * @IsGranted("ROLE_ADMIN")
 */
class SizeController extends AbstractController
{
    /**
     * @Route("/", name="size_index", methods={"GET"})
     */
    public function index(SizeRepository $sizeRepository): Response
    {
        return $this->render('size/index.html.twig', [
            'sizes' => $sizeRepository->findBy([], ['id'=>'DESC']),
        ]);
    }

    /**
     * @Route({
     *     "en": "/new",
     *     "hr": "/nova",
     * }, name="size_new", methods={"GET","POST"})
     */
    public function new(Request $request, SizeRepository $sizeRepository): Response
    {
        $size = new Size();
        $form = $this->createForm(SizeType::class, $size);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $sizeValue = $form->get('value')->getData();
            if(!is_null($sizeRepository->findOneBy(['value'=>$sizeValue]))) {
                $this->addFlash('danger',
                    'Veličina "'.$sizeValue.'" već postoji.');
                return $this->redirectToRoute('size_index');
            }
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($size);
            $entityManager->flush();

            $this->addFlash('success',
                'Veličina uspješno dodana.');
            return $this->redirectToRoute('size_index');
        }

        return $this->render('size/new.html.twig', [
            'size' => $size,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="size_show", methods={"GET"})
     */
    public function show(Size $size): Response
    {
        return $this->render('size/show.html.twig', [
            'size' => $size,
        ]);
    }

    /**
     * @Route({
     *     "en": "/{id}/edit",
     *     "hr": "/{id}/uredi",
     * }, name="size_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Size $size): Response
    {
        $form = $this->createForm(SizeType::class, $size);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success',
                'Veličina "'.$size->getValue().'" uspješno ažurirana.');
            return $this->redirectToRoute('size_index');
        }

        return $this->render('size/edit.html.twig', [
            'size' => $size,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="size_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Size $size): Response
    {
        if ($this->isCsrfTokenValid('delete'.$size->getId(),
            $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($size);

            $this->addFlash('danger',
                'Veličina "'.$size->getValue().'" uspješno obrisana.');
            $entityManager->flush();
        }

        return $this->redirectToRoute('size_index');
    }
}
