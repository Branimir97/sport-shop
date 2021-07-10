<?php

namespace App\Controller;

use App\Entity\Image;
use App\Entity\Item;
use App\Entity\ItemCategory;
use App\Entity\ItemColor;
use App\Entity\ItemSize;
use App\Entity\ItemTag;
use App\Form\ColorQuantityType;
use App\Form\EditColorQuantityType;
use App\Form\EditSizeQuantityType;
use App\Form\NewItemCategoryType;
use App\Form\NewItemColorType;
use App\Form\NewItemImageType;
use App\Form\NewItemSizeType;
use App\Form\NewItemTagType;
use App\Form\QuantityType;
use App\Form\ItemType;
use App\Form\SizeQuantityType;
use App\Repository\ColorRepository;
use App\Repository\ImageRepository;
use App\Repository\ItemColorRepository;
use App\Repository\ItemRepository;
use App\Repository\ItemSizeRepository;
use App\Repository\SizeRepository;
use App\Service\ImageUploadHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route({
 *     "en": "/items",
 *     "hr": "/artikli"
 * })
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
            'items' => $itemRepository->findBy([], ['id' => 'DESC']),
        ]);
    }

    /**
     * @Route({
     *     "en": "/new",
     *     "hr": "/novi"
     * }, name="item_new", methods={"GET","POST"})
     */
    public function new(Request $request, ImageUploadHelper $uploadHelper,
                        TranslatorInterface $translator): Response
    {
        $item = new Item();
        $formNewItem = $this->createForm(ItemType::class, $item);
        $formNewItem->handleRequest($request);
        if ($formNewItem->isSubmitted() && $formNewItem->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $item->setCipher(uniqid());
            $titleHr = $formNewItem->get('title_hr')->getData();
            $titleEn = $formNewItem->get('title_en')->getData();
            $categories = $formNewItem->get('category')->getData();
            $tags = $formNewItem->get('tag')->getData();
            $sizes = $formNewItem->get('size')->getData();
            $colors = $formNewItem->get('color')->getData();
            $images = $formNewItem->get('image')->getData();
            $descriptionHr = $formNewItem->get('description_hr')->getData();
            $descriptionEn = $formNewItem->get('description_en')->getData();

            foreach($categories as $category) {
                $itemCategory = new ItemCategory();
                $itemCategory->setItem($item);
                $itemCategory->setCategory($category);
                $entityManager->persist($itemCategory);
            }
            if(!is_null($tags)) {
                $explodedTags = explode(PHP_EOL, $tags);
                foreach ($explodedTags as $tagName) {
                    $itemTag = new ItemTag();
                    $itemTag->setItem($item);
                    $itemTag->setTag($tagName);
                    $entityManager->persist($itemTag);
                }
            }
            foreach ($images as $image) {
                $newFileName = $uploadHelper->uploadItemImage($image);
                if(is_null($newFileName)) {
                    $this->addFlash('danger',
                        $translator->trans('flash_message.image_error',
                            [], 'item'));
                    return $this->redirectToRoute('item_new');
                }
                $image = new Image();
                $image->setPath($newFileName);
                $image->setItem($item);
                $entityManager->persist($image);
            }
            $languages = ['hr', 'en'];
            foreach($languages as $language) {
                $item->setLocale($language);
                    if($language == 'hr') {
                        $item->setTitle($titleHr);
                        $item->setDescription($descriptionHr);
                    } else {
                        $item->setTitle($titleEn);
                        $item->setDescription($descriptionEn);
                    }
                $entityManager->persist($item);
                $entityManager->flush();
            }

            if (count($sizes) != 0 || count($colors) != 0) {
                foreach($sizes as $size){
                    $itemSize = new ItemSize();
                    $itemSize->setItem($item);
                    $itemSize->setSize($size);
                    $entityManager->persist($itemSize);
                    $item->addItemSize($itemSize);
                    $entityManager->persist($item);
                }
                foreach($colors as $color){
                    $itemColor = new ItemColor();
                    $itemColor->setItem($item);
                    $itemColor->setColor($color);
                    $entityManager->persist($itemColor);
                    $item->addItemColor($itemColor);
                    $entityManager->persist($item);
                }
                $entityManager->flush();
                $this->addFlash('success',
                    $translator->trans('flash_message.enter_quantity',
                        [], 'item'));
                return $this->redirectToRoute('item_quantity_set',
                    ['id' => $item->getId()]);
            }

            $this->addFlash('success',
                $translator->trans('flash_message.item_added',
                    [], 'item'));
            return $this->redirectToRoute('item_index');
        }
        return $this->render('item/new_item.html.twig', [
            'item' => $item,
            'formNewItem' => $formNewItem->createView(),
        ]);
    }

    /**
     * @Route({
     *     "en": "/{id}/set/quantity",
     *     "hr": "/{id}/postavi/količinu"
     * }, name="item_quantity_set", methods={"GET","POST"})
     */
    public function setQuantity(Request $request, ItemRepository $itemRepository,
                                ItemSizeRepository $itemSizeRepository,
                                ItemColorRepository $itemColorRepository,
                                TranslatorInterface $translator): Response
    {
        $item = $itemRepository->findOneBy(['id' => $request->get('id')]);
        $itemSizes = $item->getItemSizes();
        $itemColors = $item->getItemColors();
        $entityManager = $this->getDoctrine()->getManager();
        $formQuantity = $this->createForm(QuantityType::class, null, [
            'itemSizes' => $itemSizes, 'itemColors' => $itemColors
        ]);
        $formQuantity->handleRequest($request);
        if($formQuantity->isSubmitted() && $formQuantity->isValid()) {
            $formData = $formQuantity->getData();
            $formKeys = array_keys($formData);
            foreach($formKeys as $key) {
                if(str_contains($key, "itemSize")) {
                    $exploded = explode('_', $key);
                    $itemSizeNew = $itemSizeRepository->findOneBy(['id'=>$exploded[1]]);
                    $itemSizeNew->setQuantity($formData[$key]);
                    $entityManager->persist($itemSizeNew);
                }
                if(str_contains($key, "itemColor")) {
                    $exploded = explode('_', $key);
                    $itemColorNew = $itemColorRepository->findOneBy(['id'=>$exploded[1]]);
                    $itemColorNew->setQuantity($formData[$key]);
                    $entityManager->persist($itemColorNew);
                }
            }
            $entityManager->flush();
            $this->addFlash('success',
                $translator->trans('flash_message.added_quantites',
                    [], 'item'));
            return $this->redirectToRoute('item_index');
        }
        return $this->render('item/set_quantity.html.twig',[
            'formSetQuantity' => $formQuantity->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="item_show", methods={"GET"})
     */
    public function show(Item $item): Response
    {
        return $this->render('item/show_item.html.twig', [
            'item' => $item,
        ]);
    }

    /**
     * @Route({
     *     "en": "/{id}/edit",
     *     "hr": "/{id}/uredi"
     * }, name="item_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Item $item,
                         TranslatorInterface $translator): Response
    {
        $formNewItem = $this->createForm(ItemType::class, $item, ['isEdit' => true]);
        $itemTitleTranslations = [];
        $itemDescriptionTranslations = [];
        foreach($item->getItemTranslations() as $itemTranslation) {
            if ($itemTranslation->getField() == 'title') {
                $itemTitleTranslations[$itemTranslation->getLocale()]
                    = $itemTranslation->getContent();
            } else if ($itemTranslation->getField() == 'description') {
                $itemDescriptionTranslations[$itemTranslation->getLocale()]
                    = $itemTranslation->getContent();
            }
        }
        $formNewItem->get('title_hr')->setData($itemTitleTranslations['hr']);
        $formNewItem->get('title_en')->setData($itemTitleTranslations['en']);
        $formNewItem->get('manufacturer')->setData($item->getManufacturer());
        $formNewItem->get('description_hr')->setData($itemDescriptionTranslations['hr']);
        $formNewItem->get('description_en')->setData($itemDescriptionTranslations['en']);
        $formNewItem->handleRequest($request);
        if ($formNewItem->isSubmitted() && $formNewItem->isValid()) {
            $itemTitleHr = $formNewItem->get('title_hr')->getData();
            $itemTitleEn = $formNewItem->get('title_en')->getData();
            $itemDescriptionHr = $formNewItem->get('description_hr')->getData();
            $itemDescriptionEn = $formNewItem->get('description_en')->getData();
            $entityManager = $this->getDoctrine()->getManager();
            $languages = ['hr', 'en'];
            foreach($languages as $language) {
                $item->setLocale($language);
                if($language == 'hr') {
                    $item->setTitle($itemTitleHr);
                    $item->setDescription($itemDescriptionHr);
                } else {
                    $item->setTitle($itemTitleEn);
                    $item->setDescription($itemDescriptionEn);
                }
                $entityManager->persist($item);
                $entityManager->flush();
            }
            if($request->getLocale() == 'hr') {
                $this->addFlash('success',
                    $translator->trans('flash_message.edit_message',
                        [
                            '%item_title%' => $itemTitleHr
                        ], 'item'));
            } else {
                $this->addFlash('success',
                    $translator->trans('flash_message.edit_message',
                        [
                            '%item_title%' => $itemTitleEn
                        ], 'item'));
            }

            return $this->redirectToRoute('item_index');
        }

        return $this->render('item/edit_item.html.twig', [
            'item' => $item,
            'formNewItem' => $formNewItem->createView(),
        ]);
    }

    /**
     * @Route({
     *     "en": "/{id}/add/images",
     *     "hr": "/{id}/dodaj/fotografije"
     * }, name="item_image_add", methods={"GET", "POST"})
     */
    public function addImage(Request $request,
                             ImageUploadHelper $uploadHelper,
                             ItemRepository $itemRepository,
                             TranslatorInterface $translator): Response
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
                        $translator->trans('flash_message.image_error',
                            [], 'item'));
                    return $this->redirectToRoute('item_edit', ['id'=>$item->getId()]);
                }
                $image = new Image();
                $image->setPath($newFileName);
                $image->setItem($item);
                $entityManager->persist($image);
            }
            $entityManager->flush();
            $this->addFlash('success',
                $translator->trans('flash_message.image_added',
                    [], 'item'));
            return $this->redirectToRoute('item_edit', ['id'=>$item->getId()]);
        }
        return $this->render('item/new_image.html.twig', [
            'item' => $item,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route({
     *     "en": "/{id}/delete/image",
     *     "hr": "/{id}/obriši/fotografiju"
     * }, name="item_image_delete", methods={"DELETE"})
     */
    public function deleteImage(Request $request, Image $image,
                                ImageRepository $imageRepository,
                                TranslatorInterface $translator): RedirectResponse
    {
        if ($this->isCsrfTokenValid('delete'.$image->getId(),
            $request->request->get('_token'))) {
            $image = $imageRepository->findOneBy(['id'=>$image->getId()]);
            $itemId = $image->getItem()->getId();
            unlink('../public/uploads/'.$image->getPath());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($image);
            $this->addFlash('danger',
                $translator->trans('flash_message.image_deleted',
                    [], 'item'));
            $entityManager->flush();
        }
        return $this->redirectToRoute('item_edit', ['id' => $itemId]);
    }

    /**
     * @Route({
     *     "en": "/{id}/add/categories",
     *     "hr": "/{id}/dodaj/kategorije"
     * }, name="item_add_category", methods={"GET","POST"})
     */
    public function addCategory(Request $request, ItemRepository $itemRepository,
                                TranslatorInterface $translator): Response
    {
        $item = $itemRepository->findOneBy(['id' => $request->get('id')]);
        $categories = $item->getItemCategories();
        $categoryNames = [];
        foreach($categories as $category) {
            array_push($categoryNames, $category->getCategory()->getName());
        }
        $form = $this->createForm(NewItemCategoryType::class, [],
            ['category_names' => $categoryNames]);
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
            $this->addFlash('success',
                $translator->trans('flash_message.category_added',
                    [], 'item'));
            return $this->redirectToRoute('item_edit', ['id' => $item->getId()]);
        }

        return $this->render('item/new_category.html.twig', [
            'item' => $item,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route({
     *     "en": "/{id}/delete/category",
     *     "hr": "/{id}/obriši/kategoriju"
     * }, name="item_category_delete", methods={"DELETE"})
     */
    public function deleteCategory(Request $request, ItemCategory $itemCategory,
                                   TranslatorInterface $translator): RedirectResponse
    {
        if ($this->isCsrfTokenValid('delete'.$itemCategory->getId(), $request->request->get('_token'))) {
            $itemId = $itemCategory->getItem()->getId();
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($itemCategory);
            $this->addFlash('danger',
                $translator->trans('flash_message.category_deleted',
                    [
                        '%category_name%' => $itemCategory->getCategory()->getName()
                    ], 'item'));
            $entityManager->flush();
        }
        return $this->redirectToRoute('item_edit', ['id' => $itemId]);
    }

    /**
     * @Route({
     *     "en": "/{id}/add/tags",
     *     "hr": "/{id}/dodaj/tagove"
     * }, name="item_add_tag", methods={"GET","POST"})
     */
    public function addTag(Request $request, ItemRepository $itemRepository,
                           TranslatorInterface $translator): Response
    {
        $entityManager=$this->getDoctrine()->getManager();
        $item = $itemRepository->findOneBy(['id' => $request->get('id')]);
        $itemTags = [];
        foreach($item->getItemTags() as $itemTag) {
            array_push($itemTags, rtrim($itemTag->getTag()));
        }
        $form = $this->createForm(NewItemTagType::class);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $tags = $form->get('tag')->getData();
            if(!is_null($tags)) {
                $explodedTags = explode(PHP_EOL, $tags);
                foreach ($explodedTags as $tagName) {
                    if(in_array($tagName, $itemTags)) {
                        $this->addFlash('danger',
                            $translator->trans('flash_message.tag_error',
                                [], 'item'));
                        return $this->redirectToRoute('item_add_tag',
                            ['id' => $item->getId()]);
                    } else {
                        $itemTag = new ItemTag();
                        $itemTag->setItem($item);
                        $itemTag->setTag($tagName);
                        $entityManager->persist($itemTag);
                    }
                }
            }
            $entityManager->flush();
            $this->addFlash('success',
                $translator->trans('flash_message.tag_added',
                    [], 'item'));
            return $this->redirectToRoute('item_edit', ['id' => $item->getId()]);
        }
        return $this->render('item/new_tag.html.twig', [
            'item' => $item,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route({
     *     "en": "/{id}/delete/tag",
     *     "hr": "/{id}/obriši/tag"
     * }, name="item_tag_delete", methods={"DELETE"})
     */
    public function deleteTag(Request $request, ItemTag $itemTag,
                              TranslatorInterface $translator): RedirectResponse
    {
        if ($this->isCsrfTokenValid('delete'.$itemTag->getId(), $request->request->get('_token'))) {
            $itemId = $itemTag->getItem()->getId();
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($itemTag);
            $this->addFlash('danger',
                $translator->trans('flash_message.tag_deleted',
                    [
                        '%tag%' => $itemTag->getTag()
                    ], 'item'));
            $entityManager->flush();
        }
        return $this->redirectToRoute('item_edit', ['id' => $itemId]);
    }

    /**
     * @Route({
     *     "en": "/{id}/add/colors",
     *     "hr": "/{id}/dodaj/boje"
     * }, name="item_add_color", methods={"GET","POST"})
     */
    public function addColor(Request $request, ItemRepository $itemRepository): Response
    {
        $item = $itemRepository->findOneBy(['id'=>$request->get('id')]);
        $colors = $item->getItemColors();
        $colorValues = [];
        foreach($colors as $color) {
            array_push($colorValues, $color->getColor()->getValue());
        }
        $form = $this->createForm(NewItemColorType::class, null,
            ['color_values' => $colorValues]);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $selectedColors = $form->get('color')->getData();
            $session = new Session();
            $session->set('colors', $selectedColors);
            $session->set('item', $item);
            return $this->redirectToRoute('item_color_set_quantity');
        }
        return $this->render('item/new_color.html.twig', [
            'item' => $item,
            'form' => $form->createView(),
        ]);
    }

    /**
     *@Route({
     *     "en": "/set/colors/quantity",
     *     "hr": "/postavi/količinu/boja"
     * }, name="item_color_set_quantity", methods={"GET", "POST"})
     */
    public function setNewColorQuantity(Request $request,
                                        ColorRepository $colorRepository,
                                        TranslatorInterface $translator): Response
    {
        $markedColors = [];
        $colorQuantities = [];
        $colors = $this->get('session')->get('colors');
        $item = $this->get('session')->get('item');
        $form = $this->createForm(ColorQuantityType::class, null,
            ['colors' => $colors]);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            foreach ($colors as $color) {
                array_push($markedColors, $color->getId());
            }
            $formData = $form->getData();
            $formKeys = array_keys($formData);

            foreach ($formKeys as $key) {
                if (str_contains($key, "color")) {
                    array_push($colorQuantities, $formData[$key]);
                }
            }
            $colorsDone = array_combine($markedColors, $colorQuantities);

            foreach ($colorsDone as $key => $value) {
                $colorObj = $colorRepository->findOneBy(['id' => $key]);
                $itemColor = new ItemColor();
                $itemColor->setItem($item);
                $itemColor->setColor($colorObj);
                $itemColor->setQuantity($value);
                $entityManager->merge($itemColor);
            }
            $entityManager->flush();
            $this->addFlash('success',
                $translator->trans('flash_message.color_added',
                    [], 'item'));
            return $this->redirectToRoute('item_edit', ['id' => $item->getId()]);
        }
        return $this->render('item/set_color_quantity.html.twig', [
            'item' => $item,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route({
     *     "en": "/edit/color/{id}/quantity",
     *     "hr": "/uredi/količinu/{id}/boje"
     * }, name="item_color_quantity_edit", methods={"GET", "POST"})
     */
    public function editColorQuantity(Request $request,
                                      ItemColorRepository $itemColorRepository,
                                      TranslatorInterface $translator): Response
    {
        $itemColor = $itemColorRepository->findOneBy(['id' => $request->get('id')]);
        $form = $this->createForm(EditColorQuantityType::class, $itemColor);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success',
                $translator->trans('flash_message.color_edited',
                    [
                        '%color_name%' => $itemColor->getColor()->getName()
                    ], 'item'));
            return $this->redirectToRoute('item_edit', ['id' => $itemColor->getItem()->getId()]);
        }
        return $this->render('item/edit_color_quantity.html.twig', [
            'form' => $form->createView(),
            'itemColor' => $itemColor
        ]);
    }

    /**
     * @Route({
     *     "en": "/{id}/delete/color",
     *     "hr": "/{id}/obriši/boju"
     * }, name="item_color_delete", methods={"DELETE"})
     */
    public function deleteColor(Request $request, ItemColor $itemColor,
                                TranslatorInterface $translator): RedirectResponse
    {
        if ($this->isCsrfTokenValid('delete'.$itemColor->getId(),
            $request->request->get('_token'))) {
            $itemId = $itemColor->getItem()->getId();
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($itemColor);
            $this->addFlash('danger',
                $translator->trans('flash_message.color_deleted',
                    [], 'item'));
            $entityManager->flush();
        }
        return $this->redirectToRoute('item_edit', ['id' => $itemId]);
    }


    /**
     * @Route({
     *     "en": "/{id}/add/sizes",
     *     "hr": "/{id}/dodaj/veličine"
     * }, name="item_add_size", methods={"GET","POST"})
     */
    public function addSize(Request $request, ItemRepository $itemRepository): Response
    {
        $item = $itemRepository->findOneBy(['id'=>$request->get('id')]);
        $sizes = $item->getItemSizes();
        $sizeValues = [];
        foreach($sizes as $size) {
            array_push($sizeValues, $size->getSize()->getValue());
        }
        $form = $this->createForm(NewItemSizeType::class, null,
            ['size_values' => $sizeValues]);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $selectedSizes = $form->get('size')->getData();
            $session = new Session();
            $session->set('sizes', $selectedSizes);
            $session->set('item', $item);
            return $this->redirectToRoute('item_size_set_quantity');
        }
        return $this->render('item/new_size.html.twig', [
            'item' => $item,
            'form' => $form->createView(),
        ]);
    }

    /**
     *@Route({
     *     "en": "/set/sizes/quantity",
     *     "hr": "/postavi/količinu/veličina"
     * }, name="item_size_set_quantity", methods={"GET", "POST"})
     */
    public function setNewSizeQuantity(Request $request,
                                       SizeRepository $sizeRepository,
                                       TranslatorInterface $translator): Response
    {
        $markedSizes = [];
        $sizeQuantities = [];
        $sizes = $this->get('session')->get('sizes');
        $item = $this->get('session')->get('item');
        $form = $this->createForm(SizeQuantityType::class, null,
            ['sizes' => $sizes]);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            foreach ($sizes as $size) {
                array_push($markedSizes, $size->getId());
            }
            $formData = $form->getData();
            $formKeys = array_keys($formData);

            foreach ($formKeys as $key) {
                if (str_contains($key, "size")) {
                    array_push($sizeQuantities, $formData[$key]);
                }
            }
            $colorsDone = array_combine($markedSizes, $sizeQuantities);

            foreach ($colorsDone as $key => $value) {
                $sizeObj = $sizeRepository->findOneBy(['id' => $key]);
                $itemSize = new ItemSize();
                $itemSize->setItem($item);
                $itemSize->setSize($sizeObj);
                $itemSize->setQuantity($value);
                $entityManager->merge($itemSize);
            }
            $entityManager->flush();
            $this->addFlash('success',
                $translator->trans('flash_message.size_added',
                    [], 'item'));
            return $this->redirectToRoute('item_edit', ['id' => $item->getId()]);
        }
        return $this->render('item/set_size_quantity.html.twig', [
            'item' => $item,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route({
     *     "en": "/edit/size/{id}/quantity",
     *     "hr": "/uredi/količinu/{id}/veličine"
     * }, name="item_size_quantity_edit", methods={"GET", "POST"})
     */
    public function editSizeQuantity(Request $request,
                                     ItemSizeRepository $itemSizeRepository,
                                     TranslatorInterface $translator)
    {
        $itemSize = $itemSizeRepository->findOneBy(['id' => $request->get('id')]);
        $form = $this->createForm(EditSizeQuantityType::class, $itemSize);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success',
                $translator->trans('flash_message.size_edited',
                    [
                        '%size_value%' => $itemSize->getSize()->getValue()
                    ], 'item'));
            return $this->redirectToRoute('item_edit',
                ['id'=>$itemSize->getItem()->getId()]);
        }
        return $this->render('item/edit_size_quantity.html.twig', [
            'form' => $form->createView(),
            'itemSize' => $itemSize
        ]);
    }

    /**
     * @Route({
     *     "en": "/{id}/delete/size",
     *     "hr": "/{id}/obriši/veličinu"
     * }, name="item_size_delete", methods={"DELETE"})
     */
    public function deleteSize(Request $request, ItemSize $itemSize,
                               TranslatorInterface $translator): RedirectResponse
    {
        if($this->isCsrfTokenValid('delete'.$itemSize->getId(),
            $request->request->get('_token'))) {
            $itemId = $itemSize->getItem()->getId();
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($itemSize);
            $this->addFlash('danger',
                $translator->trans('flash_message.size_deleted',
                    [], 'item'));
            $entityManager->flush();
        }
        return $this->redirectToRoute('item_edit', ['id' => $itemId]);
    }

    /**
     * @Route("/{id}", name="item_delete", methods={"DELETE"})
     */
    public function deleteItem(Request $request, Item $item,
                               ImageRepository $imageRepository,
                               TranslatorInterface $translator): Response
    {
        if ($this->isCsrfTokenValid('delete'.$item->getId(),
            $request->request->get('_token'))) {
            $images = $imageRepository->findBy(['item'=>$item]);
            if($images !== null) {
                foreach($images as $image) {
                    unlink('../public/uploads/'.$image->getPath());
                }
            }
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($item);
            $this->addFlash('danger',
                $translator->trans('flash_message.item_deleted',
                    [
                        '%item_title%' => $item->getTitle()
                    ], 'item'));
            $entityManager->flush();
        }
        return $this->redirectToRoute('item_index');
    }
}
