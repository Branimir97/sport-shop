<?php

namespace App\Controller;

use App\Entity\Item;
use App\Entity\Tag;
use App\Form\ItemType;
use App\Repository\CategoryRepository;
use App\Repository\ItemRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/item")
 * @IsGranted("ROLE_ADMIN")
 */
class ItemController extends AbstractController
{
    /**
     * @Route("/", name="item_index", methods={"GET"})
     */
    public function index(ItemRepository $itemRepository): Response
    {
        return $this->render('item/index.html.twig', [
            'items' => $itemRepository->findBy([], ['id'=>'DESC']),
        ]);
    }

    /**
     * @Route("/new", name="item_new", methods={"GET","POST"})
     */
    public function new(Request $request, CategoryRepository $categoryRepository): Response
    {
        $item = new Item();
        $form = $this->createForm(ItemType::class, $item);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $item->setCipher(uniqid());

            $categories = $form->get('category')->getData();
            foreach($categories as $category) {
                $item->addCategory($category);
            }
//            $tags = $form->get('tag')->getData();
//            $explodedTags = explode(PHP_EOL, $tags);
//            foreach($explodedTags as $tag) {
//                $tagObject = new Tag();
//                $tagObject->setName($tag);
//                $item->addTag($tagObject);
//            }

            $sizes = $form->get('size')->getData();
            foreach($sizes as $size) {
                $item->addSize($size);
            }
            $colors = $form->get('color')->getData();
            foreach($colors as $color) {
                $item->addColor($color);
            }
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($item);
            $entityManager->flush();

            $this->addFlash('success', 'Artikl uspješno dodan.');
            return $this->redirectToRoute('item_index');
        }

        $categories = $categoryRepository->findBy([], ['id'=>'DESC']);

        return $this->render('item/new.html.twig', [
            'item' => $item,
            'form' => $form->createView(),
            'categories' => $categories
        ]);
    }

    /**
     * @Route("/{id}", name="item_show", methods={"GET"})
     */
    public function show(Item $item): Response
    {
        return $this->render('item/show.html.twig', [
            'item' => $item,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="item_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Item $item): Response
    {
        $form = $this->createForm(ItemType::class, $item);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', 'Artikl "'.$item->getTitle().'" uspješno ažuriran.');
            return $this->redirectToRoute('item_index');
        }

        return $this->render('item/edit.html.twig', [
            'item' => $item,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="item_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Item $item): Response
    {
        if ($this->isCsrfTokenValid('delete'.$item->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($item);

            $this->addFlash('danger', 'Artikl "'.$item->getTitle().'" uspješno obrisan.');
            $entityManager->flush();
        }

        return $this->redirectToRoute('item_index');
    }
}
