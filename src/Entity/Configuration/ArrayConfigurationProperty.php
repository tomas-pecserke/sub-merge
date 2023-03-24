<?php

namespace App\Entity\Configuration;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;

/** @extends ConfigurationProperty<array> */
#[Entity]
class ArrayConfigurationProperty extends ConfigurationProperty {
    #[Column(nullable: true)]
    private ?array $arrayValue = null;

    public function getArrayValue(): ?array {
        return $this->arrayValue;
    }

    public function setArrayValue(?array $arrayValue): self {
        $this->arrayValue = $arrayValue;
        return $this;
    }

    public function getValue(): ?array {
        return $this->getArrayValue();
    }

    public function setValue($value): self {
        return $this->setArrayValue($value);
    }
}
