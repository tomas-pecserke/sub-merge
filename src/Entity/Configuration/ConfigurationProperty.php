<?php

namespace App\Entity\Configuration;

use App\Repository\ConfigurationPropertyRepository;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\DiscriminatorMap;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\InheritanceType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Unique;

/** @template T */
#[Entity(repositoryClass: ConfigurationPropertyRepository::class)]
#[InheritanceType('SINGLE_TABLE')]
#[DiscriminatorColumn(name: 'type', type: 'string')]
#[DiscriminatorMap([
    'string' => StringConfigurationProperty::class,
    'text' => TextConfigurationProperty::class,
    'int' => IntegerConfigurationProperty::class,
    'float' => FloatConfigurationProperty::class,
    'boolean' => BooleanConfigurationProperty::class,
    'datetime' => DateTimeConfigurationProperty::class,
    'array' => ArrayConfigurationProperty::class,
])]
abstract class ConfigurationProperty {
    #[Id, Column(length: 255)]
    #[NotBlank, Unique, Length(max: 255)]
    private string $name = '';

    public function getName(): string {
        return $this->name;
    }

    public function setName(string $name): self {
        $this->name = $name;
        return $this;
    }

    /**
     * @return T|null
     */
    public abstract function getValue(): mixed;

    /**
     * @param T|null $value
     * @return self
     */
    public abstract function setValue($value): self;

    /**
     * @param T|null $default
     * @return T|null
     */
    public function getValueOr($default = null): mixed {
        $value = $this->getValue();
        return $value === null ? $default : $value;
    }
}
