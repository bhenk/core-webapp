<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

namespace bhenk\corewa\dao\abc;

use ValueError;

enum DataTypes: string {

    case string = "VARCHAR(255)";
    case int = "INT";
    case bool = "BOOLEAN";
    case float = "FLOAT";

    public static function fromName(string $name): string
    {
        foreach (self::cases() as $type) {
            if( $name === $type->name ){
                return $type->value;
            }
        }
        throw new ValueError("$name is not a valid backing value for enum " . self::class );
    }
}
