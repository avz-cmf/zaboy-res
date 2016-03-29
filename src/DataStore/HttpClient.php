<?php
/**
 * Zaboy lib (http://zaboy.org/lib/)
 * 
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\res\DataStore;

use zaboy\res\DataStores\DataStoresAbstract;
use zaboy\res\DataStores\DataStoresException;
use zaboy\res\DataStore\ConditionBuilder\RqlConditionBuilder;
use Xiag\Rql\Parser\Query;
use Xiag\Rql\Parser\Node\AbstractQueryNode;
use Xiag\Rql\Parser\Node\SortNode;
use Zend\Http\Client;
use Zend\Http\Request;
use Zend\Json\Json;


/**
 * DataStores as http Client
 * 
 * @category   DataStores
 * @package    DataStores
 * @uses Zend\Http\Client
 * @see https://github.com/zendframework/zend-db
 * @see http://en.wikipedia.org/wiki/Create,_read,_update_and_delete 
 */
class HttpClient extends DataStoresAbstract
{    
    /**
     * @var string 'http://example.org'
     */
    protected $url;
    
    /**
     * @var string 'mylogin'
     * @see https://en.wikipedia.org/wiki/Basic_access_authentication
     */
    protected $login;    
    
     
    /**
     * @var string 'kjfgn&56Ykjfnd'
     * @see https://en.wikipedia.org/wiki/Basic_access_authentication
     */
    protected $password;      
    
    /**
     * @var array
     */
    protected $options = [];
    
    /**
     * 
     * @param string $url  'http://example.org'
     * @param array $options
     */
    public function __construct($url, $options = null, ConditionBuilderAbstract $conditionBuilder = null)
    {
        parent::__construct($options);
        $this->url = rtrim(trim($url),'/');        
        if (is_array($options)) {
            if (isset($options['login']) && isset($options['password'])) {
                $this->login = $options['login'];
                $this->password = $options['password'];
            }
            $supportedKeys = [
                'maxredirects',
                'useragent',
                'timeout',
            ];
            $this->options = array_intersect_key($options, array_flip($supportedKeys));
        }
        if ( isset($conditionBuilder)) {
            $this->_conditionBuilder = $conditionBuilder;
        }  else {
            $this->_conditionBuilder = new RqlConditionBuilder;
        }
    }        
            
            
    /**
     * Return Item by id
     * 
     * Method return null if item with that id is absent.
     * Format of Item - Array("id"=>123, "fild1"=value1, ...)
     * 
     * @param int|string|float $id PrimaryKey
     * @return array|null
     */
    public function read($id)
    {
        $this->_checkIdentifierType($id);
        $client = $this->initHttpClient(Request::METHOD_GET, null, $id);
        $response = $client->send();
        if ($response->isOk()) {
            $result = $this->jsonDecode($response->getBody());
        }else{
            throw new DataStoresException(
                'Status: ' . $response->getStatusCode() 
                . ' - ' . $response->getReasonPhrase()    
            );
        }
        return $result;
    }

    /**
     * By default, insert new (by create) Item. 
     * 
     * It can't overwrite existing item by default. 
     * You can get item "id" for creatad item us result this function.
     * 
     * If  $item["id"] !== null, item set with that id. 
     * If item with same id already exist - method will throw exception, 
     * but if $rewriteIfExist = true item will be rewrited.<br>
     * 
     * If $item["id"] is not set or $item["id"]===null, 
     * item will be insert with autoincrement PrimryKey.<br>
     * 
     * @param array $itemData associated array with or without PrimaryKey
     * @return int|string|null  "id" for creatad item
     */
    public function create($itemData, $rewriteIfExist = false) {
        $identifier = $this->getIdentifier();
        if (isset($itemData[$identifier])) {
            $id = $itemData[$identifier];
            $this->_checkIdentifierType($id);
        }else{
            $id = null;
        }
        $client = $this->initHttpClient(Request::METHOD_POST, null, $id, $rewriteIfExist);
        $json = $this->jsonEncode($itemData);
        $client->setRawBody($json);
        $response = $client->send();
        if ($response->isSuccess()) {
            $result = $this->jsonDecode($response->getBody());
        }else{
            throw new DataStoresException(
                'Status: ' . $response->getStatusCode() 
                . ' - ' . $response->getReasonPhrase()    
            );
        }
        return $result;
    }

    /**
     * By default, update existing Item.
     * 
     * If item with PrimaryKey == $item["id"] is existing in store, item will updete.
     * Filds wich don't present in $item will not change in item in store.<br>
     * Method will return updated item<br>
     * <br>
     * If $item["id"] isn't set - method will throw exception.<br>
     * <br>
     * If item with PrimaryKey == $item["id"] is absent - method  will throw exception,<br>
     * but if $createIfAbsent = true item will be created and method return inserted item<br>
     * <br>
     * 
     * @param array $itemData associated array with PrimaryKey
     * @return array updated item or inserted item
     */
    public function update($itemData, $createIfAbsent = false) {
        $identifier = $this->getIdentifier();
        if (!isset($itemData[$identifier])) {
            throw new DataStoresException('Item must has primary key'); 
        }
        $id = $itemData[$identifier];
        $this->_checkIdentifierType($id);
        $client = $this->initHttpClient(Request::METHOD_PUT, null, $id, $createIfAbsent);
        $client->setRawBody($this->jsonEncode($itemData));
        $response = $client->send();
        if ($response->isSuccess()) {
            $result = $this->jsonDecode($response->getBody());
        }else{
            throw new DataStoresException(
                'Status: ' . $response->getStatusCode() 
                . ' - ' . $response->getReasonPhrase()    
            );
        }
        return $result;
    }

     /**
      * Delete Item by id. Method do nothing if item with that id is absent.
      * 
      * @param int|string $id PrimaryKey
      * @return int number of deleted items: 0 or 1
      */
    public function delete($id) {
        $identifier = $this->getIdentifier();
        $this->_checkIdentifierType($id);
        $client = $this->initHttpClient(Request::METHOD_DELETE, null, $id);
        $response = $client->send();
        if ($response->isSuccess()) {
            $result = $this->jsonDecode($response->getBody());
        }else{
            throw new DataStoresException(
                'Status: ' . $response->getStatusCode() 
                . ' - ' . $response->getReasonPhrase()    
            );
        }
        return $result;
    }  
    
    /**
     * @see coutable
     * @return int
     */
    public function count() {
        return parent::count();
    }    

    public function query(Query $query) 
    {
var_dump('Http   public function query( -------->');
        $client = $this->initHttpClient(Request::METHOD_GET, $query);
        $response = $client->send();
        if ($response->isOk()) {
            $result = $this->jsonDecode($response->getBody());
        }else{
            throw new DataStoresException(
                'Status: ' . $response->getStatusCode() 
                . ' - ' . $response->getReasonPhrase()    
            );
        }
        return $result;
    }
    
    public function  rqlEncode(Query $query) 
    {
var_dump('Http   public function rqlEncode( -------->');
        $rqlQueryString = $this->getQueryWhereConditioon($query->getQuery());
        $rqlQueryString = $this->makeLimit($query, $rqlQueryString);
        $rqlQueryString = $this->makeSort($query, $rqlQueryString);     
        $rqlQueryString = $this->makeSelect($query, $rqlQueryString);  
var_dump('Astruct where -------->' . $rqlQueryString);
        return ltrim($rqlQueryString,'&');
    }

    public function  makeLimit(Query $query, $rqlQueryString) 
    {
        $objLimit = $query->getLimit();
        $limit = !$objLimit ? DataStoresAbstract::LIMIT_INFINITY : $objLimit->getLimit();
        $offset =  !$objLimit ? 0 : $objLimit->getOffset();
        if ($limit == DataStoresAbstract::LIMIT_INFINITY && $offset == 0) {
            return $rqlQueryString;     
        }else{
            $rqlQueryString =  $rqlQueryString . sprintf('&limit(%s,%s)',$limit, $offset);
            return $rqlQueryString;      
        }  
    }

    public function  makeSort(Query $query, $rqlQueryString) 
    {
        $objSort = $query->getSort();
        $sortFilds = !$objSort ? [] : $objSort->getFields();
        if (empty($sortFilds)) {
            return $rqlQueryString;      
        }else{
            $strSelect =  'sort(';
            foreach ($sortFilds as $key => $value) {
                $prefix = $value == SortNode::SORT_DESC ? '-' : '+';
                $strSelect =  $strSelect . $prefix . $key . ',';
            }
            $rqlQueryString = $rqlQueryString . rtrim($strSelect, ',') . ')';
            return $rqlQueryString;      
        }  
    }

    public function makeSelect(Query $query, $rqlQueryString) 
    {
        $objSelect = $query->getSelect();  //What filds will return
        $selectFilds = !$objSelect ? [] : $objSelect->getFields();
        if (empty($selectFilds)) {
            return $rqlQueryString;   
        }else{
            $rqlQueryString =  $rqlQueryString . '&select(' . implode(',', $selectFilds) . ')';
            return $rqlQueryString;      
        }  
    }
    
    /**
     * 
     * @param string $method
     * @param Query $rqlQuery
     * @param string $id
     * @param bool $ifMatch see $createIfAbsent and $rewriteIfExist
     * @return Client
     */
    protected function initHttpClient($method, Query $query = null, $id = null, $ifMatch = false)
    {
        
        $url = !$id ? $this->url : $this->url . '/' . $id;
        if (isset($query)) {
            $rqlString = $this->rqlEncode($query);
            $url = $url . '?' . $rqlString;
        }
var_dump('Http   public function initHttpClient( -------->' . $url);
        $httpClient = new Client($url, $this->options);
        $headers['Content-Type'] =  'application/json';
        $headers['Accept'] =  'application/json';
        if ($ifMatch) {
            $headers['If-Match'] =  '*';
        }
        $httpClient->setHeaders($headers);
        if (isset($this->login) && isset($this->password)) {
            $httpClient->setAuth($this->login, $this->password);
        }
        $httpClient->setMethod($method);
        return $httpClient;
    }
    
    protected function jsonDecode($data)
    {
        // Clear json_last_error()
        json_encode(null);

        $result = Json::decode($data, Json::TYPE_ARRAY);//json_decode($data);
        json_encode(null);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new DataStoresException(
                'Unable to decode data from JSON' .
                json_last_error_msg()
            );
        }

        return $result;
    }
   
    
    /**
     * Decode the provided data to JSON.
     *
     * @param mixed $data
     * @param int $encodingOptions
     * @return string
     * @throws InvalidArgumentException if unable to encode the $data to JSON.
     */
    protected function jsonEncode($data)
    {
        // Clear json_last_error()
        json_encode(null);

        $result = json_encode($data, 79);
        json_encode(null);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new DataStoresException(
                'Unable to encode data to JSON' .
                json_last_error_msg()
            );
        }

        return $result;
    }
    
    protected function getQueryWhereConditioon(AbstractQueryNode $queryNode = null)
    {
        $conditionBuilder = $this->_conditionBuilder;
        return $conditionBuilder($queryNode);
    }    
}    