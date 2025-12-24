<?php

namespace App\Entity;

use App\Repository\MediaCardRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: MediaCardRepository::class)]
class MediaCard
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?Uuid $id = null;

    #[ORM\Column(length: 50)]
    private ?string $mediaId = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $mediaCreated = null;

    #[ORM\Column(length: 50)]
    private ?string $systemId = null;

    #[ORM\Column(length: 255)]
    private ?string $systemKind = null;

    #[ORM\Column(length: 50)]
    private ?string $masterVersion = null;

    #[ORM\Column(length: 255)]
    private ?string $mediaKind = null;

    #[ORM\Column(length: 255)]
    private ?string $mediaName = null;

    #[ORM\Column(type: Types::JSON)]
    private array $contents = [];

    #[ORM\Column(length: 36, unique: true)]
    private ?string $heavyName = null;

    #[ORM\Column(type: Types::JSON)]
    private array $permissions = [];

    #[ORM\ManyToOne(targetEntity: Trip::class, inversedBy: 'mediaCards')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Trip $trip = null;

	#[ORM\OneToMany(mappedBy: 'mediaCard', targetEntity: LogfileDataEntry::class)]
	private Collection $logfileDataEntries;
	
	#[ORM\OneToMany(mappedBy: 'mediaCard', targetEntity: AttachedFile::class)]
    private Collection $attachedFiles;	

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;	

    public function __construct()
    {
        $this->heavyName = Uuid::v4()->toRfc4122();
		$this->logfileDataEntries = new ArrayCollection();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getMediaId(): ?string
    {
        return $this->mediaId;
    }

    public function setMediaId(string $mediaId): static
    {
        $this->mediaId = $mediaId;
        return $this;
    }

    public function getMediaCreated(): ?\DateTimeImmutable
    {
        return $this->mediaCreated;
    }

    public function setMediaCreated(\DateTimeImmutable $mediaCreated): static
    {
        $this->mediaCreated = $mediaCreated;
        return $this;
    }

    public function getSystemId(): ?string
    {
        return $this->systemId;
    }

    public function setSystemId(string $systemId): static
    {
        $this->systemId = $systemId;
        return $this;
    }

    public function getSystemKind(): ?string
    {
        return $this->systemKind;
    }

    public function setSystemKind(string $systemKind): static
    {
        $this->systemKind = $systemKind;
        return $this;
    }

    public function getMasterVersion(): ?string
    {
        return $this->masterVersion;
    }

    public function setMasterVersion(string $masterVersion): static
    {
        $this->masterVersion = $masterVersion;
        return $this;
    }

    public function getMediaKind(): ?string
    {
        return $this->mediaKind;
    }

    public function setMediaKind(string $mediaKind): static
    {
        $this->mediaKind = $mediaKind;
        return $this;
    }

    public function getMediaName(): ?string
    {
        return $this->mediaName;
    }

    public function setMediaName(string $mediaName): static
    {
        $this->mediaName = $mediaName;
        return $this;
    }

    public function getContents(): array
    {
        return $this->contents;
    }

    public function setContents(array $contents): static
    {
        $this->contents = $contents;
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

    public function getTrip(): ?Trip
    {
        return $this->trip;
    }

    public function setTrip(?Trip $trip): static
    {
        $this->trip = $trip;
        return $this;
    }
    
	/**
	 * @return Collection<int, LogfileDataEntry>
	 */
	public function getLogfileDataEntries(): Collection
	{
		return $this->logfileDataEntries;
	}

	public function addLogfileDataEntry(LogfileDataEntry $logfileDataEntry): static
	{
		if (!$this->logfileDataEntries->contains($logfileDataEntry)) {
			$this->logfileDataEntries->add($logfileDataEntry);
			$logfileDataEntry->setMediaCard($this);
		}

		return $this;
	}

	public function removeLogfileDataEntry(LogfileDataEntry $logfileDataEntry): static
	{
		if ($this->logfileDataEntries->removeElement($logfileDataEntry)) {
			if ($logfileDataEntry->getMediaCard() === $this) {
				$logfileDataEntry->setMediaCard(null);
			}
		}

		return $this;
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
	
	/**
	 * @return Collection<int, AttachedFile>
	 */
	public function getAttachedFiles(): Collection
	{
		return $this->attachedFiles;
	}

	public function addAttachedFile(AttachedFile $attachedFile): static
	{
		if (!$this->attachedFiles->contains($attachedFile)) {
			$this->attachedFiles->add($attachedFile);
			$attachedFile->setMediaCard($this);
		}

		return $this;
	}

	public function removeAttachedFile(AttachedFile $attachedFile): static
	{
		if ($this->attachedFiles->removeElement($attachedFile)) {
			if ($attachedFile->getMediaCard() === $this) {
				$attachedFile->setMediaCard(null);
			}
		}

		return $this;
	}	
	
}