<?php


namespace App\Service;


use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageUploadHelper
{
    private $uploadsPath;
    private $temporaryUploadsPath;

    /**
     * ImageUploadHelper constructor.
     * @param $uploadsPath
     * @param $temporaryUploadsPath
     */
    public function __construct($uploadsPath, $temporaryUploadsPath)
    {
        $this->uploadsPath = $uploadsPath;
        $this->temporaryUploadsPath = $temporaryUploadsPath;
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

    public function uploadImageTemporary(UploadedFile $uploadedFile): ?string
    {
        if(!file_exists('/var/www/sport-shop/public/uploads/temporary')) {
            mkdir('/var/www/sport-shop/public/uploads/temporary');
        }
        $destination = $this->temporaryUploadsPath;
        $originalFileName = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
        if(!in_array($uploadedFile->getClientOriginalExtension(), ['jpg', 'jpeg', 'png'])) {
            return null;
        }
        $newFileName = $originalFileName.'-'.uniqid().'.'.$uploadedFile->guessExtension();
        $uploadedFile->move($destination, $newFileName);
        return $newFileName;
    }

    public function uploadImageFromTemporaryFolderAndGetImagesList() {
        $directory = '/var/www/sport-shop/public/uploads/temporary';
        $directoryFiles = array_diff(scandir($directory), array('..', '.'));
        foreach($directoryFiles as $image) {
            copy("$directory/$image", "/var/www/sport-shop/public/uploads/$image");
            unlink("$directory/$image");
        }
        return $directoryFiles;
    }
}