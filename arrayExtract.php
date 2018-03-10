<?php
include_once 'DataTypes.php';
include_once 'InvalidDataException.php';

/**
 * Grabs the indexed data from the table safely
 * @param array $array
 * @param string|int $index
 * @param string $type name of the type to convert to
 * @throws InvalidArgumentException if arguments are not of specified type
 * @throws InvalidDataException if the index does not exist in the array
 * @return 
 */
function arrayExtract(array $array, $index, string $type){
    
    if (array_key_exists($index, $array)){
        $value = $array[$index];
        
        if (is_null($value))
            return $value;
        return DataTypes::cast($value, $type);

    }
    else
        throw new InvalidDataException("Specified index '".$index."' does not exist in given array!");
}