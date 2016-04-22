<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\res\DataStore;

use zaboy\res\DataStore\DataStoresAbstract;
use zaboy\res\DataStore\DataStoresException;
use zaboy\res\DataStore\ConditionBuilder\SqlConditionBuilder;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;
use Xiag\Rql\Parser\Query;
use Xiag\Rql\Parser\Node\SortNode;

/**
 * DataStores as Db Table
 *
 * @category   DataStores
 * @package    DataStores
 * @uses zend-db
 * @see https://github.com/zendframework/zend-db
 * @see http://en.wikipedia.org/wiki/Create,_read,_update_and_delete
 */
class DbTable extends DataStoresAbstract
{

    /**
     *
     * @var \Zend\Db\TableGateway\TableGateway
     */
    protected $dbTable;

    /**
     *
     * @param TableGateway $dbTable
     */
    public function __construct(TableGateway $dbTable)
    {
        $this->dbTable = $dbTable;
        $db = $dbTable->getAdapter();
        $this->conditionBuilder = new SqlConditionBuilder($db);
    }

//** Interface "zaboy\res\DataStore\Interfaces\ReadInterface" **/

    /**
     * {@inheritdoc}
     *
     * {@inheritdoc}
     */
    public function read($id)
    {
        $this->checkIdentifierType($id);
        $identifier = $this->getIdentifier();
        $rowset = $this->dbTable->select(array($identifier => $id));
        $row = $rowset->current();
        if (isset($row)) {
            return $row->getArrayCopy();
        } else {
            return null;
        }
    }

    /**
     * {@inheritdoc}
     *
     * {@inheritdoc}
     */
    public function query(Query $query)
    {
        $limits = $query->getLimit();
        $limit = !$limits ? self::LIMIT_INFINITY : $query->getLimit()->getLimit();
        $offset = !$limits ? 0 : $query->getLimit()->getOffset();
        $sort = $query->getSort();
        $sortFilds = !$sort ? [$this->getIdentifier() => SortNode::SORT_ASC] : $sort->getFields();
        $select = $query->getSelect();  //What filds will return
        $selectFilds = !$select ? [] : $select->getFields();
        $selectSQL = $this->dbTable->getSql()->select();
        // ***********************   where   ***********************
        $conditionBuilder = $this->conditionBuilder;
        $where = $conditionBuilder($query->getQuery());
        $selectSQL->where($where);
        // ***********************   order   ***********************
        foreach ($sortFilds as $ordKey => $ordVal) {
            if ((int) $ordVal === SortNode::SORT_DESC) {
                $selectSQL->order($ordKey . ' ' . Select::ORDER_DESCENDING);
            } else {
                $selectSQL->order($ordKey . ' ' . Select::ORDER_ASCENDING);
            }
        }
        // *********************  limit, offset   ***********************
        if ($limit <> self::LIMIT_INFINITY) {
            $selectSQL->limit($limit);
        }
        if ($offset <> 0) {
            $selectSQL->offset($offset);
        }
        // *********************  filds  ***********************
        if (!empty($selectFilds)) {

            $selectSQL->columns($selectFilds);
        }
        // ***********************   return   ***********************

        $rowset = $this->dbTable->selectWith($selectSQL);
        return $rowset->toArray();
    }

// ** Interface "zaboy\res\DataStore\Interfaces\DataStoresInterface"  **/

    /**
     * {@inheritdoc}
     *
     * {@inheritdoc}
     */
    public function create($itemData, $rewriteIfExist = false)
    {

        $identifier = $this->getIdentifier();
        $adapter = $this->dbTable->getAdapter();
        // begin Transaction
        $errorMsg = 'Can\'t start insert transaction';
        $adapter->getDriver()->getConnection()->beginTransaction();
        try {
            if (isset($itemData[$identifier]) && $rewriteIfExist) {
                $errorMsg = 'Can\'t delete item with "id" = ' . $itemData[$identifier];
                $this->dbTable->delete(array($identifier => $itemData[$identifier]));
            }
            $errorMsg = 'Can\'t insert item';
            $rowsCount = $this->dbTable->insert($itemData);
            $adapter->getDriver()->getConnection()->commit();
        } catch (\Exception $e) {
            $adapter->getDriver()->getConnection()->rollback();
            throw new DataStoresException($errorMsg, 0, $e);
        }

        $id = $this->dbTable->getLastInsertValue();
        $newItem = array_merge(array($identifier => $id), $itemData);
        return $newItem;
    }

    /**
     * {@inheritdoc}
     *
     * {@inheritdoc}
     */
    public function update($itemData, $createIfAbsent = false)
    {
        $identifier = $this->getIdentifier();
        if (!isset($itemData[$identifier])) {
            throw new DataStoresException('Item must has primary key');
        }
        $id = $itemData[$identifier];
        $this->checkIdentifierType($id);
        $adapter = $this->dbTable->getAdapter();
        $errorMsg = 'Can\'t update item with "id" = ' . $id;
        $queryStr = 'SELECT ' . Select::SQL_STAR
                . ' FROM ' . $adapter->platform->quoteIdentifier($this->dbTable->getTable())
                . ' WHERE ' . $adapter->platform->quoteIdentifier($identifier) . ' = ?'
                . ' FOR UPDATE';
        $adapter->getDriver()->getConnection()->beginTransaction();
        try {
            //is row with this index exist?
            $rowset = $adapter->query($queryStr, array($id));
            $isExist = !is_null($rowset->current());
            switch (true) {
                case!$isExist && !$createIfAbsent:
                    throw new DataStoresException($errorMsg);
                case!$isExist && $createIfAbsent:
                    $this->dbTable->insert($itemData);
                    $result = $itemData;
                    break;
                case $isExist:
                    unset($itemData[$identifier]);
                    $this->dbTable->update($itemData, array($identifier => $id));
                    $rowset = $adapter->query($queryStr, array($id));
                    $result = $rowset->current()->getArrayCopy();
                    break;
            }
            $adapter->getDriver()->getConnection()->commit();
        } catch (\Exception $e) {
            $adapter->getDriver()->getConnection()->rollback();
            throw new DataStoresException($errorMsg, 0, $e);
        }
        return $result;
    }

    /**
     * {@inheritdoc}
     *
     * {@inheritdoc}
     */
    public function delete($id)
    {
        $identifier = $this->getIdentifier();
        $this->checkIdentifierType($id);
        $deletedItemsCount = $this->dbTable->delete(array($identifier => $id));
        return $deletedItemsCount;
    }

    /**
     * {@inheritdoc}
     *
     * {@inheritdoc}
     */
    public function deleteAll()
    {
        $where = '1=1';
        $deletedItemsCount = $this->dbTable->delete($where);
        return $deletedItemsCount;
    }

    /**
     * {@inheritdoc}
     *
     * {@inheritdoc}
     */
    public function count()
    {
        $adapter = $this->dbTable->getAdapter();
        /* @var $rowset Zend\Db\ResultSet\ResultSet */
        $rowset = $adapter->query(
                'SELECT COUNT(*) AS count FROM '
                . $adapter->platform->quoteIdentifier($this->dbTable->getTable())
                , $adapter::QUERY_MODE_EXECUTE);
        return $rowset->current()['count'];
    }

// ** protected  **/

    /**
     * {@inheritdoc}
     *
     * {@inheritdoc}
     */
    protected function getKeys()
    {
        $identifier = $this->getIdentifier();
        $select = $this->dbTable->getSql()->select();
        $select->columns(array($identifier));
        $rowset = $this->dbTable->selectWith($select);
        $keysArrays = $rowset->toArray();
        if (PHP_VERSION_ID >= 50500) {
            $keys = array_column($keysArrays, $identifier);
        } else {
            $keys = array();
            foreach ($keysArrays as $value) {
                $keys[] = $value[$identifier];
            }
        }
        return $keys;
    }

}
