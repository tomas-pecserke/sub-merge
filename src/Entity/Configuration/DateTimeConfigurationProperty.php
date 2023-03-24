<?php

namespace App\Entity\Configuration;

use DateTimeInterface;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;

/** @extends ConfigurationProperty<DateTimeInterface> */
#[Entity]
class DateTimeConfigurationProperty extends ConfigurationProperty {
    #[Column(nullable: true)]
    private ?DateTimeInterface $dateTimeValue = null;

    public function getDateTimeValue(): ?DateTimeInterface {
        return $this->dateTimeValue;
    }

    public function setDateTimeValue(?DateTimeInterface $dateTimeValue): self {
        $this->dateTimeValue = $dateTimeValue;
        return $this;
    }

    public function getValue(): ?DateTimeInterface {
        return $this->getDateTimeValue();
    }

    public function setValue($value): self {
        return $this->setDateTimeValue($value);
    }
}
