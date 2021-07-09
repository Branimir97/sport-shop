<?php

namespace App\Entity;

use App\Repository\ColorTranslationRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Entity\MappedSuperclass\AbstractPersonalTranslation;

/**
 * @ORM\Entity(repositoryClass=ColorTranslationRepository::class)
 */
class ColorTranslation extends AbstractPersonalTranslation
{
    /**
     * @ORM\ManyToOne(targetEntity=Color::class, inversedBy="colorTranslations")
     */
    protected $object;

    /**
     * ColorTranslation constructor.
     * @param string $locale
     * @param string $field
     * @param string $value
     */
    public function __construct(string $locale, string $field, string $value)
    {
        $this->setLocale($locale);
        $this->setField($field);
        $this->setContent($value);
    }
}
