<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Image;
use App\Entity\Item;
use App\Entity\ItemCategory;
use App\Entity\ItemColor;
use App\Entity\ItemSize;
use App\Entity\ItemTag;
use App\Entity\Tag;
use App\Form\NewItemCategoryType;
use App\Form\NewItemColorType;
use App\Form\NewItemImageType;
use App\Form\NewItemSizeType;
use App\Form\NewItemTagType;
use App\Form\QuantityType;
use App\Form\ItemType;
use App\Repository\ColorRepository;
use App\Repository\ImageRepository;
use App\Repository\ItemCategoryRepository;
use App\Repository\ItemRepository;
use App\Repository\ItemTagRepository;
use App\Repository\SizeRepository;
use App\Repository\TagRepository;
use App\Service\ImageUploadHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
    public function new(Request $request, TagRepository $tagRepository,
                        ImageUploadHelper $uploadHelper): Response
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
            $images = $form->get('image')->getData();
            if(count($sizes)!=0 || count($colors)!=0) {
                $session = new Session();
                $session->set('item', $item);
                $session->set('categories', $categories);
                $session->set('tags', $tags);
                $session->set('sizes', $sizes);
                $session->set('colors', $colors);
                foreach ($images as $image) {
                    $newFileName = $uploadHelper->uploadImageTemporary($image);
                    if(is_null($newFileName)) {
                        $this->addFlash('danger',
                            'Pogreška prilikom prijenosa fotografija.
                                    Dopušteni formati fotografija: jpg, jpeg i png.'
                        );
                        return $this->redirectToRoute('item_new');
                    }
                }
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
                    foreach ($explodedTags as $tagName) {
                        $tagObject = $tagRepository->findOneBy(['name'=>$tagName]);
                        if(is_null($tagObject)) {
                            $tagObject = new Tag();
                            $tagObject->setName($tagName);
                        }
                        $entityManager->persist($tagObject);
                        $itemTag = new ItemTag();
                        $itemTag->setItem($item);
                        $itemTag->setTag($tagObject);
                        $entityManager->persist($itemTag);
                    }
                }
                foreach ($images as $image) {
                    $newFileName = $uploadHelper->uploadItemImage($image);
                    if(is_null($newFileName)) {
                        $this->addFlash('danger',
                            'Pogreška prilikom prijenosa fotografija. 
                                    Dopušteni formati fotografija: jpg, jpeg i png.'
                        );
                        return $this->redirectToRoute('item_new');
                    }
                    $image = new Image();
                    $image->setPath($newFileName);
                    $image->setItem($item);
                    $entityManager->persist($image);
                }
                $entityManager->persist($item);
                $entityManager->flush();
                $this->addFlash('success', 'Artikl uspješno dodan.');
                return $this->redirectToRoute('item_index');
            }
        }
        return $this->render('new_item.html.twig', [
            'item' => $item,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/quantity/set", name="item_quantity_set", methods={"GET","POST"})
     */
    public function setQuantity(Request $request, ImageUploadHelper $uploadHelper,
                                SizeRepository $sizeRepository,
                                ColorRepository $colorRepository,
                                TagRepository $tagRepository): Response
    {
        $markedSizes = [];
        $markedColors = [];
        $sizeQuantities = [];
        $colorQuantities = [];

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
                array_push($markedSizes, $size->getId());
            }
            foreach($colors as $color) {
                array_push($markedColors, $color->getId());
            }
            $formData = $formQuantity->getData();
            $formKeys = array_keys($formData);

            foreach($formKeys as $key) {
                if(str_contains($key, "size")) {
                    array_push($sizeQuantities, $formData[$key]);
                }
                if(str_contains($key, "color")) {
                    array_push($colorQuantities, $formData[$key]);
                }
            }
            $sizesDone = array_combine($markedSizes, $sizeQuantities);
            $colorsDone = array_combine($markedColors, $colorQuantities);

            foreach($sizesDone as $key=>$value) {
                $sizeObj = $sizeRepository->findOneBy(['id'=>$key]);
                $itemSize = new ItemSize();
                $itemSize->setItem($item);
                $itemSize->setSize($sizeObj);
                $itemSize->setQuantity($value);
                $entityManager->persist($itemSize);
            }
            foreach($colorsDone as $key=>$value) {
                $colorObj = $colorRepository->findOneBy(['id'=>$key]);
                $itemColor = new ItemColor();
                $itemColor->setItem($item);
                $itemColor->setColor($colorObj);
                $itemColor->setQuantity($value);
                $entityManager->persist($itemColor);
            }
            foreach($categories as $category) {
                $itemCategory = new ItemCategory();
                $itemCategory->setItem($item);
                $itemCategory->setCategory($category);
                $entityManager->merge($itemCategory); //Ne radi s persistom, razlog nepoznat
            }
            if(!is_null($tags)) {
                $explodedTags = explode(PHP_EOL, $tags);
                foreach ($explodedTags as $tagName) {
                    $tagObject = $tagRepository->findOneBy(['name'=>$tagName]);
                    if(is_null($tagObject)) {
                        $tagObject = new Tag();
                        $tagObject->setName($tagName);
                    }
                    $entityManager->persist($tagObject);
                    $itemTag = new ItemTag();
                    $itemTag->setItem($item);
                    $itemTag->setTag($tagObject);
                    $entityManager->persist($itemTag);
                }
            }
            $imageFileNames = $uploadHelper->uploadImageFromTemporaryFolderAndGetImagesList();
            foreach ($imageFileNames as $imageFileName) {
                $image = new Image();
                $image->setPath($imageFileName);
                $image->setItem($item);
                $entityManager->persist($image);
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
        return $this->render('show_item.html.twig', [
            'item' => $item,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="item_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Item $item): Response
    {
        $form = $this->createForm(ItemType::class, $item, ['isEdit'=>true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', 'Artikl "'.$item->getTitle().'" uspješno ažuriran.');
            return $this->redirectToRoute('item_index');
        }

        return $this->render('item/edit_item.html.twig', [
            'item' => $item,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/image/add", name="item_image_add", methods={"GET", "POST"})
     */
    public function addImage(Request $request, ImageUploadHelper $uploadHelper, ItemRepository $itemRepository): Response
    {
        $form = $this->createForm(NewItemImageType::class);
        $form->handleRequest($request);
        $item = $itemRepository->findOneBy(['id'=>$request->get('id')]);
        if($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $images = $form->get('image')->getData();
            foreach ($images as $image) {
                $newFileName = $uploadHelper->uploadItemImage($image);
                if(is_null($newFileName)) {
                    $this->addFlash('danger',
                        'Pogreška prilikom prijenosa fotografija. 
                                    Dopušteni formati fotografija: jpg, jpeg i png.'
                    );
                    return $this->redirectToRoute('item_edit', ['id'=>$item->getId()]);
                }
                $image = new Image();
                $image->setPath($newFileName);
                $image->setItem($item);
                $entityManager->persist($image);
            }
            $entityManager->flush();
            $this->addFlash('success', "Fotografija/e uspješno dodana/e.");
            return $this->redirectToRoute('item_edit', ['id'=>$item->getId()]);
        }
        return $this->render('item/new_image.html.twig', [
            'item' => $item,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/image/delete", name="item_image_delete", methods={"DELETE"})
     */
    public function deleteImage(Request $request, Image $image, ImageRepository $imageRepository): RedirectResponse
    {
        if ($this->isCsrfTokenValid('delete'.$image->getId(), $request->request->get('_token'))) {
            $image = $imageRepository->findOneBy(['id'=>$image->getId()]);
            $itemId = $image->getItem()->getId();
            unlink('../public/uploads/'.$image->getPath());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($image);
            $this->addFlash('success', 'Fotografija uspješno obrisana.');
            $entityManager->flush();
        }
        return $this->redirectToRoute('item_edit', ['id'=>$itemId]);
    }

    /**
     * @Route("/{id}/add/category", name="item_add_category", methods={"GET","POST"})
     */
    public function addCategory(Request $request, ItemRepository $itemRepository): Response
    {
        $item = $itemRepository->findOneBy(['id'=>$request->get('id')]);
        $categories = $item->getItemCategories();
        $categoryNames = [];
        foreach($categories as $category) {
            array_push($categoryNames, $category->getCategory()->getName());
        }
        $form = $this->createForm(NewItemCategoryType::class, [], ['category_names'=>$categoryNames]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $categories = $form->get('category')->getData();
            foreach($categories as $category) {
                $itemCategory = new ItemCategory();
                $itemCategory->setItem($item);
                $itemCategory->setCategory($category);
                $entityManager->persist($itemCategory);
            }
            $entityManager->flush();
            $this->addFlash('success', 'Kategorija/e uspješno dodana/e.');
            return $this->redirectToRoute('item_edit', ['id'=>$item->getId()]);
        }

        return $this->render('item/new_category.html.twig', [
            'item' => $item,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete/category", name="item_category_delete", methods={"DELETE"})
     */
    public function deleteCategory(Request $request, ItemCategory $itemCategory, ItemCategoryRepository $itemCategoryRepository): RedirectResponse
    {
        if ($this->isCsrfTokenValid('delete'.$itemCategory->getId(), $request->request->get('_token'))) {
            $itemCategory = $itemCategoryRepository->findOneBy(['id'=>$itemCategory->getId()]);
            $itemId = $itemCategory->getItem()->getId();
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($itemCategory);
            $this->addFlash('success', 'Kategorija "'.$itemCategory->getCategory()->getName().'" uspješno obrisana.');
            $entityManager->flush();
        }
        return $this->redirectToRoute('item_edit', ['id'=>$itemId]);
    }

    /**
     * @Route("/{id}/add/tag", name="item_add_tag", methods={"GET","POST"})
     */
    public function addTag(Request $request, ItemRepository $itemRepository, TagRepository $tagRepository, ItemTagRepository $itemTagRepository): Response
    {
        $item = $itemRepository->findOneBy(['id'=>$request->get('id')]);
        $form = $this->createForm(NewItemTagType::class);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $tags = $form->get('tag')->getData();
            $explodedTags = explode(PHP_EOL, $tags);
            foreach ($explodedTags as $tagName) {
                $tagObject = $tagRepository->findOneBy(['name'=>$tagName]);
                if(is_null($tagObject)) {
                    $tagObject = new Tag();
                    $tagObject->setName($tagName);
                    $entityManager->persist($tagObject);
                    $entityManager->flush();
                }
                $itemTagObj = $itemTagRepository->findOneBy(['tag'=>$tagObject->getId()]);
                if(is_null($itemTagObj)) {
                    $itemTag = new ItemTag();
                    $itemTag->setItem($item);
                    $itemTag->setTag($tagObject);
                    $entityManager->persist($itemTag);
                } else {
                    $this->addFlash('danger', 'Tag/ovi koje ste pokušali unijeti već su povezani s ovim artiklom.');
                    return $this->redirectToRoute('item_edit', ['id'=>$item->getId()]);
                }
            }
            $entityManager->flush();
            $this->addFlash('success', 'Tag/ovi uspješno dodan/i.');
            return $this->redirectToRoute('item_edit', ['id'=>$item->getId()]);
        }
        return $this->render('item/new_tag.html.twig', [
            'item' => $item,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete/tag", name="item_tag_delete", methods={"DELETE"})
     */
    public function deleteTag(Request $request, ItemTag $itemTag, ItemTagRepository $itemTagRepository): RedirectResponse
    {
        if ($this->isCsrfTokenValid('delete'.$itemTag->getId(), $request->request->get('_token'))) {
            $itemTag = $itemTagRepository->findOneBy(['id'=>$itemTag->getId()]);
            $itemId = $itemTag->getItem()->getId();
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($itemTag);
            $this->addFlash('success', 'Tag "'.$itemTag->getTag()->getName().'" uspješno obrisan.');
            $entityManager->flush();
        }
        return $this->redirectToRoute('item_edit', ['id'=>$itemId]);
    }

    /**
     * @Route("/{id}/add/color", name="item_add_color", methods={"GET","POST"})
     */
    public function addColor(Request $request, ItemRepository $itemRepository): Response
    {
        $item = $itemRepository->findOneBy(['id'=>$request->get('id')]);
        $colors = $item->getItemColors();
        $colorValues = [];
        foreach($colors as $color) {
            array_push($colorValues, $color->getColor()->getValue());
        }
        $form = $this->createForm(NewItemColorType::class, null, ['color_values'=>$colorValues]);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

        }
        return $this->render('item/new_color.html.twig', [
            'item' => $item,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit/color", name="item_color_edit", methods={""})
     */
    public function editColor(Request $request)
    {

    }

    /**
     * @Route("/{id}/delete/color", name="item_color_delete", methods={"DELETE"})
     */
    public function deleteColor(Request $request)
    {

    }


    /**
     * @Route("/{id}/add/size", name="item_add_size", methods={"GET","POST"})
     */
    public function addSize(Request $request, ItemRepository $itemRepository): Response
    {
        $item = $itemRepository->findOneBy(['id'=>$request->get('id')]);
        $sizes = $item->getItemSizes();
        $sizeValues = [];
        foreach($sizes as $size) {
            array_push($sizeValues, $size->getSize()->getValue());
        }
        $form = $this->createForm(NewItemSizeType::class, null, ['size_values'=>$sizeValues]);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

        }
        return $this->render('item/new_size.html.twig', [
            'item' => $item,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit/size", name="item_size_edit", methods={""})
     */
    public function editSize(Request $request)
    {

    }

    /**
     * @Route("/{id}/delete/size", name="item_size_delete", methods={"DELETE"})
     */
    public function deleteSize(Request $request)
    {

    }

    /**
     * @Route("/{id}", name="item_delete", methods={"DELETE"})
     */
    public function deleteItem(Request $request, Item $item, ImageRepository $imageRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$item->getId(), $request->request->get('_token'))) {
            $images = $imageRepository->findBy(['item'=>$item]);
            if($images !== null) {
                foreach($images as $image) {
                    unlink('../public/uploads/'.$image->getPath());
                }
            }
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
    public function getItemdetails(Request $request, ItemRepository $itemRepository): Response
    {
        return $this->render('item/item_details.html.twig', [
            'item' => $itemRepository->findOneBy(['id'=>$request->get('id')]),
        ]);
    }
}
