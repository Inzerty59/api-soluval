<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Pièce vendue/retirée à signaler à Appropo (fichier hour/appropo-unstock-*.csv).
 * sent_at NULL = pas encore déposé sur leur SFTP.
 */
#[ORM\Entity]
#[ORM\Index(columns: ['sent_at'], name: 'idx_appropo_unstock_sent_at')]
class AppropoUnstock
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $externalId = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $sentAt = null;

    public function __construct(int $externalId)
    {
        $this->externalId = $externalId;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getExternalId(): ?int
    {
        return $this->externalId;
    }
}
