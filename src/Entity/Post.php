<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post as Store;
use App\Repository\PostRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;

use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PostRepository::class)]
#[ApiResource(
    description: 'Entidad de las publicaciones con title, body y category',
    operations: [
        new Get(
            normalizationContext: ['groups' => ['post:read', 'post:read:item']],
        ),
        new GetCollection(
            normalizationContext: ['groups' => ['post:read', 'post:read:collection']],
        ),
        new Patch(),
        new Store(),
    ],
    denormalizationContext: ['groups' => ['post:write']], // POST, PUT, PATCH
    paginationItemsPerPage: 8
)]
#[ApiFilter(SearchFilter::class, properties: [
    'title'         => 'partial', // exact, partial, start, end, word_start
    'body'          => 'partial',
    'category.name' => 'partial',
])]
#[ApiFilter(OrderFilter::class, properties: ['id'])]
class Post
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['post:read'])]
    /**
     * Campo ID, único y auto_increment
     */
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['post:read', 'post:write'])]
    #[Assert\NotBlank]
    /**
     * Título de una publicación, frase
     */
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['post:read:item', 'post:write'])]
    #[Assert\NotBlank]
    /**
     * Contenido de la publicación, 1200 palabras
     */
    private ?string $body = null;

    #[ORM\ManyToOne(inversedBy: 'posts')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['post:read', 'post:write'])]
    #[Assert\NotBlank]
    /**
     * Campo de la categoría, relación ManyToOne
     */
    private ?Category $category = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    #[Groups(['post:read:collection'])]
    /**
     * Resumen de la publicación, su función es mostrarse en los listados
     */
    public function getSummary($len = 70): ?string
    {
        if (mb_strlen($this->body) <= $len) {
            return $this->body;
        }

        return mb_substr($this->body, 0, $len) . '[...]';
    }

    public function setBody(string $body): self
    {
        $this->body = $body;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }
}
