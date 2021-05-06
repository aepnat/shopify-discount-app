<?php

namespace App\Entity;

use App\Repository\DiscountCodeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=DiscountCodeRepository::class)
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(name="discount_codes")
 */
class DiscountCode
{
    const TYPES = ["PERCENTAGE", "FIXED_AMOUNT"];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=40)
     * @Assert\NotBlank
     * @Assert\Choice(choices=DiscountCode::TYPES, message="Choose a valid type.")
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=20)
     * @Assert\NotBlank
     */
    private $value;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Assert\Type("bool")
     */
    private $once_per_customer;

    /**
     * @ORM\Column(type="bigint", nullable=true)
     */
    private $price_rule_id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Shop", inversedBy="discount_code")
     */
    private $shop;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updated_at;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function getPriceRuleId(): ?string
    {
        return $this->price_rule_id;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getValueFormatted(): ?string
    {
        if ($this->getType() === "PERCENTAGE") {
            return "$this->value %";
        }

        $formatted = number_format($this->value, 2, ',', '.');
        return "$formatted IDR";
    }

    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getOncePerCustomer(): bool
    {
        return $this->once_per_customer ?: false;
    }

    public function setOncePerCustomer(?bool $once_per_customer): self
    {
        $this->once_per_customer = $once_per_customer;

        return $this;
    }

    public function setPriceRuleId(int $price_rule_id): self
    {
        $this->price_rule_id = $price_rule_id;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(?\DateTimeInterface $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    /**
     * Gets triggered only on insert
     * @ORM\PrePersist
     */
    public function onPrePersist()
    {
        $this->created_at = new \DateTime("now");
    }

    /**
     * Gets triggered every time on update
     * @ORM\PreUpdate
     */
    public function onPreUpdate()
    {
        $this->updated_at = new \DateTime("now");
    }

    /**
     * @return mixed
     */
    public function getShop(): ?Shop
    {
        return $this->shop;
    }

    /**
     * @param Shop|null $shop
     * @return DiscountCode
     */
    public function setShop(?Shop $shop): self
    {
        $this->shop = $shop;

        return $this;
    }


}
