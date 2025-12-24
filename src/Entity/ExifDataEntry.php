<?php

namespace App\Entity;

use App\Repository\ExifDataEntryRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;

#[ORM\Entity(repositoryClass: ExifDataEntryRepository::class)]
class ExifDataEntry
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?Uuid $id = null;

    #[ORM\Column(type: Types::FLOAT)]
    private ?float $sampleTime = null; // 18.14 s

    #[ORM\Column(type: Types::FLOAT)]
    private ?float $sampleDuration = null; // 0.02 s

    #[ORM\Column(type: Types::FLOAT)]
    private ?float $fNumber = null; // 2.8

    #[ORM\Column(type: Types::STRING, length: 50)]
    private ?string $exposureTime = null; // 1/1750 (store as string)

    #[ORM\Column(type: Types::FLOAT)]
    private ?float $masterGainAdjustment = null; // 0.00 dB

    #[ORM\Column(type: Types::INTEGER)]
    private ?int $electricalExtenderMagnification = null; // 100

    #[ORM\Column(length: 50)]
    private ?string $gpsVersionId = null; // 2.2.0.0

    #[ORM\Column(length: 10)]
    private ?string $gpsLatitudeRef = null; // North

    #[ORM\Column(type: Types::STRING, length: 255)]
    private ?string $gpsLatitude = null; // 36 deg 33' 29.96" (store as string)

    #[ORM\Column(length: 10)]
    private ?string $gpsLongitudeRef = null; // East

    #[ORM\Column(type: Types::STRING, length: 255)]
    private ?string $gpsLongitude = null; // 30 deg 33' 51.80" (store as string)

    #[ORM\Column(type: Types::TIME_IMMUTABLE, precision: 3)]
    private ?\DateTimeImmutable $gpsTimeStamp = null; // 08:22:01.093

    #[ORM\Column(length: 50)]
    private ?string $gpsStatus = null; // Measurement Active

    #[ORM\Column(length: 50)]
    private ?string $gpsMeasureMode = null; // 3-Dimensional Measurement

    #[ORM\Column(length: 50)]
    private ?string $gpsMapDatum = null; // WGS-84

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $gpsDateStamp = null; // 2024:12:20

    #[ORM\Column(length: 50)]
    private ?string $whiteBalance = null; // Preset

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $dateTime = null; // 2024:12:20 12:21:58

    #[ORM\Column(length: 36, unique: true)]
    private ?string $heavyName = null;

    #[ORM\Column(type: Types::JSON)]
    private array $permissions = [];

    public function __construct()
    {
        $this->heavyName = Uuid::v4()->toRfc4122();
    }

    // Getters and setters for all fields
    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getSampleTime(): ?float
    {
        return $this->sampleTime;
    }

    public function setSampleTime(float $sampleTime): static
    {
        $this->sampleTime = $sampleTime;
        return $this;
    }

    public function getSampleDuration(): ?float
    {
        return $this->sampleDuration;
    }

    public function setSampleDuration(float $sampleDuration): static
    {
        $this->sampleDuration = $sampleDuration;
        return $this;
    }

    public function getFNumber(): ?float
    {
        return $this->fNumber;
    }

    public function setFNumber(float $fNumber): static
    {
        $this->fNumber = $fNumber;
        return $this;
    }

    public function getExposureTime(): ?string
    {
        return $this->exposureTime;
    }

    public function setExposureTime(string $exposureTime): static
    {
        $this->exposureTime = $exposureTime;
        return $this;
    }

    public function getMasterGainAdjustment(): ?float
    {
        return $this->masterGainAdjustment;
    }

    public function setMasterGainAdjustment(float $masterGainAdjustment): static
    {
        $this->masterGainAdjustment = $masterGainAdjustment;
        return $this;
    }

    public function getElectricalExtenderMagnification(): ?int
    {
        return $this->electricalExtenderMagnification;
    }

    public function setElectricalExtenderMagnification(int $electricalExtenderMagnification): static
    {
        $this->electricalExtenderMagnification = $electricalExtenderMagnification;
        return $this;
    }

    public function getGpsVersionId(): ?string
    {
        return $this->gpsVersionId;
    }

    public function setGpsVersionId(string $gpsVersionId): static
    {
        $this->gpsVersionId = $gpsVersionId;
        return $this;
    }

    public function getGpsLatitudeRef(): ?string
    {
        return $this->gpsLatitudeRef;
    }

    public function setGpsLatitudeRef(string $gpsLatitudeRef): static
    {
        $this->gpsLatitudeRef = $gpsLatitudeRef;
        return $this;
    }

    public function getGpsLatitude(): ?string
    {
        return $this->gpsLatitude;
    }

    public function setGpsLatitude(string $gpsLatitude): static
    {
        $this->gpsLatitude = $gpsLatitude;
        return $this;
    }

    public function getGpsLongitudeRef(): ?string
    {
        return $this->gpsLongitudeRef;
    }

    public function setGpsLongitudeRef(string $gpsLongitudeRef): static
    {
        $this->gpsLongitudeRef = $gpsLongitudeRef;
        return $this;
    }

    public function getGpsLongitude(): ?string
    {
        return $this->gpsLongitude;
    }

    public function setGpsLongitude(string $gpsLongitude): static
    {
        $this->gpsLongitude = $gpsLongitude;
        return $this;
    }

    public function getGpsTimeStamp(): ?\DateTimeImmutable
    {
        return $this->gpsTimeStamp;
    }

    public function setGpsTimeStamp(\DateTimeImmutable $gpsTimeStamp): static
    {
        $this->gpsTimeStamp = $gpsTimeStamp;
        return $this;
    }

    public function getGpsStatus(): ?string
    {
        return $this->gpsStatus;
    }

    public function setGpsStatus(string $gpsStatus): static
    {
        $this->gpsStatus = $gpsStatus;
        return $this;
    }

    public function getGpsMeasureMode(): ?string
    {
        return $this->gpsMeasureMode;
    }

    public function setGpsMeasureMode(string $gpsMeasureMode): static
    {
        $this->gpsMeasureMode = $gpsMeasureMode;
        return $this;
    }

    public function getGpsMapDatum(): ?string
    {
        return $this->gpsMapDatum;
    }

    public function setGpsMapDatum(string $gpsMapDatum): static
    {
        $this->gpsMapDatum = $gpsMapDatum;
        return $this;
    }

    public function getGpsDateStamp(): ?\DateTimeImmutable
    {
        return $this->gpsDateStamp;
    }

    public function setGpsDateStamp(\DateTimeImmutable $gpsDateStamp): static
    {
        $this->gpsDateStamp = $gpsDateStamp;
        return $this;
    }

    public function getWhiteBalance(): ?string
    {
        return $this->whiteBalance;
    }

    public function setWhiteBalance(string $whiteBalance): static
    {
        $this->whiteBalance = $whiteBalance;
        return $this;
    }

    public function getDateTime(): ?\DateTimeImmutable
    {
        return $this->dateTime;
    }

    public function setDateTime(\DateTimeImmutable $dateTime): static
    {
        $this->dateTime = $dateTime;
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
}