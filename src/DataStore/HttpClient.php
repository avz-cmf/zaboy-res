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
use zaboy\res\DataStore\ConditionBuilder\RqlConditionBuilder;
use Xiag\Rql\Parser\Query;
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
    public function __construct($url, $options = null)
    {
        $this->url = rtrim(trim($url), '/');
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
        $this->conditionBuilder = new RqlConditionBuilder;
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
        $client = $this->initHttpClient(Request::METHOD_GET, null, $id);
        $response = $client->send();
        if ($response->isOk()) {
            $result = $this->jsonDecode($response->getBody());
        } else {
            throw new DataStoresException(
            'Status: ' . $response->getStatusCode()
            . ' - ' . $response->getReasonPhrase()
            );
        }
        return $result;
    }

    /**
     * {@inheritdoc}
     *
     * {@inheritdoc}
     */
    public function query(Query $query)
    {
        $client = $this->initHttpClient(Request::METHOD_GET, $query);
        $response = $client->send();
        if ($response->isOk()) {
            $result = $this->jsonDecode($response->getBody());
        } else {
            throw new DataStoresException(
            'Status: ' . $response->getStatusCode()
            . ' - ' . $response->getReasonPhrase()
            );
        }
        return $result;
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
        if (isset($itemData[$identifier])) {
            $id = $itemData[$identifier];
            $this->checkIdentifierType($id);
        } else {
            $id = null;
        }
        $client = $this->initHttpClient(Request::METHOD_POST, null, $id, $rewriteIfExist);
        $json = $this->jsonEncode($itemData);
        $client->setRawBody($json);
        $response = $client->send();
        if ($response->isSuccess()) {
            $result = $this->jsonDecode($response->getBody());
        } else {
            throw new DataStoresException(
            'Status: ' . $response->getStatusCode()
            . ' - ' . $response->getReasonPhrase()
            );
        }
        return $result;
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
        $client = $this->initHttpClient(Request::METHOD_PUT, null, $id, $createIfAbsent);
        $client->setRawBody($this->jsonEncode($itemData));
        $response = $client->send();
        if ($response->isSuccess()) {
            $result = $this->jsonDecode($response->getBody());
        } else {
            throw new DataStoresException(
            'Status: ' . $response->getStatusCode()
            . ' - ' . $response->getReasonPhrase()
            );
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
        $this->checkIdentifierType($id);
        $client = $this->initHttpClient(Request::METHOD_DELETE, null, $id);
        $response = $client->send();
        if ($response->isSuccess()) {
            $result = $this->jsonDecode($response->getBody());
        } else {
            throw new DataStoresException(
            'Status: ' . $response->getStatusCode()
            . ' - ' . $response->getReasonPhrase()
            );
        }
        return $result;
    }

    /**
     * {@inheritdoc}
     *
     * {@inheritdoc}
     */
    public function count()
    {
        return parent::count();
    }

// ** protected  **/

    protected function rqlEncode(Query $query)
    {
        $conditionBuilder = $this->conditionBuilder;
        $rqlQueryString = $conditionBuilder($query->getQuery());
        $rqlQueryString = $rqlQueryString . $this->makeLimit($query);
        $rqlQueryString = $rqlQueryString . $this->makeSort($query);
        $rqlQueryString = $rqlQueryString . $this->makeSelect($query);
        return ltrim($rqlQueryString, '&');
    }

    protected function makeLimit(Query $query)
    {
        $limitNode = $query->getLimit();
        $limit = !$limitNode ? DataStoresAbstract::LIMIT_INFINITY : $limitNode->getLimit();
        $offset = !$limitNode ? 0 : $limitNode->getOffset();
        if ($limit == DataStoresAbstract::LIMIT_INFINITY && $offset == 0) {
            return '';
        } else {
            return sprintf('&limit(%s,%s)', $limit, $offset);
        }
    }

    protected function makeSort(Query $query)
    {
        $sortNode = $query->getSort();
        $sortFilds = !$sortNode ? [] : $sortNode->getFields();
        if (empty($sortFilds)) {
            return '';
        } else {
            $strSort = '';
            foreach ($sortFilds as $key => $value) {
                $prefix = $value == SortNode::SORT_DESC ? '-' : '+';
                $strSort = $strSort . $prefix . $key . ',';
            }
            return '&sort(' . rtrim($strSort, ',') . ')';
        }
    }

    protected function makeSelect(Query $query)
    {
        $selectNode = $query->getSelect();  //What filds will be return
        $selectFilds = !$selectNode ? [] : $selectNode->getFields();
        if (empty($selectFilds)) {
            return '';
        } else {
            $selectString = '&select(';
            foreach ($selectFilds as $fild) {
                $selectString = $selectString . $fild . ',';
            }
            return rtrim($selectString, ',') . ')';
        }
    }

    /**
     *
     * @param string  'GET' 'HEAD' 'POST' 'PUT' 'DELETE';
     * @param Query $query
     * @param int|string $id
     * @param bool see $ifMatch $rewriteIfExist and $createIfAbsent in {@see DataStoresAbstract}
     * @return Client
     */
    protected function initHttpClient($method, Query $query = null, $id = null, $ifMatch = false)
    {

        $url = !$id ? $this->url : $this->url . '/' . $id;
        if (isset($query)) {
            $rqlString = $this->rqlEncode($query);
            $url = $url . '?' . $rqlString;
        }
        $httpClient = new Client($url, $this->options);
        $headers['Content-Type'] = 'application/json';
        $headers['Accept'] = 'application/json';
        if ($ifMatch) {
            $headers['If-Match'] = '*';
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
        json_encode(null); // Clear json_last_error()
        $result = Json::decode($data, Json::TYPE_ARRAY); //json_decode($data);
        if (JSON_ERROR_NONE !== json_last_error()) {
            $jsonErrorMsg = json_last_error_msg();
            json_encode(null);  // Clear json_last_error()
            throw new DataStoresException(
            'Unable to decode data from JSON - ' . $jsonErrorMsg
            );
        }
        return $result;
    }

    protected function jsonEncode($data)
    {
        json_encode(null); // Clear json_last_error()
        $result = json_encode($data, 79);
        if (JSON_ERROR_NONE !== json_last_error()) {
            $jsonErrorMsg = json_last_error_msg();
            json_encode(null);  // Clear json_last_error()
            throw new DataStoresException(
            'Unable to encode data to JSON - ' . $jsonErrorMsg
            );
        }
        return $result;
    }

}
