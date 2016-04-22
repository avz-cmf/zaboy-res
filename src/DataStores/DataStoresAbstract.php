<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\res\DataStores;

use zaboy\res\DataStores\Interfaces\DataStoresInterface;
use zaboy\res\DataStores\DataStoresException;
use zaboy\res\DataStore\Iterators\DataStoreIterator;
use Xiag\Rql\Parser\Query;
use Xiag\Rql\Parser\Node;
use Xiag\Rql\Parser\Node\SortNode;

/**
 * Abstract class for DataStores
 *
 * @todo make support null in eq(fildname, null) and ne(fildname, null)
 * @todo JsonSerializable https://github.com/zendframework/zend-diactoros/blob/master/doc/book/custom-responses.md#json-responses
 * @todo Adapter paras to config for tests
 * @todo Excel client
 * @todo SVC Store
 * @category   DataStores
 * @package    DataStores
 * @see http://en.wikipedia.org/wiki/Create,_read,_update_and_delete
 */
abstract class DataStoresAbstract implements DataStoresInterface
{

    /**
     *
     * @var \zaboy\res\DataStores\ConditionBuilder\ConditionBuilderAbstract
     */
    protected $conditionBuilder;

//** Interface "zaboy\res\DataStores\Interfaces\ReadInterface" **/

    /**
     * {@inheritdoc}
     *
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return self::DEF_ID;
    }

    /**
     * {@inheritdoc}
     *
     * {@inheritdoc}
     */
    public function read($id)
    {
        $identifier = $this->getIdentifier();
        $this->checkIdentifierType($id);
        $query = new Query();
        $eqNode = new EqNode($identifier, $id);
        $query->setQuery($eqNode);
        $queryResult = $this->query($query);
        if (empty($queryResult)) {
            return null;
        } else {
            return $queryResult[0];
        }
    }

    /**
     * {@inheritdoc}
     *
     * {@inheritdoc}
     */
    public function has($id)
    {
        return !(empty($this->read($id)));
    }

    /**
     * {@inheritdoc}
     *
     * {@inheritdoc}
     */
    public function query(Query $query)
    {
        $limitNode = $query->getLimit();
        $limit = !$limitNode ? self::LIMIT_INFINITY : $query->getLimit()->getLimit();
        $offset = !$limitNode ? 0 : $query->getLimit()->getOffset();
        if (isset($limitNode) && $query->getSort() !== null) {
            $data = $this->queryWhere($query, self::LIMIT_INFINITY, 0);
            $sortedData = $this->querySort($data, $query);
            $result = array_slice($sortedData, $offset, $limit == self::LIMIT_INFINITY ? null : $limit);
        } else {
            $data = $this->queryWhere($query, $limit, $offset);
            $result = $this->querySort($data, $query);
        }
        return $this->querySelect($result, $query);
    }

// ** Interface "zaboy\res\DataStores\Interfaces\DataStoresInterface"  **/

    /**
     * {@inheritdoc}
     *
     * {@inheritdoc}
     */
    abstract public function create($itemData, $rewriteIfExist = false);

    /**
     * {@inheritdoc}
     *
     * {@inheritdoc}
     */
    abstract public function update($itemData, $createIfAbsent = false);

    /**
     * {@inheritdoc}
     *
     * {@inheritdoc}
     */
    abstract public function delete($id);

    /**
     * {@inheritdoc}
     *
     * {@inheritdoc}
     */
    public function deleteAll()
    {
        $keys = $this->getKeys();
        $deletedItemsNumber = 0;
        foreach ($keys as $id) {
            $deletedNumber = $this->delete($id);
            if (is_null($deletedNumber)) {
                return null;
            }
            $deletedItemsNumber = $deletedItemsNumber + $deletedNumber;
        }
        return $deletedItemsNumber;
    }

// ** Interface "/Coutable"  **/

    /**
     * Interface "/Coutable"
     *
     * @see /coutable
     * @return int
     */
    public function count()
    {
        $keys = $this->getKeys();
        return count($keys);
    }

// ** Interface "/IteratorAggregate"  **/

    /**
     * Iterator for Interface IteratorAggregate
     *
     * @see IteratorAggregate
     * @return Traversable
     */
    public function getIterator()
    {
        return new DataStoreIterator($this);
    }

// ** protected  **/

    protected function querySort($data, Query $query)
    {
        if (empty($query->getSort())) {
            return $data;
        }
        $nextCompareLevel = '';
        $sortFilds = $query->getSort()->getFields();
        foreach ($sortFilds as $ordKey => $ordVal) {
            if ((int) $ordVal <> SortNode::SORT_ASC && (int) $ordVal <> SortNode::SORT_DESC) {
                throw new DataStoresException('Invalid condition: ' . $ordVal);
            }
            $cond = $ordVal == SortNode::SORT_DESC ? '<' : '>';
            $notCond = $ordVal == SortNode::SORT_ASC ? '<' : '>';

            $prevCompareLevel = "if (\$a['$ordKey'] $cond \$b['$ordKey']) {return 1;};" . PHP_EOL
                    . "if (\$a['$ordKey'] $notCond  \$b['$ordKey']) {return -1;};" . PHP_EOL
            ;
            $nextCompareLevel = $nextCompareLevel . $prevCompareLevel;
        }
        $sortFunctionBody = $nextCompareLevel . 'return 0;';
        $sortFunction = create_function('$a,$b', $sortFunctionBody);
        usort($data, $sortFunction);
        return $data;
    }

    protected function querySelect($data, Query $query)
    {
        $selectNode = $query->getSelect();
        if (empty($selectNode)) {
            return $data;
        } else {
            $resultArray = array();
            foreach ($data as $item) {
                $resultArray[] = array_intersect_key($item, array_flip($selectNode->getFields()));
            }
            return $resultArray;
        }
    }

    protected function queryWhere(Query $query, $limit, $offset)
    {
        $conditionBuilder = $this->conditionBuilder;
        $conditioon = $conditionBuilder($query->getQuery());
        $whereFunctionBody = PHP_EOL .
                '$result = ' . PHP_EOL
                . rtrim($conditioon, PHP_EOL) . ';' . PHP_EOL
                . 'return $result;'
        ;
        $whereFunction = create_function('$item', $whereFunctionBody);
        $suitableItemsNumber = 0;
        $result = [];
        foreach ($this as $value) {
            switch (true) {
                case (!($whereFunction($value))):
                    break; // skip!
                case $suitableItemsNumber < $offset:
                    $suitableItemsNumber = $suitableItemsNumber + 1;
                    break; // increment!
                case $limit <> self::LIMIT_INFINITY && $suitableItemsNumber >= ($limit + $offset):
                    return $result; //enough!
                default:
                    $result[] = $value; // write!
                    $suitableItemsNumber = $suitableItemsNumber + 1;
            }
        }
        return $result;
    }

    /**
     * Return array of keys or empty array
     *
     * @return array array of keys or empty array
     */
    protected function getKeys()
    {
        $identifier = $this->getIdentifier();
        $query = new Query();
        $selectNode = new Node\SelectNode([$identifier]);
        $query->setSelect($selectNode);
        $queryResult = $this->query($query);
        $keysArray = [];
        foreach ($queryResult as $row) {
            $keysArray[] = $row[$identifier];
        }
        return $keysArray;
    }

    /**
     * Throw Exception if type of Identifier is wrong
     *
     * @param mix $id
     */
    protected function checkIdentifierType($id)
    {
        $idType = gettype($id);
        if ($idType == 'integer' || $idType == 'double' || $idType == 'string') {
            return;
        } else {
            throw new DataStoresException("Type of Identifier is wrong - " . $idType);
        }
    }

}
