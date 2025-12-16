<?php

namespace App\Entity;

use App\Repository\TripRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: TripRepository::class)]
class Trip
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?Uuid $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $dateBegin = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $dateEnd = null;

    #[ORM\Column(length: 36, unique: true)]
    private ?string $heavyName = null;

    #[ORM\Column(type: Types::JSON)]
    private array $permissions = [];
	
	#[ORM\OneToMany(mappedBy: 'trip', targetEntity: MediaCard::class)]
	private Collection $mediaCards;	

    // Constructor to auto-generate heavyName
    public function __construct()
    {
        $this->heavyName = Uuid::v4()->toRfc4122();
		$this->mediaCards = new ArrayCollection();
    }

    // Getters and setters...
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getDateBegin(): ?\DateTimeImmutable
    {
        return $this->dateBegin;
    }

    public function setDateBegin(\DateTimeImmutable $dateBegin): static
    {
        $this->dateBegin = $dateBegin;

        return $this;
    }

    public function getDateEnd(): ?\DateTimeImmutable
    {
        return $this->dateEnd;
    }

    public function setDateEnd(\DateTimeImmutable $dateEnd): static
    {
        $this->dateEnd = $dateEnd;

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
	/**
	 * @return Collection<int, MediaCard>
	 */
	public function getMediaCards(): Collection
	{
		return $this->mediaCards;
	}

	public function addMediaCard(MediaCard $mediaCard): static
	{
		if (!$this->mediaCards->contains($mediaCard)) {
			$this->mediaCards->add($mediaCard);
			$mediaCard->setTrip($this);
		}

		return $this;
	}

	public function removeMediaCard(MediaCard $mediaCard): static
	{
		if ($this->mediaCards->removeElement($mediaCard)) {
			// set the owning side to null (unless already changed)
			if ($mediaCard->getTrip() === $this) {
				$mediaCard->setTrip(null);
			}
		}

		return $this;
	}	
}