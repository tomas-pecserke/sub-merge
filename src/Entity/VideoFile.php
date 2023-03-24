<?php

namespace App\Entity;

use App\Repository\VideoFileRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Gedmo\Mapping\Annotation\Timestampable;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

#[Entity(repositoryClass: VideoFileRepository::class)]
class VideoFile {
    #[Id, GeneratedValue, Column]
    private ?int $id = null;

    #[Column(length: 512), NotBlank]
    private string $path = '';

    #[Column(length: 255), NotBlank]
    private string $title = '';

    #[Column(length: 255), NotBlank]
    private string $videoCodec = '';

    #[Column(length: 255), NotBlank]
    private string $audioCodec = '';

    #[Column(length: 255), NotBlank]
    private string $container = '';

    #[Column, PositiveOrZero]
    private int $size = 0;

    #[Column, NotNull]
    private ?DateTimeImmutable $mTime = null;

    #[Column(type: 'string', enumType: VideoFileStatus::class)]
    private VideoFileStatus $status = VideoFileStatus::Unknown;

    #[Column(nullable: true)]
    private ?DateTimeImmutable $lastSyncedAt = null;

    #[Column, NotNull, Timestampable(on: 'create')]
    private ?DateTimeImmutable $createdAt = null;

    #[Column, NotNull, Timestampable(on: 'update')]
    private ?DateTimeImmutable $lastModifiedAt = null;

    public function getId(): ?int {
        return $this->id;
    }

    public function getPath(): string {
        return $this->path;
    }

    public function setPath(string $path): self {
        $this->path = $path;
        return $this;
    }

    public function getTitle(): string {
        return $this->title;
    }

    public function setTitle(string $title): self {
        $this->title = $title;
        return $this;
    }

    public function getVideoCodec(): string {
        return $this->videoCodec;
    }

    public function setVideoCodec(string $videoCodec): self {
        $this->videoCodec = $videoCodec;
        return $this;
    }

    public function getAudioCodec(): string {
        return $this->audioCodec;
    }

    public function setAudioCodec(string $audioCodec): self {
        $this->audioCodec = $audioCodec;
        return $this;
    }

    public function getContainer(): string {
        return $this->container;
    }

    public function setContainer(string $container): self {
        $this->container = $container;
        return $this;
    }

    public function getSize(): int {
        return $this->size;
    }

    public function setSize(int $size): self {
        $this->size = $size;
        return $this;
    }

    public function getMTime(): ?DateTimeImmutable {
        return $this->mTime;
    }

    public function setMTime(DateTimeImmutable $mTime): self {
        $this->mTime = $mTime;
        return $this;
    }

    public function getStatus(): VideoFileStatus {
        return $this->status;
    }

    public function setStatus(VideoFileStatus $status): self {
        $this->status = $status;
        return $this;
    }

    public function getLastSyncedAt(): ?DateTimeImmutable {
        return $this->lastSyncedAt;
    }

    public function setLastSyncedAt(DateTimeImmutable $lastSyncedAt): self {
        $this->lastSyncedAt = $lastSyncedAt;
        return $this;
    }

    public function getCreatedAt(): ?DateTimeImmutable {
        return $this->createdAt;
    }

    public function getLastModifiedAt(): ?DateTimeImmutable {
        return $this->lastModifiedAt;
    }
}
