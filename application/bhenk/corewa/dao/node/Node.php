<?php

namespace bhenk\corewa\dao\node;

use bhenk\corewa\dao\abc\Entity;

class Node extends Entity {

    function __construct(private readonly ?int $ID = null,
                         private ?int          $parent_id = null,
                         private ?string       $name = null,
                         private ?string       $alias = null,
                         private ?string       $nature = null
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

}