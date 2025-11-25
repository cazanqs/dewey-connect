<?php

namespace App\Entity;

use App\Repository\TrajetRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TrajetRepository::class)]
class Trajet
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank(message: "Le point de départ est obligatoire.")]
    #[ORM\Column(length: 255)]
    private ?string $point_de_depart = null;

    #[Assert\NotBlank(message: "La destination est obligatoire.")]
    #[ORM\Column(length: 255)]
    private ?string $destination = null;

    #[Assert\NotNull(message: "La date et l'heure sont obligatoires.")]
    #[Assert\GreaterThan('now', message: "La date et l'heure ne doivent pas être encore passées.")]
    #[ORM\Column]
    private ?\DateTime $date_et_heure = null;

    #[Assert\NotNull(message: "Le nombre de sièges libres est obligatoire.")]
    #[Assert\Positive(message: "Le nombre de sièges libres doit être supérieur à 0.")]
    #[ORM\Column]
    private ?int $sieges_libres = null;

    #[ORM\ManyToOne(inversedBy: 'trajets')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "Un utilisateur doit être associé au trajet.")]
    private ?Utilisateur $utilisateur = null;

    /**
     * @var Collection<int, Reservation>
     */
    #[ORM\OneToMany(targetEntity: Reservation::class, mappedBy: 'trajet')]
    private Collection $reservations;

    public function __construct()
    {
        $this->reservations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPointDeDepart(): ?string
    {
        return $this->point_de_depart;
    }

    public function setPointDeDepart(string $point_de_depart): static
    {
        $this->point_de_depart = $point_de_depart;

        return $this;
    }

    public function getDestination(): ?string
    {
        return $this->destination;
    }

    public function setDestination(string $destination): static
    {
        $this->destination = $destination;

        return $this;
    }

    public function getDateEtHeure(): ?\DateTime
    {
        return $this->date_et_heure;
    }

    public function setDateEtHeure(\DateTime $date_et_heure): static
    {
        $this->date_et_heure = $date_et_heure;

        return $this;
    }

    public function getSiegesLibres(): ?int
    {
        return $this->sieges_libres;
    }

    public function setSiegesLibres(int $sieges_libres): static
    {
        $this->sieges_libres = $sieges_libres;

        return $this;
    }

    public function getUtilisateur(): ?utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?Utilisateur $utilisateur): static
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }

    /**
     * @return Collection<int, Reservation>
     */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(Reservation $reservation): static
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations->add($reservation);
            $reservation->setTrajet($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): static
    {
        if ($this->reservations->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getTrajet() === $this) {
                $reservation->setTrajet(null);
            }
        }

        return $this;
    }
}
