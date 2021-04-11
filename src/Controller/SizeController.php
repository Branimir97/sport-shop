<?php

namespace App\Controller;

use App\Entity\Size;
use App\Form\SizeType;
use App\Repository\SizeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/size")
 */
class SizeController extends AbstractController
{
    /**
     * @Route("/", name="size_index", methods={"GET"})
     */
    public function index(SizeRepository $sizeRepository): Response
    {
        return $this->render('size/index.html.twig', [
            'sizes' => $sizeRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="size_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $size = new Size();
        $form = $this->createForm(SizeType::class, $size);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($size);
            $entityManager->flush();

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
     * @Route("/{id}/edit", name="size_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Size $size): Response
    {
        $form = $this->createForm(SizeType::class, $size);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

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
        if ($this->isCsrfTokenValid('delete'.$size->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($size);
            $entityManager->flush();
        }

        return $this->redirectToRoute('size_index');
    }
}
