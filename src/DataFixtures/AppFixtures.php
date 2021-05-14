<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Color;
use App\Entity\Manufacturer;
use App\Entity\Size;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {

        $user = new User();
        $user->setName("Branimir");
        $user->setSurname("Butković");
        $user->setEmail("branimir@gmail.com");
        $user->setRoles(["ROLE_USER", "ROLE_ADMIN"]);
        $user->setBirthDate(new \DateTime("1997-10-27"));
        $user->setGender("Muški");
        $user->setPassword('$argon2id$v=19$m=65536,t=4,p=1$avj5HbJl56Te5US1YZiQAQ$T9qpVqy9QHEokQZya9zJLjpHsS0pqh8aqFRHZkKcMOI');

        $manager->persist($user);

        $categories = [
            "Muškarci",
            "Žene",
            "Djeca",
            "Sportska oprema",
            "Ostalo",
            "Obuća",
            "Odjeća",
            "Tenisice",
            "Kopačke",
            "Patike za trčanje",
            "Čizme",
            "Dodaci za obuću",
            "Ulošci",
            "Vezice",
            "Impregnacijski sprejevi",
            "Majice",
            "Hlače",
            "Jakne",
            "Odjevni dodaci",
            "Kape",
            "Rukavice",
            "Šalovi",
            "Nogomet",
            "Rukomet",
            "Košarka",
            "Odbojka",
            "Stolni tenis",
            "Steznici",
            "Znojnici",
            "Ruksaci",
            "Čarape"
        ];

        $sizesClothes = [
            "XS",
            "S",
            "M",
            "L",
            "XL",
            "XXL",
            "XXXL"
        ];

        $sizesFootWear = [
            "30",
            "31",
            "32",
            "33",
            "34",
            "35",
            "36",
            "37",
            "38",
            "39",
            "40",
            "41",
            "42",
            "43",
            "44",
            "45",
            "46",
            "47",
        ];

        $colors = [
            "#7E4539",
            "#397E55",
            "#C4E4D1",
            "#DEC4E4",
            "#FF0000",
            "#0101DF",
            "#FFBF00",
            "#F781F3",
            "#084B8A",
            "#CEF6EC",
            "#1B0A2A"
        ];

        $manufacturers = [
          "Nike",
          "Adidas",
          "Umbro",
          "Vty",
          "Puma",
          "Rebook",
          "Fila",
          "Converse",
          "Levi's",
          "Lotto",
          "Crocs",
          "Carrera",
          "Diadora",
          "Hummel",
          "Lacoste",
          "Timberland",
          "Salomon",
          "Skechers"
        ];

        foreach($categories as $categoryName) {
            $category = new Category();
            $category->setName($categoryName);
            $manager->persist($category);
        }

        foreach($sizesClothes as $size) {
            $sizeObj = new Size();
            $sizeObj->setValue($size);
            $sizeObj->setType("Odjeća");
            $manager->persist($sizeObj);
        }

        foreach($sizesFootWear as $size) {
            $sizeObj = new Size();
            $sizeObj->setValue($size);
            $sizeObj->setType("Obuća");
            $manager->persist($sizeObj);
        }

        foreach($colors as $color) {
            $colorObj = new Color();
            $colorObj->setValue($color);
            $manager->persist($colorObj);
        }

        foreach($manufacturers as $manufacturer) {
            $manufacturerObj = new Manufacturer();
            $manufacturerObj->setName($manufacturer);
            $manager->persist($manufacturerObj);
        }

        $manager->flush();
    }
}
