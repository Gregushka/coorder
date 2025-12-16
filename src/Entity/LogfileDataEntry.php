<?php

namespace App\Entity;

use App\Repository\LogfileDataEntryRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;

#[ORM\Entity(repositoryClass: LogfileDataEntryRepository::class)]
class LogfileDataEntry
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?Uuid $id = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 8)]
    private ?string $latitude = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 11, scale: 8)]
    private ?string $longitude = null;

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $speed = null;

    #[ORM\Column(type: Types::TIME_IMMUTABLE, precision: 3)]
    private ?\DateTimeImmutable $utcTime = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $gpsQualityIndicator = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $numberOfSatellites = null;

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $hdop = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $altitude = null;

    #[ORM\Column(length: 1, nullable: true)]
    private ?string $altitudeUnits = null;

    #[ORM\Column(length: 1)]
    private ?string $status = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $date = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $checksum = null;

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $magneticVariation = null;

    #[ORM\Column(length: 1, nullable: true)]
    private ?string $magneticVariationDirection = null;

    #[ORM\Column(length: 1, nullable: true)]
    private ?string $modeIndicator = null;

    #[ORM\Column(length: 36, unique: true)]
    private ?string $heavyName = null;

    #[ORM\Column(type: Types::JSON)]
    private array $permissions = [];

    #[ORM\ManyToOne(targetEntity: MediaCard::class, inversedBy: 'logfileDataEntries')]
    #[ORM\JoinColumn(nullable: false)]
    private ?MediaCard $mediaCard = null;

    public function __construct()
    {
        $this->heavyName = Uuid::v4()->toRfc4122();
    }

    // Getters and setters for all fields
    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    public function setLatitude(string $latitude): static
    {
        $this->latitude = $latitude;
        return $this;
    }

    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    public function setLongitude(string $longitude): static
    {
        $this->longitude = $longitude;
        return $this;
    }

    public function getSpeed(): ?float
    {
        return $this->speed;
    }

    public function setSpeed(?float $speed): static
    {
        $this->speed = $speed;
        return $this;
    }

    public function getUtcTime(): ?\DateTimeImmutable
    {
        return $this->utcTime;
    }

    public function setUtcTime(\DateTimeImmutable $utcTime): static
    {
        $this->utcTime = $utcTime;
        return $this;
    }

    public function getGpsQualityIndicator(): ?int
    {
        return $this->gpsQualityIndicator;
    }

    public function setGpsQualityIndicator(int $gpsQualityIndicator): static
    {
        $this->gpsQualityIndicator = $gpsQualityIndicator;
        return $this;
    }

    public function getNumberOfSatellites(): ?int
    {
        return $this->numberOfSatellites;
    }

    public function setNumberOfSatellites(?int $numberOfSatellites): static
    {
        $this->numberOfSatellites = $numberOfSatellites;
        return $this;
    }

    public function getHdop(): ?float
    {
        return $this->hdop;
    }

    public function setHdop(?float $hdop): static
    {
        $this->hdop = $hdop;
        return $this;
    }

    public function getAltitude(): ?int
    {
        return $this->altitude;
    }

    public function setAltitude(?int $altitude): static
    {
        $this->altitude = $altitude;
        return $this;
    }

    public function getAltitudeUnits(): ?string
    {
        return $this->altitudeUnits;
    }

    public function setAltitudeUnits(?string $altitudeUnits): static
    {
        $this->altitudeUnits = $altitudeUnits;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): static
    {
        $this->date = $date;
        return $this;
    }

    public function getChecksum(): ?string
    {
        return $this->checksum;
    }

    public function setChecksum(?string $checksum): static
    {
        $this->checksum = $checksum;
        return $this;
    }

    public function getMagneticVariation(): ?float
    {
        return $this->magneticVariation;
    }

    public function setMagneticVariation(?float $magneticVariation): static
    {
        $this->magneticVariation = $magneticVariation;
        return $this;
    }

    public function getMagneticVariationDirection(): ?string
    {
        return $this->magneticVariationDirection;
    }

    public function setMagneticVariationDirection(?string $magneticVariationDirection): static
    {
        $this->magneticVariationDirection = $magneticVariationDirection;
        return $this;
    }

    public function getModeIndicator(): ?string
    {
        return $this->modeIndicator;
    }

    public function setModeIndicator(?string $modeIndicator): static
    {
        $this->modeIndicator = $modeIndicator;
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
}