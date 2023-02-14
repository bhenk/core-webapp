<?php

namespace bhenk\corewa\dao\node;

use bhenk\corewa\dao\abc\Entity;

class NodeDo extends Entity {

    function __construct(private readonly ?int $ID = null,
                         private ?int          $parent_id = null,
                         private ?string       $name = null,
                         private ?string       $alias = null,
                         private ?string       $nature = null,
                         private bool          $public = true,
                         private float         $estimate = 0.0
    ) {
        parent::__construct($this->ID);
    }

    /**
     * @return int|null
     */
    public function getParentId(): ?int {
        return $this->parent_id;
    }

    /**
     * @param int|null $parent_id
     */
    public function setParentId(?int $parent_id): void {
        $this->parent_id = $parent_id;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string {
        return $this->name;
    }

    /**
     * @param string|null $name
     */
    public function setName(?string $name): void {
        $this->name = $name;
    }

    /**
     * @return string|null
     */
    public function getAlias(): ?string {
        return $this->alias;
    }

    /**
     * @param string|null $alias
     */
    public function setAlias(?string $alias): void {
        $this->alias = $alias;
    }

    /**
     * @return string|null
     */
    public function getNature(): ?string {
        return $this->nature;
    }

    /**
     * @param string|null $nature
     */
    public function setNature(?string $nature): void {
        $this->nature = $nature;
    }

    /**
     * @return bool
     */
    public function isPublic(): bool {
        return $this->public;
    }

    /**
     * @param bool $public
     */
    public function setPublic(bool $public): void {
        $this->public = $public;
    }

    /**
     * @return float
     */
    public function getEstimate(): float {
        return $this->estimate;
    }

    /**
     * @param float $estimate
     */
    public function setEstimate(float $estimate): void {
        $this->estimate = $estimate;
    }

}