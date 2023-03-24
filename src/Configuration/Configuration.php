<?php

namespace App\Configuration;

use App\Repository\ConfigurationPropertyRepository;
use DateTimeInterface;

class Configuration {
    private readonly ConfigurationPropertyRepository $repository;

    public function __construct(ConfigurationPropertyRepository $repository) {
        $this->repository = $repository;
    }

    public function getString(Property $property, ?string $default = null): ?string {
        return $this->repository->getString($property->name, $default);
    }

    public function setString(Property $property, ?string $value): void {
        $this->repository->setString($property->name, $value);
    }

    public function getText(Property $property, ?string $default = null): ?string {
        return $this->repository->getText($property->name, $default);
    }

    public function setText(Property $property, ?string $value): void {
        $this->repository->setText($property->name, $value);
    }

    public function getInt(Property $property, ?int $default = null): ?int {
        return $this->repository->getInt($property->name, $default);
    }

    public function setInt(Property $property, ?int $value): void {
        $this->repository->setInt($property->name, $value);
    }

    public function getFloat(Property $property, ?float $default = null): ?float {
        return $this->repository->getFloat($property->name, $default);
    }

    public function setFloat(Property $property, ?float $value): void {
        $this->repository->setFloat($property->name, $value);
    }

    public function getBoolean(Property $property, ?bool $default = null): ?bool {
        return $this->repository->getBoolean($property->name, $default);
    }

    public function setBoolean(Property $property, ?bool $value): void {
        $this->repository->setBoolean($property->name, $value);
    }

    public function getDatetime(Property $property, ?DateTimeInterface $default = null): ?DateTimeInterface {
        return $this->repository->getDatetime($property->name, $default);
    }

    public function setDatetime(Property $property, ?DateTimeInterface $value): void {
        $this->repository->setDatetime($property->name, $value);
    }

    public function getArray(Property $property, ?array $default = null): ?array {
        return $this->repository->getArray($property->name, $default);
    }

    public function setArray(Property $property, ?array $value): void {
        $this->repository->setArray($property->name, $value);
    }
}
