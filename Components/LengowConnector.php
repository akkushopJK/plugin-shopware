<?php
/**
 * Copyright 2017 Lengow SAS
 *
 * NOTICE OF LICENSE
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * It is available through the world-wide-web at this URL:
 * https://www.gnu.org/licenses/agpl-3.0
 *
 * @category    Lengow
 * @package     Lengow
 * @subpackage  Components
 * @author      Team module <team-module@lengow.com>
 * @copyright   2017 Lengow SAS
 * @license     https://www.gnu.org/licenses/agpl-3.0 GNU Affero General Public License, version 3
 */

use Shopware_Plugins_Backend_Lengow_Components_LengowCheck as LengowCheck;
use Shopware_Plugins_Backend_Lengow_Components_LengowConfiguration as LengowConfiguration;
use Shopware_Plugins_Backend_Lengow_Components_LengowConnector as LengowConnector;
use Shopware_Plugins_Backend_Lengow_Components_LengowException as LengowException;
use Shopware_Plugins_Backend_Lengow_Components_LengowLog as LengowLog;
use Shopware_Plugins_Backend_Lengow_Components_LengowMain as LengowMain;

/**
 * Lengow Connector Class
 */
class Shopware_Plugins_Backend_Lengow_Components_LengowConnector
{
    /**
     * @var string url of Lengow solution
     */
    // const LENGOW_URL = 'lengow.io';
    // const LENGOW_URL = 'lengow.net';
    const LENGOW_URL = 'rec.lengow.hom';
    // const LENGOW_URL = 'dev.lengow.hom';

    /**
     * @var string url of the Lengow API
     */
    // const LENGOW_API_URL = 'https://api.lengow.io';
    // const LENGOW_API_URL = 'https://api.lengow.net';
    const LENGOW_API_URL = 'http://api.lengow.rec';
    // const LENGOW_API_URL = 'http://10.100.1.82:8081';

    /**
     * @var string url of access token API
     */
    const API_ACCESS_TOKEN = '/access/get_token';

    /**
     * @var string url of order API
     */
    const API_ORDER = '/v3.0/orders';

    /**
     * @var string url of order merchant order id API
     */
    const API_ORDER_MOI = '/v3.0/orders/moi/';

    /**
     * @var string url of order action API
     */
    const API_ORDER_ACTION = '/v3.0/orders/actions/';

    /**
     * @var string url of marketplace API
     */
    const API_MARKETPLACE = '/v3.0/marketplaces';

    /**
     * @var string url of plan API
     */
    const API_PLAN = '/v3.0/plans';

    /**
     * @var string url of cms API
     */
    const API_CMS = '/v3.1/cms';

    /**
     * @var string url of plugin API
     */
    const API_PLUGIN = '/v3.0/plugins';

    /**
     * @var string request GET
     */
    const GET = 'GET';

    /**
     * @var string request POST
     */
    const POST = 'POST';

    /**
     * @var string request PUT
     */
    const PUT = 'PUT';

    /**
     * @var string request PATCH
     */
    const PATCH = 'PATCH';

    /**
     * @var string json format return
     */
    const FORMAT_JSON = 'json';

    /**
     * @var string stream format return
     */
    const FORMAT_STREAM = 'stream';

    /**
     * @var string success code
     */
    const CODE_200 = 200;

    /**
     * @var string success create code
     */
    const CODE_201 = 201;

    /**
     * @var string forbidden access code
     */
    const CODE_403 = 403;

    /**
     * @var string error server code
     */
    const CODE_500 = 500;

    /**
     * @var string timeout server code
     */
    const CODE_504 = 504;

    /**
     * @var array success HTTP codes for request
     */
    protected $successCodes = array(
        self::CODE_200,
        self::CODE_201,
    );

    /**
     * @var integer Authorization token lifetime
     */
    protected $tokenLifetime = 3000;

    /**
     * @var array default options for Curl
     */
    protected $curlOpts = array(
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_USERAGENT => 'lengow-cms-shopware',
    );

    /**
     * @var string access token to connect
     */
    protected $accessToken;

    /**
     * @var string secret to connect
     */
    protected $secret;

    /**
     * @var string temporary token for the authorization
     */
    protected $token;

    /**
     * @var array lengow url for Curl timeout
     */
    protected $lengowUrls = array(
        self::API_ORDER => 20,
        self::API_ORDER_MOI => 10,
        self::API_ORDER_ACTION => 15,
        self::API_MARKETPLACE => 15,
        self::API_PLAN => 5,
        self::API_CMS => 5,
        self::API_PLUGIN => 5,
    );

    /**
     * Make a new Lengow API Connector
     *
     * @param string $accessToken your access token
     * @param string $secret your secret
     */
    public function __construct($accessToken, $secret)
    {
        $this->accessToken = $accessToken;
        $this->secret = $secret;
    }

    /**
     * Check API authentication
     *
     * @param boolean $logOutput see log or not
     *
     * @return boolean
     */
    public static function isValidAuth($logOutput = false)
    {
        if (!LengowCheck::isCurlActivated()) {
            return false;
        }
        $accessIds = LengowConfiguration::getAccessIds();
        list($accountId, $accessToken, $secretToken) = $accessIds;
        if ($accountId === null || (int)$accountId === 0 || !is_numeric($accountId)) {
            return false;
        }
        $connector = new LengowConnector($accessToken, $secretToken);
        try {
            $connector->connect();
        } catch (LengowException $e) {
            $message = LengowMain::decodeLogMessage($e->getMessage());
            $error = LengowMain::setLogMessage(
                'log/connector/error_api',
                array(
                    'error_code' => $e->getCode(),
                    'error_message' => $message,
                )
            );
            LengowMain::log(LengowLog::CODE_CONNECTOR, $error, $logOutput);
            return false;
        }
        return true;
    }

    /**
     * Get result for a query Api
     *
     * @param string $type request type (GET / POST / PUT / PATCH)
     * @param string $api request api
     * @param array $args request params
     * @param string $body body data for request
     * @param boolean $logOutput see log or not
     *
     * @return mixed
     */
    public static function queryApi($type, $api, $args = array(), $body = '', $logOutput = false)
    {
        if (!in_array($type, array(self::GET, self::POST, self::PUT, self::PATCH))) {
            return false;
        }
        try {
            $accessIds = LengowConfiguration::getAccessIds();
            list($accountId, $accessToken, $secretToken) = $accessIds;
            if ($accountId === null) {
                return false;
            }
            $connector = new LengowConnector($accessToken, $secretToken);
            $type = strtolower($type);
            $results = $connector->$type(
                $api,
                array_merge(array('account_id' => $accountId), $args),
                self::FORMAT_STREAM,
                $body,
                $logOutput
            );
        } catch (LengowException $e) {
            $message = LengowMain::decodeLogMessage($e->getMessage());
            $error = LengowMain::setLogMessage(
                'log/connector/error_api',
                array(
                    'error_code' => $e->getCode(),
                    'error_message' => $message,
                )
            );
            LengowMain::log(LengowLog::CODE_CONNECTOR, $error, $logOutput);
            return false;
        }
        return json_decode($results);
    }

    /**
     * Connection to the API
     *
     * @param boolean $force Force cache Update
     * @param boolean $logOutput see log or not
     *
     * @throws LengowException
     */
    public function connect($force = false, $logOutput = false)
    {
        $token = LengowConfiguration::getConfig('lengowAuthorizationToken');
        $updatedAt = LengowConfiguration::getConfig('lengowLastAuthorizationTokenUpdate');
        if (!$force
            && $token !== null
            && strlen($token) > 0
            && $updatedAt !== null
            && (time() - $updatedAt) < $this->tokenLifetime
        ) {
            $authorizationToken = $token;
        } else {
            $authorizationToken = $this->getAuthorizationToken($logOutput);
            LengowConfiguration::setConfig('lengowAuthorizationToken', $authorizationToken);
            LengowConfiguration::setConfig('lengowLastAuthorizationTokenUpdate', time());
        }
        $this->token = $authorizationToken;
    }

    /**
     * Get API call
     *
     * @param string $api Lengow method API call
     * @param array $args Lengow method API parameters
     * @param string $format return format of API
     * @param string $body body data for request
     * @param boolean $logOutput see log or not
     *
     * @throws LengowException
     *
     * @return mixed
     */
    public function get($api, $args = array(), $format = self::FORMAT_JSON, $body = '', $logOutput = false)
    {
        return $this->call($api, $args, self::GET, $format, $body, $logOutput);
    }

    /**
     * Post API call
     *
     * @param string $api Lengow method API call
     * @param array $args Lengow method API parameters
     * @param string $format return format of API
     * @param string $body body data for request
     * @param boolean $logOutput see log or not
     *
     * @throws LengowException
     *
     * @return mixed
     */
    public function post($api, $args = array(), $format = self::FORMAT_JSON, $body = '', $logOutput = false)
    {
        return $this->call($api, $args, self::POST, $format, $body, $logOutput);
    }

    /**
     * Put API call
     *
     * @param string $api Lengow method API call
     * @param array $args Lengow method API parameters
     * @param string $format return format of API
     * @param string $body body data for request
     * @param boolean $logOutput see log or not
     *
     * @throws LengowException
     *
     * @return mixed
     */
    public function put($api, $args = array(), $format = self::FORMAT_JSON, $body = '', $logOutput = false)
    {
        return $this->call($api, $args, self::PUT, $format, $body, $logOutput);
    }

    /**
     * Patch API call
     *
     * @param string $api Lengow method API call
     * @param array $args Lengow method API parameters
     * @param string $format return format of API
     * @param string $body body data for request
     * @param boolean $logOutput see log or not
     *
     * @throws LengowException
     *
     * @return mixed
     */
    public function patch($api, $args = array(), $format = self::FORMAT_JSON, $body = '', $logOutput = false)
    {
        return $this->call($api, $args, self::PATCH, $format, $body, $logOutput);
    }

    /**
     * The API method
     *
     * @param string $api Lengow method API call
     * @param array $args Lengow method API parameters
     * @param string $type type of request GET|POST|PUT|PATCH
     * @param string $format return format of API
     * @param string $body body data for request
     * @param boolean $logOutput see log or not
     *
     * @throws LengowException
     *
     * @return mixed
     */
    private function call($api, $args, $type, $format, $body, $logOutput)
    {
        try {
            $this->connect(false, $logOutput);
            $data = $this->callAction($api, $args, $type, $format, $body, $logOutput);
        } catch (LengowException $e) {
            if ($e->getCode() === self::CODE_403) {
                LengowMain::log(
                    LengowLog::CODE_CONNECTOR,
                    LengowMain::setLogMessage('log/connector/retry_get_token'),
                    $logOutput
                );
                $this->connect(true, $logOutput);
                $data = $this->callAction($api, $args, $type, $format, $body, $logOutput);
            } else {
                throw new LengowException($e->getMessage(), $e->getCode());
            }
        }
        return $data;
    }

    /**
     * Call API action
     *
     * @param string $api Lengow method API call
     * @param array $args Lengow method API parameters
     * @param string $type type of request GET|POST|PUT|PATCH
     * @param string $format return format of API
     * @param string $body body data for request
     * @param boolean $logOutput see log or not
     *
     * @throws LengowException
     *
     * @return mixed
     */
    private function callAction($api, $args, $type, $format, $body, $logOutput)
    {
        $result = $this->makeRequest($type, $api, $args, $this->token, $body, $logOutput);
        return $this->format($result, $format);
    }

    /**
     * Get authorization token from Middleware
     *
     * @param boolean $logOutput see log or not
     *
     * @throws LengowException
     *
     * @return string
     */
    private function getAuthorizationToken($logOutput)
    {
        $data = $this->callAction(
            self::API_ACCESS_TOKEN,
            array(
                'access_token' => $this->accessToken,
                'secret' => $this->secret,
            ),
            self::POST,
            self::FORMAT_JSON,
            '',
            $logOutput
        );
        // return a specific error for get_token
        if (!isset($data['token'])) {
            throw new LengowException(LengowMain::setLogMessage('log/connector/token_not_return'), self::CODE_500);
        } elseif (strlen($data['token']) === 0) {
            throw new LengowException(LengowMain::setLogMessage('log/connector/token_is_empty'), self::CODE_500);
        }
        return $data['token'];
    }

    /**
     * Make Curl request
     *
     * @param string $type Lengow method API call
     * @param string $api Lengow API url
     * @param array $args Lengow method API parameters
     * @param string $token temporary access token
     * @param string $body body data for request
     * @param boolean $logOutput see log or no
     *
     * @throws LengowException
     *
     * @return mixed
     */
    protected function makeRequest($type, $api, $args, $token, $body, $logOutput)
    {
        // define CURLE_OPERATION_TIMEDOUT for old php versions
        defined('CURLE_OPERATION_TIMEDOUT') || define('CURLE_OPERATION_TIMEDOUT', CURLE_OPERATION_TIMEOUTED);
        $ch = curl_init();
        // define generic Curl options
        $opts = $this->curlOpts;
        // get special timeout for specific Lengow API
        if (array_key_exists($api, $this->lengowUrls)) {
            $opts[CURLOPT_TIMEOUT] = $this->lengowUrls[$api];
        }
        // get url for a specific environment
        $url = self::LENGOW_API_URL . $api;
        $opts[CURLOPT_CUSTOMREQUEST] = strtoupper($type);
        $url = parse_url($url);
        if (isset($url['port'])) {
            $opts[CURLOPT_PORT] = $url['port'];
        }
        $opts[CURLOPT_HEADER] = false;
        $opts[CURLOPT_VERBOSE] = false;
        if (isset($token)) {
            $opts[CURLOPT_HTTPHEADER] = array('Authorization: ' . $token);
        }
        $url = $url['scheme'] . '://' . $url['host'] . $url['path'];
        switch ($type) {
            case self::GET:
                $opts[CURLOPT_URL] = $url . (!empty($args) ? '?' . http_build_query($args) : '');
                break;
            case self::PUT:
                if (isset($token)) {
                    $opts[CURLOPT_HTTPHEADER] = array_merge(
                        $opts[CURLOPT_HTTPHEADER],
                        array(
                            'Content-Type: application/json',
                            'Content-Length: ' . strlen($body),
                        )
                    );
                }
                $opts[CURLOPT_URL] = $url . '?' . http_build_query($args);
                $opts[CURLOPT_POSTFIELDS] = $body;
                break;
            case self::PATCH:
                if (isset($token)) {
                    $opts[CURLOPT_HTTPHEADER] = array_merge(
                        $opts[CURLOPT_HTTPHEADER],
                        array('Content-Type: application/json')
                    );
                }
                $opts[CURLOPT_URL] = $url;
                $opts[CURLOPT_POST] = count($args);
                $opts[CURLOPT_POSTFIELDS] = json_encode($args);
                break;
            default:
                $opts[CURLOPT_URL] = $url;
                $opts[CURLOPT_POST] = count($args);
                $opts[CURLOPT_POSTFIELDS] = http_build_query($args);
                break;
        }
        LengowMain::log(
            LengowLog::CODE_CONNECTOR,
            LengowMain::setLogMessage(
                'log/connector/call_api',
                array(
                    'call_type' => $type,
                    'curl_url' => $opts[CURLOPT_URL],
                )
            ),
            $logOutput
        );
        curl_setopt_array($ch, $opts);
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        $curlErrorNumber = curl_errno($ch);
        curl_close($ch);
        $this->checkReturnRequest($result, $httpCode, $curlError, $curlErrorNumber);
        return $result;
    }

    /**
     * Check return request and generate exception if needed
     *
     * @param string $result Curl return call
     * @param integer $httpCode request http code
     * @param string $curlError Curl error
     * @param string $curlErrorNumber Curl error number
     *
     * @throws LengowException
     *
     */
    private function checkReturnRequest($result, $httpCode, $curlError, $curlErrorNumber)
    {
        if ($result === false) {
            // recovery of Curl errors
            if (in_array($curlErrorNumber, array(CURLE_OPERATION_TIMEDOUT, CURLE_OPERATION_TIMEOUTED))) {
                throw new LengowException(LengowMain::setLogMessage('log/connector/timeout_api'), self::CODE_504);
            } else {
                $error = LengowMain::setLogMessage(
                    'log/connector/error_curl',
                    array(
                        'error_code' => $curlErrorNumber,
                        'error_message' => $curlError,
                    )
                );
                throw new LengowException($error, self::CODE_500);
            }
        } else {
            if (!in_array($httpCode, $this->successCodes)) {
                $result = $this->format($result);
                // recovery of Lengow Api errors
                if (isset($result['error'])) {
                    throw new LengowException($result['error']['message'], $httpCode);
                } else {
                    throw new LengowException(LengowMain::setLogMessage('log/connector/api_not_available'), $httpCode);
                }
            }
        }
    }

    /**
     * Get data in specific format
     *
     * @param mixed $data Curl response data
     * @param string $format return format of API
     *
     * @return mixed
     */
    private function format($data, $format = self::FORMAT_JSON)
    {
        switch ($format) {
            case self::FORMAT_STREAM:
                return $data;
            default:
            case self::FORMAT_JSON:
                return json_decode($data, true);
        }
    }
}
