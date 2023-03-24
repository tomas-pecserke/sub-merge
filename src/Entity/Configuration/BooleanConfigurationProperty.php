<?php

namespace App\Entity\Configuration;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;

/** @extends ConfigurationProperty<bool> */
#[Entity]
class BooleanConfigurationProperty extends ConfigurationProperty {
    #[Column(nullable: true)]
    private ?bool $booleanValue = null;

    public function getBooleanValue(): ?bool {
        return $this->booleanValue;
    }

    public function setBooleanValue(?bool $booleanValue): self {
        $this->booleanValue = $booleanValue;
        return $this;
    }

    public function getValue(): ?bool {
        return $this->getBooleanValue();
    }

    public function setValue($value): self {
        return $this->setBooleanValue($value);
    }
}
