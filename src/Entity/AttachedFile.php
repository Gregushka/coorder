<?php

namespace App\Entity;

use App\Repository\AttachedFileRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;

#[ORM\Entity(repositoryClass: AttachedFileRepository::class)]
#[ORM\HasLifecycleCallbacks]
class AttachedFile
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?Uuid $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 64)]
    private ?string $hash = null;

    #[ORM\Column(length: 100)]
    private ?string $mimeType = null;

    #[ORM\Column(length: 100)]
    private ?string $mimeSubtype = null;

    #[ORM\Column(type: Types::BIGINT)]
    private ?int $size = null;

    #[ORM\Column(length: 500)]
    private ?string $path = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private array $exif = [];

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(length: 36, unique: true)]
    private ?string $heavyName = null;

    #[ORM\Column(type: Types::JSON)]
    private array $permissions = [];

    #[ORM\ManyToOne(targetEntity: MediaCard::class, inversedBy: 'attachedFiles')]
    #[ORM\JoinColumn(nullable: false)]
    private ?MediaCard $mediaCard = null;

    public function __construct()
    {
        $this->heavyName = Uuid::v4()->toRfc4122();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function updateTimestamps(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    // Getters and setters
    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getHash(): ?string
    {
        return $this->hash;
    }

    public function setHash(string $hash): static
    {
        $this->hash = $hash;
        return $this;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(string $mimeType): static
    {
        $this->mimeType = $mimeType;
        return $this;
    }

    public function getMimeSubtype(): ?string
    {
        return $this->mimeSubtype;
    }

    public function setMimeSubtype(string $mimeSubtype): static
    {
        $this->mimeSubtype = $mimeSubtype;
        return $this;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function setSize(int $size): static
    {
        $this->size = $size;
        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path): static
    {
        $this->path = $path;
        return $this;
    }

    public function getExif(): array
    {
        return $this->exif;
    }

    public function setExif(?array $exif): static
    {
        $this->exif = $exif ?? [];
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getHeavyName(): ?string
    {
        return $this->heavyName;
    }

    public function setHeavyName(string $heavyName): static
    {
        $this->heavyName = $heavyName;
        return $this;
    }

    public function getPermissions(): array
    {
        return $this->permissions;
    }

    public function setPermissions(array $permissions): static
    {
        $this->permissions = $permissions;
        return $this;
    }

    public function getMediaCard(): ?MediaCard
    {
        return $this->mediaCard;
    }

    public function setMediaCard(?MediaCard $mediaCard): static
    {
        $this->mediaCard = $mediaCard;
        return $this;
    }

    /**
     * Get the full MIME type (type/subtype)
     */
    public function getFullMimeType(): string
    {
        return $this->mimeType . '/' . $this->mimeSubtype;
    }

    /**
     * Get file extension from name
     */
    public function getExtension(): string
    {
        return pathinfo($this->name, PATHINFO_EXTENSION);
    }

    /**
     * Get filename without extension
     */
    public function getBasename(): string
    {
        return pathinfo($this->name, PATHINFO_FILENAME);
    }
}