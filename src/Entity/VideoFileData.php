<?php

namespace App\Entity;

use App\Repository\VideoFileDataRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;

#[ORM\Entity(repositoryClass: VideoFileDataRepository::class)]
class VideoFileData
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?Uuid $id = null;

    #[ORM\Column(length: 255)]
    private ?string $videoFileName = null;

    #[ORM\Column(type: Types::JSON)]
    private array $videoFileXml = [];

    #[ORM\Column(type: Types::JSON)]
    private array $exifHeader = [];

    #[ORM\Column(type: Types::JSON)]
    private array $exifParameters = [];

    #[ORM\Column(length: 255)]
    private ?string $logfileName = null;

    #[ORM\Column(type: Types::JSON)]
    private array $logfileHeader = [];

    #[ORM\Column(length: 255)]
    private ?string $thumbnail = null;

    #[ORM\Column(length: 36, unique: true)]
    private ?string $heavyName = null;

    #[ORM\Column(type: Types::JSON)]
    private array $permissions = [];

    #[ORM\ManyToOne(targetEntity: MediaCard::class, inversedBy: 'videoFileDatas')]
    #[ORM\JoinColumn(nullable: false)]
    private ?MediaCard $mediaCard = null;
	
	#[ORM\OneToMany(mappedBy: 'videoFileData', targetEntity: ExifDataEntry::class)]
	private Collection $exifDataEntries;	

    // Constructor to auto-generate heavyName
    public function __construct()
    {
        $this->heavyName = Uuid::v4()->toRfc4122();
		$this->exifDataEntries = new ArrayCollection();
    }

    // Getters and setters
    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getVideoFileName(): ?string
    {
        return $this->videoFileName;
    }

    public function setVideoFileName(string $videoFileName): static
    {
        $this->videoFileName = $videoFileName;
        return $this;
    }

    public function getVideoFileXml(): array
    {
        return $this->videoFileXml;
    }

    public function setVideoFileXml(array $videoFileXml): static
    {
        $this->videoFileXml = $videoFileXml;
        return $this;
    }

    public function getExifHeader(): array
    {
        return $this->exifHeader;
    }

    public function setExifHeader(array $exifHeader): static
    {
        $this->exifHeader = $exifHeader;
        return $this;
    }

    public function getExifParameters(): array
    {
        return $this->exifParameters;
    }

    public function setExifParameters(array $exifParameters): static
    {
        $this->exifParameters = $exifParameters;
        return $this;
    }

    public function getLogfileName(): ?string
    {
        return $this->logfileName;
    }

    public function setLogfileName(string $logfileName): static
    {
        $this->logfileName = $logfileName;
        return $this;
    }

    public function getLogfileHeader(): array
    {
        return $this->logfileHeader;
    }

    public function setLogfileHeader(array $logfileHeader): static
    {
        $this->logfileHeader = $logfileHeader;
        return $this;
    }

    public function getThumbnail(): ?string
    {
        return $this->thumbnail;
    }

    public function setThumbnail(string $thumbnail): static
    {
        $this->thumbnail = $thumbnail;
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
	 * @return Collection<int, ExifDataEntry>
	 */
	public function getExifDataEntries(): Collection
	{
		return $this->exifDataEntries;
	}

	public function addExifDataEntry(ExifDataEntry $exifDataEntry): static
	{
		if (!$this->exifDataEntries->contains($exifDataEntry)) {
			$this->exifDataEntries->add($exifDataEntry);
			$exifDataEntry->setVideoFileData($this);
		}

		return $this;
	}

	public function removeExifDataEntry(ExifDataEntry $exifDataEntry): static
	{
		if ($this->exifDataEntries->removeElement($exifDataEntry)) {
			if ($exifDataEntry->getVideoFileData() === $this) {
				$exifDataEntry->setVideoFileData(null);
			}
		}

		return $this;
	}
}