<?php

namespace App\Entity\Admin;

use App\Entity\GestApp\Event;
use App\Entity\GestApp\Product;
use App\Entity\GestApp\Recommandation;
use App\Entity\Webapp\Article;
use App\Entity\Webapp\Page;
use App\Repository\Admin\MemberRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\Ignore;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity(repositoryClass=MemberRepository::class)
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity(fields={"email"}, message="There is already an account with this email")
 * @Vich\Uploadable()
 */
class Member implements UserInterface
{
    public function serialize(): array
    {
        return (array(
            $this->id,
            $this->email,
            $this->firstName,
            $this->lastName,
            $this->roles,
            $this->password,
            $this->adress1,
            $this->Adress2,
            $this->zipcode,
            $this->city,
            $this->phoneGsm
        ));
    }

    public function unserialize(array $serialized)
    {
        list(
            $this->id,
            $this->email,
            $this->firstName,
            $this->lastName,
            $this->roles,
            $this->password,
            $this->adress1,
            $this->Adress2,
            $this->zipcode,
            $this->city,
            $this->phoneGsm
            ) = $this->unserialized($serialized, array('allowed_classes' => false));
    }

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"user"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Groups({"user"})
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"user"})
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"user"})
     */
    private $lastName;

    /**
     * Insertion de l'image mise en avant liée à un article
     * NOTE : Il ne s'agit pas d'un champ mappé des métadonnées de l'entité, mais d'une simple propriété.
     *
     * @Vich\UploadableField(mapping="avatar_image", fileNameProperty="avatarName", size="avatarSize")
     * @Ignore()
     * @var File|null
     */
    private $avatarFile;

    /**
     * @ORM\Column(type="string",nullable=true)
     *
     * @var string|null
     */
    private $avatarName;

    /**
     * @ORM\Column(type="integer",nullable=true)
     *
     * @var int|null
     */
    private $avatarSize;

    /**
     * @ORM\Column(type="string", length=150)
     */
    private $adress1;

    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    private $Adress2;

    /**
     * @ORM\Column(type="string", length=5, nullable=true)
     */
    private $zipcode;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $city;

    /**
     * @ORM\Column(type="string", length=14, nullable=true)
     */
    private $phoneDesk;

    /**
     * @ORM\Column(type="string", length=14, nullable=true)
     */
    private $phoneGsm;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isVerified = false;

    /**
     * @ORM\OneToMany(targetEntity=Page::class, mappedBy="author")
     */
    private $pages;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $username;

    /**
     * @ORM\OneToMany(targetEntity=Article::class, mappedBy="Author")
     */
    private $articles;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $type;

    /**
     * @ORM\OneToMany(targetEntity=Product::class, mappedBy="producer", orphanRemoval=true)
     */
    private $products;

    /**
     * @ORM\ManyToOne(targetEntity=Structure::class, inversedBy="members")
     */
    private $structure;

    /**
     * @ORM\OneToMany(targetEntity=Event::class, mappedBy="author", orphanRemoval=true)
     */
    private $events;

    /**
     * @ORM\OneToMany(targetEntity=Recommandation::class, mappedBy="member", orphanRemoval=true)
     */
    private $recommandations;

    /**
     * @ORM\OneToMany(targetEntity=Recommandation::class, mappedBy="author", orphanRemoval=true)
     */
    private $authorReco;

    /**
     * @ORM\OneToMany(targetEntity=Annonce::class, mappedBy="author", orphanRemoval=true)
     */
    private $annonce;

    /**
     * @ORM\OneToMany(targetEntity=Message::class, mappedBy="author", orphanRemoval=true)
     */
    private $authormessages;

    /**
     * @ORM\ManyToMany(targetEntity=Message::class, mappedBy="recipient")
     */
    private $recipientmessage;

    /**
     * @ORM\OneToMany(targetEntity=Parrainage::class, mappedBy="author")
     */
    private $parraingesAuthor;

    /**
     * @ORM\OneToMany(targetEntity=Team::class, mappedBy="Anims")
     */
    private $team;

    /**
     * @ORM\ManyToMany(targetEntity=Team::class, mappedBy="members")
     */
    private $teams;


    public function __construct()
    {
        $this->pages = new ArrayCollection();
        $this->articles = new ArrayCollection();
        $this->products = new ArrayCollection();
        $this->events = new ArrayCollection();
        $this->recommandations = new ArrayCollection();
        $this->authorReco = new ArrayCollection();
        $this->annonces = new ArrayCollection();
        $this->messages = new ArrayCollection();
        $this->authormessages = new ArrayCollection();
        $this->recipientmessage = new ArrayCollection();
        $this->parraingesAuthor = new ArrayCollection();
        $this->team = new ArrayCollection();
        $this->teams = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }


    /**
     * Si vous téléchargez manuellement un fichier (c'est-à-dire sans utiliser Symfony Form),
     * assurez-vous qu'une instance de "UploadedFile" est injectée dans ce paramètre pour déclencher la mise à jour.
     * Si le paramètre de configuration 'inject_on_load' de ce bundle est défini sur 'true', ce setter doit être
     * capable d'accepter une instance de 'File' car le bundle en injectera une ici pendant l'hydratation de Doctrine.
     *
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile|null $avatarFile
     */
    public function setAvatarFile(?File $avatarFile = null): void
    {
        $this->avatarFile = $avatarFile;

        if (null !== $avatarFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getAvatarFile(): ?File
    {
        return $this->avatarFile;
    }

    public function setAvatarName(?string $avatarName): void
    {
        $this->avatarName = $avatarName;
    }

    public function getAvatarName(): ?string
    {
        return $this->avatarName;
    }

    public function setAvatarSize(?int $avatarSize): void
    {
        $this->avatarSize = $avatarSize;
    }

    public function getAvatarSize(): ?int
    {
        return $this->avatarSize;
    }
    

    public function getAdress1(): ?string
    {
        return $this->adress1;
    }

    public function setAdress1(string $adress1): self
    {
        $this->adress1 = $adress1;

        return $this;
    }

    public function getAdress2(): ?string
    {
        return $this->Adress2;
    }

    public function setAdress2(?string $Adress2): self
    {
        $this->Adress2 = $Adress2;

        return $this;
    }

    public function getZipcode(): ?string
    {
        return $this->zipcode;
    }

    public function setZipcode(?string $zipcode): self
    {
        $this->zipcode = $zipcode;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getPhoneDesk(): ?string
    {
        return $this->phoneDesk;
    }

    public function setPhoneDesk(?string $phoneDesk): self
    {
        $this->phoneDesk = $phoneDesk;

        return $this;
    }

    public function getPhoneGsm(): ?string
    {
        return $this->phoneGsm;
    }

    public function setPhoneGsm(?string $phoneGsm): self
    {
        $this->phoneGsm = $phoneGsm;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @ORM\PrePersist()
     */
    public function setCreatedAt(): self
    {
        $this->createdAt = new \DateTime('now');
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function setUpdatedAt(): self
    {
        $this->updatedAt = new \DateTime('now');
        return $this;
    }

    public function getIsVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    /**
     * @return Collection|Page[]
     */
    public function getPages(): Collection
    {
        return $this->pages;
    }

    public function addPage(Page $page): self
    {
        if (!$this->pages->contains($page)) {
            $this->pages[] = $page;
            $page->setAuthor($this);
        }

        return $this;
    }

    public function removePage(Page $page): self
    {
        if ($this->pages->removeElement($page)) {
            // set the owning side to null (unless already changed)
            if ($page->getAuthor() === $this) {
                $page->setAuthor(null);
            }
        }

        return $this;
    }

    public function __ToString()
    {
        return $this->firstName. " " .$this->lastName;
    }

    public function setUsername(?string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return Collection|Article[]
     */
    public function getArticles(): Collection
    {
        return $this->articles;
    }

    public function addArticle(Article $article): self
    {
        if (!$this->articles->contains($article)) {
            $this->articles[] = $article;
            $article->setAuthor($this);
        }

        return $this;
    }

    public function removeArticle(Article $article): self
    {
        if ($this->articles->removeElement($article)) {
            // set the owning side to null (unless already changed)
            if ($article->getAuthor() === $this) {
                $article->setAuthor(null);
            }
        }

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return Collection|Product[]
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product): self
    {
        if (!$this->products->contains($product)) {
            $this->products[] = $product;
            $product->setProducer($this);
        }

        return $this;
    }

    public function removeProduct(Product $product): self
    {
        if ($this->products->removeElement($product)) {
            // set the owning side to null (unless already changed)
            if ($product->getProducer() === $this) {
                $product->setProducer(null);
            }
        }

        return $this;
    }

    public function getStructure(): ?Structure
    {
        return $this->structure;
    }

    public function setStructure(?Structure $structure): self
    {
        $this->structure = $structure;

        return $this;
    }

    /**
     * @return Collection|Event[]
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(Event $event): self
    {
        if (!$this->events->contains($event)) {
            $this->events[] = $event;
            $event->setAuthor($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): self
    {
        if ($this->events->removeElement($event)) {
            // set the owning side to null (unless already changed)
            if ($event->getAuthor() === $this) {
                $event->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Recommandation[]
     */
    public function getRecommandations(): Collection
    {
        return $this->recommandations;
    }

    public function addRecommandation(Recommandation $recommandation): self
    {
        if (!$this->recommandations->contains($recommandation)) {
            $this->recommandations[] = $recommandation;
            $recommandation->setMember($this);
        }

        return $this;
    }

    public function removeRecommandation(Recommandation $recommandation): self
    {
        if ($this->recommandations->removeElement($recommandation)) {
            // set the owning side to null (unless already changed)
            if ($recommandation->getMember() === $this) {
                $recommandation->setMember(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Recommandation[]
     */
    public function getAuthorReco(): Collection
    {
        return $this->authorReco;
    }

    public function addAuthorReco(Recommandation $authorReco): self
    {
        if (!$this->authorReco->contains($authorReco)) {
            $this->authorReco[] = $authorReco;
            $authorReco->setAuthor($this);
        }

        return $this;
    }

    public function removeAuthorReco(Recommandation $authorReco): self
    {
        if ($this->authorReco->removeElement($authorReco)) {
            // set the owning side to null (unless already changed)
            if ($authorReco->getAuthor() === $this) {
                $authorReco->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Annonce[]
     */
    public function getAnnonce(): Collection
    {
        return $this->annonce;
    }

    public function addAnnonce(Annonce $annonce): self
    {
        if (!$this->annonce->contains($annonce)) {
            $this->annonce[] = $annonce;
            $annonce->setAuthor($this);
        }

        return $this;
    }

    public function removeAnnonce(Annonce $annonce): self
    {
        if ($this->annonce->removeElement($annonce)) {
            // set the owning side to null (unless already changed)
            if ($annonce->getAuthor() === $this) {
                $annonce->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Message[]
     */
    public function getAuthormessages(): Collection
    {
        return $this->authormessages;
    }

    public function addAuthormessage(Message $authormessage): self
    {
        if (!$this->authormessages->contains($authormessage)) {
            $this->authormessages[] = $authormessage;
            $authormessage->setAuthor($this);
        }

        return $this;
    }

    public function removeAuthormessage(Message $authormessage): self
    {
        if ($this->authormessages->removeElement($authormessage)) {
            // set the owning side to null (unless already changed)
            if ($authormessage->getAuthor() === $this) {
                $authormessage->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Message[]
     */
    public function getRecipientmessage(): Collection
    {
        return $this->recipientmessage;
    }

    public function addRecipientmessage(Message $recipientmessage): self
    {
        if (!$this->recipientmessage->contains($recipientmessage)) {
            $this->recipientmessage[] = $recipientmessage;
            $recipientmessage->addRecipient($this);
        }

        return $this;
    }

    public function removeRecipientmessage(Message $recipientmessage): self
    {
        if ($this->recipientmessage->removeElement($recipientmessage)) {
            $recipientmessage->removeRecipient($this);
        }

        return $this;
    }

    /**
     * @return Collection|Parrainage[]
     */
    public function getParraingesAuthor(): Collection
    {
        return $this->parraingesAuthor;
    }

    public function addParraingesAuthor(Parrainage $parraingesAuthor): self
    {
        if (!$this->parraingesAuthor->contains($parraingesAuthor)) {
            $this->parraingesAuthor[] = $parraingesAuthor;
            $parraingesAuthor->setAuthor($this);
        }

        return $this;
    }

    public function removeParraingesAuthor(Parrainage $parraingesAuthor): self
    {
        if ($this->parraingesAuthor->removeElement($parraingesAuthor)) {
            // set the owning side to null (unless already changed)
            if ($parraingesAuthor->getAuthor() === $this) {
                $parraingesAuthor->setAuthor(null);
            }
        }

        return $this;
    }

    private function unserialized(array $serialized, array $array)
    {
    }

    /**
     * @return Collection|Team[]
     */
    public function getTeam(): Collection
    {
        return $this->team;
    }

    public function addTeam(Team $team): self
    {
        if (!$this->team->contains($team)) {
            $this->team[] = $team;
            $team->setAnims($this);
        }

        return $this;
    }

    public function removeTeam(Team $team): self
    {
        if ($this->team->removeElement($team)) {
            // set the owning side to null (unless already changed)
            if ($team->getAnims() === $this) {
                $team->setAnims(null);
            }
        }

        return $this;
    }
}
