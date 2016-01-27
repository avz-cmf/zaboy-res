<?php
/**
 * Zaboy lib (http://zaboy.org/lib/)
 * 
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\res\DataStores\Tree;

interface Avz_DataStore_Tree_Read_Interface extends Avz_DataStore_Read_Interface
{

    /**
    * @return array|object|null
    */
    public function getRootCollection();
            
    /**
    * @param mixed 
    * @return array|object|null
    */
    public function getParent($id);
    
    /**
    * @param mixed 
    * @return array|object ArrayAccess,Traversable,Countable
    */
    public function getChildren($id);
    
    /**
    * @param mixed 
    * @return boool
    */
    public function mayHaveChildren($id);    
    
    /**
    * IT's true if $ancestor is (sub)parent for $id)
    * 
    * @param mixed $id possibly ancestor
    * @param mixed $descendantId possibly descendant
    * @return bool
    */
    public function isAncestor($id, $descendantId);    
}