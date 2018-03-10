<?php
include_once 'BasicEnum.php';
abstract class DataTypes extends BasicEnum {
    const int = 0;
    const integer = 1;
    const bool = 2;
    const boolean = 3;
    const float = 4;
    const double = 5;
    const real = 6;
    const string = 7;
    const array = 8;
    const object = 9;
    const unset = 10;
    
    /**
     * Cast the value to the specified type
     * @param string $value
     * @param string $castTarget
     * @throws InvalidArgumentException if the castTarget is not a valid datatype
     * @return number|boolean|string|array|StdClass|NULL
     */
    public static function cast ($value, string $castTarget){
        if (DataTypes::isValidName($castTarget) == false)
            throw new InvalidArgumentException("Invalid type to cast to: ".$castTarget);
        
        switch($castTarget)
        {
            case "int":
            case "integer": return (int)$value;
                            break;
            case "bool": 
            case "boolean": return (bool)$value;
                            break;
            case "float":   return (float)$value;
                            break;
            case "double":  return (double)$value;
                            break;
            case "real":    return (real)$value;
                            break;
            case "string":  return (string)$value;
                            break;
            case "array":   return (array)$value;
                            break;
            case "object":  return (object)$value;
                            break;
            case "unset":   return (unset)$value;
                            break;
        }
        
    }
}