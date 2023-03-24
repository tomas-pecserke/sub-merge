<?php

namespace App\Entity\Configuration;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;

/** @extends ConfigurationProperty<int> */
#[Entity]
class IntegerConfigurationProperty extends ConfigurationProperty {
    #[Column(nullable: true)]
    private ?int $intValue = null;

    public function getIntValue(): ?int {
        return $this->intValue;
    }

    public function setIntValue(?int $intValue): self {
        $this->intValue = $intValue;
        return $this;
    }

    public function getValue(): ?int {
        return $this->getIntValue();
    }

    public function setValue($value): self {
        return $this->setIntValue($value);
    }
}
