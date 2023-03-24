<?php

namespace App\Entity\Configuration;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;

/** @extends ConfigurationProperty<float> */
#[Entity]
class FloatConfigurationProperty extends ConfigurationProperty {
    #[Column(nullable: true)]
    private ?float $floatValue = null;

    public function getFloatValue(): ?float {
        return $this->floatValue;
    }

    public function setFloatValue(?float $floatValue): self {
        $this->floatValue = $floatValue;
        return $this;
    }

    public function getValue(): ?float {
        return $this->getFloatValue();
    }

    public function setValue($value): self {
        return $this->setFloatValue($value);
    }
}
