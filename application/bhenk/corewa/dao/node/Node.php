<?php

namespace bhenk\corewa\dao\node;

use bhenk\corewa\dao\abc\Entity;

readonly class Node extends Entity {

    function __construct(private ?int    $id = null,
                         private ?int    $parent_id = null,
                         private ?string $name = null,
                         private ?string $alias = null,
                         private ?string $nature = null
    ) {
        parent::__construct($this->id);
    }

    /**
     * @return int|null
     */
    public function getParentId(): ?int {
        return $this->parent_id;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getAlias(): ?string {
        return $this->alias;
    }

    /**
     * @return string|null
     */
    public function getNature(): ?string {
        return $this->nature;
    }
}