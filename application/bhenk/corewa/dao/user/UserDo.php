<?php

namespace bhenk\corewa\dao\user;

use bhenk\corewa\dao\abc\Entity;

class UserDo extends Entity {

    function __construct(private readonly ?int $ID = null,
                         private ?string       $first_name = null,
                         private ?string       $prefixes = null,
                         private ?string       $last_name = null,
                         private ?string       $email = null
    ) {
        parent::__construct($this->ID);
    }

    /**
     * @return string|null
     */
    public function getFirstName(): ?string {
        return $this->first_name;
    }

    /**
     * @param string|null $first_name
     */
    public function setFirstName(?string $first_name): void {
        $this->first_name = $first_name;
    }

    /**
     * @return string|null
     */
    public function getPrefixes(): ?string {
        return $this->prefixes;
    }

    /**
     * @param string|null $prefixes
     */
    public function setPrefixes(?string $prefixes): void {
        $this->prefixes = $prefixes;
    }

    /**
     * @return string|null
     */
    public function getLastName(): ?string {
        return $this->last_name;
    }

    /**
     * @param string|null $last_name
     */
    public function setLastName(?string $last_name): void {
        $this->last_name = $last_name;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string {
        return $this->email;
    }

    /**
     * @param string|null $email
     */
    public function setEmail(?string $email): void {
        $this->email = $email;
    }

}