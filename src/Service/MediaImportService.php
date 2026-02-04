<?php

namespace App\Service;

use App\Entity\AttachedFile;
use App\Entity\MediaCard;
use App\Entity\Trip;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Mime\MimeTypes;
use Symfony\Component\String\Slugger\SluggerInterface;

class MediaImportService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SluggerInterface $slugger,
        #[Autowire('%uploads_directory%')] private string $uploadsDirectory
    ) {}

    /**
     * @throws \Exception
     */
    public function importMedia(UploadedFile $file, Trip $trip, ?string $userMediaName): void
    {
        // ... (File upload logic remains the same) ...
        
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();
        $fullPath = $this->uploadsDirectory . '/' . $newFilename;

        // 1. Move file
        $file->move($this->uploadsDirectory, $newFilename);

        // 2. Create AttachedFile Entity
        $attachedFile = new AttachedFile();
        $attachedFile->setName($file->getClientOriginalName()); // store original name
        $attachedFile->setPath($newFilename);
        $attachedFile->setSize(filesize($fullPath));
        $attachedFile->setHash(md5_file($fullPath));
        $attachedFile->setUpdatedAt(\DateTimeImmutable::createFromFormat('U', (string)filemtime($fullPath)));

        // Detect Mime Type
        $mimeTypes = new MimeTypes();
        $mimeType = $mimeTypes->guessMimeType($fullPath);
        if ($mimeType) {
            $parts = explode('/', $mimeType);
            $attachedFile->setMimeType($parts[0]);
            $attachedFile->setMimeSubtype($parts[1] ?? '');
        }

        // Extract EXIF (suppress warnings if no exif data)
        $exifData = @exif_read_data($fullPath);
        $attachedFile->setExif($exifData ?: []);

        // ... (XML Parsing Logic) ...

        $xmlContent = file_get_contents($fullPath);
        $xml = simplexml_load_string($xmlContent);

        if ($xml === false) {
            throw new \Exception("The uploaded file is not a valid XML.");
        }

        $mediaCard = new MediaCard();
        $mediaCard->setTrip($trip);
        $mediaCard->setName($userMediaName); // User input name
        
        // Parse "MediaProfile" attributes
        if (isset($xml['createdAt'])) {
            $mediaCard->setMediaCreated(new \DateTimeImmutable((string)$xml['createdAt']));
        }

        // Accessing Properties -> System
        if (isset($xml->Properties->System)) {
            $mediaCard->setSystemId((string)$xml->Properties->System['systemId']);
            $mediaCard->setSystemKind((string)$xml->Properties->System['systemKind']);
            $mediaCard->setMasterVersion((string)$xml->Properties->System['masterVersion']);
        }

        // Accessing Properties -> Attached
        if (isset($xml->Properties->Attached)) {
            $mediaCard->setMediaId((string)$xml->Properties->Attached['mediaId']);
            $mediaCard->setMediaKind((string)$xml->Properties->Attached['mediaKind']);
            $mediaCard->setMediaName((string)$xml->Properties->Attached['mediaName']);
        }

        // --- UPDATED CONTENTS PARSING ---
        $contents = [];
        if (isset($xml->Contents->Material)) {
            foreach ($xml->Contents->Material as $material) {
                // 1. Capture ALL attributes of the Material tag
                $materialItem = [
                    'uri'         => (string)$material['uri'],
                    'type'        => (string)$material['type'],
                    'videoType'   => (string)$material['videoType'],
                    'audioType'   => (string)$material['audioType'],
                    'fps'         => (string)$material['fps'],
                    'dur'         => (string)$material['dur'],
                    'ch'          => (string)$material['ch'],
                    'aspectRatio' => (string)$material['aspectRatio'],
                    'offset'      => (string)$material['offset'],
                    'umid'        => (string)$material['umid'],
                    'relevantInfo'=> [] // Placeholder for nested children
                ];

                // 2. Iterate through nested RelevantInfo children
                if (isset($material->RelevantInfo)) {
                    foreach ($material->RelevantInfo as $info) {
                        $materialItem['relevantInfo'][] = [
                            'uri'  => (string)$info['uri'],
                            'type' => (string)$info['type'],
                        ];
                    }
                }

                $contents[] = $materialItem;
            }
        }
        $mediaCard->setContents($contents);
        // ---------------------------------

        // 4. Persist Relations
        $attachedFile->setMediaCard($mediaCard);

        $this->entityManager->persist($mediaCard);
        $this->entityManager->persist($attachedFile);
        $this->entityManager->flush();
    }
}