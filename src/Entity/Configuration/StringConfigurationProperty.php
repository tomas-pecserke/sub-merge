<?php

namespace App\Entity\Configuration;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Symfony\Component\Validator\Constraints\Length;

/** @extends ConfigurationProperty<string> */
#[Entity]
class StringConfigurationProperty extends ConfigurationProperty {
    #[Column(length: 1023, nullable: true)]
    #[Length(max: 1023)]
    private ?string $stringValue = null;

    public function getStringValue(): ?string {
        return $this->stringValue;
    }

    public function setStringValue(?string $stringValue): self {
        $this->stringValue = $stringValue;
        return $this;
    }

    public function getValue(): ?string {
        return $this->getStringValue();
    }

    public function setValue($value): self {
        return $this->setStringValue($value);
    }
}
