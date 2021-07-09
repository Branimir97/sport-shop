<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageUploadHelper
{
    private $uploadsPath;

    /**
     * ImageUploadHelper constructor.
     * @param $uploadsPath
     */
    public function __construct($uploadsPath)
    {
        $this->uploadsPath = $uploadsPath;
    }

    public function uploadItemImage(UploadedFile $uploadedFile): ?string
    {
        if(!file_exists('/var/www/sport-shop/public/uploads')) {
            mkdir('/var/www/sport-shop/public/uploads');
        }
        $destination = $this->uploadsPath;
        $originalFileName = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
        if(!in_array($uploadedFile->getClientOriginalExtension(), ['jpg', 'jpeg', 'png'])) {
            return null;
        }
        $newFileName = $originalFileName.'-'.uniqid().'.'.$uploadedFile->guessExtension();
        $uploadedFile->move($destination, $newFileName);
        return $newFileName;
    }
}