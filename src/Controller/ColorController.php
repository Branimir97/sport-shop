<?php

namespace App\Controller;

use App\Entity\Color;
use App\Form\ItemColorType;
use App\Repository\ColorRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/color")
 * @IsGranted("ROLE_ADMIN")
 */
class ColorController extends AbstractController
{
    /**
     * @Route("/", name="color_index", methods={"GET"})
     */
    public function index(ColorRepository $colorRepository): Response
    {
        return $this->render('color/index.html.twig', [
            'colors' => $colorRepository->findBy([], ['id'=>'DESC']),
        ]);
    }

    /**
     * @Route("/new", name="color_new", methods={"GET","POST"})
     */
    public function new(Request $request, ColorRepository $colorRepository): Response
    {
        $color = new Color();
        $form = $this->createForm(ItemColorType::class, $color);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $colorValue = $form->get('value')->getData();
            if(!is_null($colorRepository->findOneBy(['value'=>$colorValue]))) {
                $this->addFlash('danger', 'Boja s kodom "'.$colorValue.'" već postoji.');
                return $this->redirectToRoute('color_index');
            }
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($color);
            $entityManager->flush();

            $this->addFlash('success', 'Boja uspješno dodana.');
            return $this->redirectToRoute('color_index');
        }

        return $this->render('color/new.html.twig', [
            'color' => $color,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="color_show", methods={"GET"})
     */
    public function show(Color $color): Response
    {
        return $this->render('color/show.html.twig', [
            'color' => $color,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="color_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Color $color): Response
    {
        $form = $this->createForm(ItemColorType::class, $color);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', '"'.$color->getName().'" boja uspješno ažurirana.');
            return $this->redirectToRoute('color_index');
        }

        return $this->render('color/edit.html.twig', [
            'color' => $color,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="color_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Color $color): Response
    {
        if ($this->isCsrfTokenValid('delete'.$color->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($color);

            $this->addFlash('danger', '"'.$color->getName().'" boja uspješno obrisana.');
            $entityManager->flush();
        }

        return $this->redirectToRoute('color_index');
    }
}
