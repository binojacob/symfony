<?php

namespace App\Entity;

use App\Repository\CommentsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CommentsRepository::class)]
class Comments
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    private ?string $description = null;

    // #[ORM\Column]
    // private ?int $bookid = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $username = null;

    #[ORM\ManyToOne(inversedBy: 'comments')]
    private ?Books $booksid = null;

    #[ORM\ManyToOne(inversedBy: 'comment')]

    public function getId(): ?int
    {
        return $this->id;
    }
    #[Assert\NotBlank]
    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    // public function getBookid(): ?int
    // {
    //     return $this->bookid;
    // }

    // public function setBookid(int $bookid): self
    // {
    //     $this->bookid = $bookid;

    //     return $this;
    // }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getBooksid(): ?Books
    {
        return $this->booksid;
    }

    public function setBooksid(?Books $booksid): self
    {

        $this->booksid = $booksid;

        return $this;
    }

}
