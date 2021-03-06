<?php
namespace EActivities;

use \DateTime;
use \Guzzle\Http\Client as Http_Client;
use \Guzzle\Http\Message\Response;
use \Guzzle\Plugin\Cookie\Cookie;
use \Guzzle\Plugin\Cookie\CookieJar\ArrayCookieJar;
use \Guzzle\Plugin\Cookie\CookieJar\CookieJarInterface;
use \Guzzle\Plugin\Cookie\CookiePlugin;
use \ImperialCollegeCredential;
use \Str;

class Client {

    const URL_BASE = 'https://eactivities.union.ic.ac.uk/';

    const PATH_COMMON_AJAX_HANDLER = '/common/ajax_handler.php';
    const PATH_ADMIN_CSP_DETAILS   = '/admin/csp/details/603';
    const PATH_FINANCE_INCOME_SHOP = '/finance/income/shop/603';
    const PATH_MEMBERS_REPORT      = '/admin/csp/details/csv';
    const PATH_PURCHASE_REPORT     = '/finance/income/shop/group/csv/%d';

    const NAME_SESSION_COOKIE = 'ICU_eActivities';

    protected $client;

    protected $cookie_jar;

    /**
     * @param Http_Client $client
     */
    public function __construct(Http_Client $client)
    {
        $client->setBaseUrl(self::URL_BASE);

        $client->setDefaultOption('exceptions', false);

        $this->cookie_jar = new ArrayCookieJar();
        $cookiePlugin = new CookiePlugin($this->cookie_jar);
        $client->addSubscriber($cookiePlugin);

        $this->client = $client;
    }

    /**
     * Get session Id
     *
     * @return string
     */
    public function getSessionId()
    {
        foreach ($this->cookie_jar->all() as $cookie) {
            if ($cookie->getName() == self::NAME_SESSION_COOKIE) {
                return $cookie->getValue();
            }
        }

        return null;
    }

    /**
     * Set session Id
     *
     * @param string $session_id
     */
    public function setSessionId($session_id)
    {
        $cookie = new Cookie(array(
            'name' => self::NAME_SESSION_COOKIE,
            'value' => $session_id,
            'domain' => 'eactivities.union.imperial.ac.uk'));
        $this->cookie_jar->add($cookie);
    }

    /**
     * Sign in a given credential
     *
     * @param  ImperialCollegeCredential $credential
     * @return boolean
     */
    public function signIn(ImperialCollegeCredential $credential)
    {
        $response = $this->getAjaxHandlerResponse(array(
            'ajax' => 'login',
            'name' => $credential->getUsername(),
            'pass' => $credential->getPassword(),
            'objid' => '1'
        ));

        return $this->isSignedIn();
    }

    /**
     * Check if user is signed in.
     * Will check the response of the root page if no response is given.
     *
     * @param  Response  $response
     * @return boolean
     */
    public function isSignedIn(Response $response = null)
    {
        if ( ! isset($response)) {
            $response = $this->getPageResponse('/');
        }

        return ($response->isSuccessful() && strpos($response->getBody(), 'Log out') !== false);
    }

    /**
     * Get user's currently selected and other roles
     *
     * @return array
     */
    public function getCurrentAndOtherRoles()
    {
        $response = $this->getAjaxHandlerResponse(array(
            'ajax' => 'setupinlineinfo',
            'navigate' => '1'
        ));
        $body = $response->getBody();

        $result = array(
            'current' => null,
            'others' => []
        );

        preg_match('/<p class="currentrole">([^<]+)<\/p>/', $body, $output_array);
        if (isset($output_array[1])) {
            $result['current'] = $output_array[1];
        }

        preg_match_all('/<span class="changerole" onclick="changeRole\(this, \'(\d+)\'\)">([^<]+)<\/span>/', $body, $output_array);
        foreach ($output_array[1] as $key => $role_key) {
            $result['others'][$role_key] = $output_array[2][$key];
        }

        return $result;
    }

    /**
     * Download and parse members report file
     *
     * @return array
     */
    public function getMembersList()
    {
        $response = $this->getPageResponse(self::PATH_ADMIN_CSP_DETAILS);
        if (!$this->isSignedIn($response)) {
            throw new EActivitiesClientException("Not logged in!");
        }

        $response = $this->activateTabs('395');
        $request = $this->client->post(self::PATH_MEMBERS_REPORT);
        $response = $request->send();
        $body = $response->getBody();

        return $this->parseCsv($body);
    }

    public function getPurchasesList($product_id)
    {
        $response = $this->getPageResponse(self::PATH_FINANCE_INCOME_SHOP);
        if ( ! $this->isSignedIn($response)) {
            return []; // @todo raise exception?
        }

        // 1725: Purchases Summary
        $response = $this->activateTabs(['1725']);
        if ( ! $response->isSuccessful()) {
            return []; // @todo raise exception?
        }

        $request = $this->client->get(sprintf(self::PATH_PURCHASE_REPORT, $product_id));
        $response = $request->send();
        $body = $response->getBody();
        $result = $this->parseCsv($body);
        $result = array_map(function($product) use ($product_id) {
            $product['product_id'] = $product_id;
            $product['date'] = DateTime::createFromFormat('d/h/Y', $product['date']);
            return $product;
        }, $result);

        return $result;
    }

    /**
     * Change user's role
     *
     * @param  integer|string $role_id
     * @return Response
     */
    public function changeRole($role_id)
    {
        return $this->getAjaxHandlerResponse(array(
            'ajax' => 'changerole',
            'navigate' => '1',
            'id' => $role_id,
        ));
    }

    /**
     * Activate tabs
     *
     * @param  integer|string $navigate
     * @return Response
     */
    protected function activateTabs($navigate)
    {
        $navigate = (array) $navigate;
        $last_response = null;

        while (($current_navigate = array_shift($navigate))) {
            $last_response = $this->getAjaxHandlerResponse(array(
                'ajax' => 'activatetabs',
                'navigate' => $current_navigate,
            ));

            if ( ! $last_response->isSuccessful()) {
                break;
            }
        }

        return $last_response;
    }

    /**
     * Send a GET request
     *
     * @param  integer $path
     * @return Response
     */
    protected function getPageResponse($path = null)
    {
        $request = $this->client->get($path);
        return $request->send();
    }

    /**
     * Send a POST request to the ajax handler
     *
     * @param  array $params
     * @return Response
     */
    protected function getAjaxHandlerResponse($params)
    {
        $request = $this->client->post(self::PATH_COMMON_AJAX_HANDLER, [], $params);
        return $request->send();
    }

    /**
     * Parse CSV body. Normalizes column names.
     * @param  string $body
     * @return array
     */
    protected function parseCsv($body)
    {
        $result = explode("\n", trim($body));
        $groups = ['Full Members', 'Life / Associate'];

        // Get header
        $headers = str_getcsv(array_shift($result));

        if (in_array($headers[0], $groups)) { // This is a section header
            /* Add the first column header to the list of things that will
             * cause a row to be ignored */
            $headers = str_getcsv(array_shift($result));
            $groups[] = $headers[0];
        }

        $headers = array_map(function($original) {
            if ($original === 'CID') {
                return 'cid';
            } else {
                return Str::snake(Str::camel($original));
            }
        }, $headers);

        // Format rows
        $output = array_map(function($original) use ($headers, $groups) {
            $new_row = [];

            $row = str_getcsv($original);
            $row_size = count($row);

            foreach ($headers as $key => $header) {
                if ($row_size <= $key || in_array($row[$key], $groups)) {
                    return [];
                }

                $new_row[$header] = $row[$key];
            }

            return $new_row;
        }, $result);

        // Remove blank rows
        return array_filter($output);
    }
}

class EActivitiesClientException extends \Exception
{
}
