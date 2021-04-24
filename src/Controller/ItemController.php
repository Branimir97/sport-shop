<?php

namespace App\Controller;

use App\Entity\Item;
use App\Entity\ItemCategory;
use App\Entity\ItemColor;
use App\Entity\ItemSize;
use App\Entity\ItemTag;
use App\Entity\Tag;
use App\Form\QuantityType;
use App\Form\ItemType;
use App\Repository\CategoryRepository;
use App\Repository\ItemRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
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
    public function new(Request $request): Response
    {
        $item = new Item();
        $form = $this->createForm(ItemType::class, $item);
        $form->handleRequest($request);
        $entityManager = $this->getDoctrine()->getManager();

        if ($form->isSubmitted() && $form->isValid()) {
            $item->setCipher(uniqid());
            $categories = $form->get('category')->getData();
            $tags = $form->get('tag')->getData();
            $sizes = $form->get('size')->getData();
            $colors = $form->get('color')->getData();
            if(count($sizes)!=0 || count($colors)!=0) {
                $session = new Session();
                $session->set('item', $item);
                $session->set('categories', $categories);
                $session->set('tags', $tags);
                $session->set('sizes', $sizes);
                $session->set('colors', $colors);
                return $this->redirectToRoute('item_quantity_set');
            } else {
                foreach($categories as $category) {
                    $itemCategory = new ItemCategory();
                    $itemCategory->setItem($item);
                    $itemCategory->setCategory($category);
                    $entityManager->persist($itemCategory);
                }
                if(!is_null($tags)) {
                    $explodedTags = explode(PHP_EOL, $tags);
                    foreach ($explodedTags as $tag) {
                        $tagObject = new Tag();
                        $tagObject->setName($tag);
                        $entityManager->persist($tagObject);
                        $itemTag = new ItemTag();
                        $itemTag->setItem($item);
                        $itemTag->setTag($tagObject);
                        $entityManager->persist($itemTag);
                    }
                }
                $entityManager->persist($item);
                $entityManager->flush();
                $this->addFlash('success', 'Artikl uspješno dodan.');
                return $this->redirectToRoute('item_index');
            }

        }
        return $this->render('item/new.html.twig', [
            'item' => $item,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/quantity/set", name="item_quantity_set", methods={"GET","POST"})
     */
    public function quantitySet(Request $request): Response
    {
        $item = $this->get('session')->get('item');
        $categories = $this->get('session')->get('categories');
        $tags = $this->get('session')->get('tags');
        $sizes = $this->get('session')->get('sizes');
        $colors = $this->get('session')->get('colors');
        $entityManager = $this->getDoctrine()->getManager();
        $formQuantity = $this->createForm(QuantityType::class, null, [
            'sizes'=>$sizes, 'colors'=>$colors
        ]);
        $formQuantity->handleRequest($request);

        if($formQuantity->isSubmitted() && $formQuantity->isValid()) {
            foreach($sizes as $size) {
                $itemSize = new ItemSize();
                $itemSize->setItem($item);
                $itemSize->setSize($size);
                $itemSize->setQuantity(5);
                $entityManager->persist($itemSize);
            }
            foreach($colors as $color) {
                $itemColor = new ItemColor();
                $itemColor->setItem($item);
                $itemColor->setColor($color);
                $itemColor->setQuantity(5);
                $entityManager->persist($itemColor);
            }
            foreach($categories as $category) {
                $itemCategory = new ItemCategory();
                $itemCategory->setCategory($category);
                $entityManager->persist($itemCategory);
            }
            if(!is_null($tags)) {
                $explodedTags = explode(PHP_EOL, $tags);
                foreach ($explodedTags as $tag) {
                    $tagObject = new Tag();
                    $tagObject->setName($tag);
                    $entityManager->persist($tagObject);
                    $itemTag = new ItemTag();
                    $itemTag->setItem($item);
                    $itemTag->setTag($tagObject);
                    $entityManager->persist($itemTag);
                }
            }
            $entityManager->persist($item);
            $entityManager->flush();
            $this->addFlash('success', 'Artikl uspješno dodan.');
            return $this->redirectToRoute('item_index');
        }
        return $this->render('item/set_quantity.html.twig',[
            'form' => $formQuantity->createView(),
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

    /**
     * @Route("/{id}/details", name="item_details", methods={"GET"})
     */
    public function details(Request $request, ItemRepository $itemRepository): Response
    {
        return $this->render('item/details.html.twig', [
            'item' => $itemRepository->findOneBy(['id'=>$request->get('id')]),
        ]);
    }
}
