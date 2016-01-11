<?php
namespace zaboy\res\NameSpase;

/**
 * <b>Zaboy_Dic</b><br>
 * assert
 */
class NameOfClass
{
    /**
     * Return Service if is described or just object with injected dependencies
     * 
     * @assert (1, 2) == 3
     * @param string $name
     * @param string|null $class
     * @return object|null
     */
    public function sumAB($a, $b)    
    {
        return $a + $b;
    }

         
}