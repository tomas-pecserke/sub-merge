<?php

namespace App\Entity\Configuration;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;

/** @extends ConfigurationProperty<string> */
#[Entity]
class TextConfigurationProperty extends ConfigurationProperty {
    #[Column(type: 'text', nullable: true)]
    private ?string $textValue = null;

    public function getTextValue(): ?string {
        return $this->textValue;
    }

    public function setTextValue(?string $textValue): self {
        $this->textValue = $textValue;
        return $this;
    }

    public function getValue(): ?string {
        return $this->getTextValue();
    }

    public function setValue($value): self {
        return $this->setTextValue($value);
    }
}
