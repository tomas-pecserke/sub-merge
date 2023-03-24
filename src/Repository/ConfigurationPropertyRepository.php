<?php

namespace App\Repository;

use App\Entity\Configuration\ArrayConfigurationProperty;
use App\Entity\Configuration\BooleanConfigurationProperty;
use App\Entity\Configuration\ConfigurationProperty;
use App\Entity\Configuration\DateTimeConfigurationProperty;
use App\Entity\Configuration\FloatConfigurationProperty;
use App\Entity\Configuration\IntegerConfigurationProperty;
use App\Entity\Configuration\StringConfigurationProperty;
use App\Entity\Configuration\TextConfigurationProperty;
use DateTimeInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ConfigurationProperty>
 *
 * @method ConfigurationProperty|null find($id, $lockMode = null, $lockVersion = null)
 * @method ConfigurationProperty|null findOneBy(array $criteria, array $orderBy = null)
 * @method ConfigurationProperty[]    findAll()
 * @method ConfigurationProperty[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConfigurationPropertyRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, ConfigurationProperty::class);
    }

    public function save(ConfigurationProperty $entity, bool $flush = false): void {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ConfigurationProperty $entity, bool $flush = false): void {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @template T
     * @param string $name
     * @param class-string<T> $class
     * @return T|null
     */
    public function findOneByNameAndType(string $name, string $class): ?ConfigurationProperty {
        $qb = $this->createQueryBuilder('c');
        return $qb->where($qb->expr()->eq('c.name', ':name'))
            ->andWhere($qb->expr()->isInstanceOf('c', $class))
            ->getQuery()
            ->setParameter('name', $name)
            ->getOneOrNullResult();
    }

    public function get(string $name, mixed $default = null): mixed {
        $config = $this->findOneBy(['name' => $name]);
        return $config === null ? $default : $config->getValueOr($default);
    }

    public function getString(string $name, ?string $default = null): ?string {
        $config = $this->findOneByNameAndType($name, StringConfigurationProperty::class);
        return $config === null ? $default : $config->getValueOr($default);
    }

    public function getText(string $name, ?string $default = null): ?string {
        $config = $this->findOneByNameAndType($name, TextConfigurationProperty::class);
        return $config === null ? $default : $config->getValueOr($default);
    }

    public function getInt(string $name, ?int $default = null): ?int {
        $config = $this->findOneByNameAndType($name, IntegerConfigurationProperty::class);
        return $config === null ? $default : $config->getValueOr($default);
    }

    public function getFloat(string $name, ?float $default = null): ?float {
        $config = $this->findOneByNameAndType($name, FloatConfigurationProperty::class);
        return $config === null ? $default : $config->getValueOr($default);
    }

    public function getBoolean(string $name, ?float $default = null): ?bool {
        $config = $this->findOneByNameAndType($name, BooleanConfigurationProperty::class);
        return $config === null ? $default : $config->getValueOr($default);
    }

    public function getDatetime(string $name, ?DateTimeInterface $default = null): ?DateTimeInterface {
        $config = $this->findOneByNameAndType($name, DateTimeConfigurationProperty::class);
        return $config === null ? $default : $config->getValueOr($default);
    }

    public function getArray(string $name, ?array $default = null): ?array {
        $config = $this->findOneByNameAndType($name, ArrayConfigurationProperty::class);
        return $config === null ? $default : $config->getValueOr($default);
    }

    public function setString(string $name, ?string $value): void {
        $this->set($name, $value, StringConfigurationProperty::class);
    }

    public function setText(string $name, ?string $value): void {
        $this->set($name, $value, TextConfigurationProperty::class);
    }

    public function setInt(string $name, ?int $value): void {
        $this->set($name, $value, IntegerConfigurationProperty::class);
    }

    public function setFloat(string $name, ?float $value): void {
        $this->set($name, $value, FloatConfigurationProperty::class);
    }

    public function setBoolean(string $name, ?bool $value): void {
        $this->set($name, $value, BooleanConfigurationProperty::class);
    }

    public function setDateTime(string $name, ?DateTimeInterface $value): void {
        $this->set($name, $value, DateTimeConfigurationProperty::class);
    }

    public function setArray(string $name, ?array $value): void {
        $this->set($name, $value, ArrayConfigurationProperty::class);
    }

    /**
     * @template T
     * @param string $name
     * @param T $value
     * @param class-string<ConfigurationProperty<T>> $class
     * @return void
     */
    private function set(string $name, $value, string $class): void {
        $config = $this->findOneBy(['name' => $name]);
        if ($config !== null && $class !== get_class($config)) {
            $this->remove($class);
            $config = null;
        }
        if ($config === null) {
            $config = new $class();
            $config->setName($name);
        }
        /* @var ConfigurationProperty<T> $config */
        $config->setValue($value);
        $this->save($config, true);
    }
}
