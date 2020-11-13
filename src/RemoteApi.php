<?php

use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;

class RemoteApi
{
    protected static $Client;
    protected static $debug;
    public static $messages;
    public $host = "ispconfig-host";
    protected $username = "api-username";
    protected $password = "api-password";
    protected $session_key;

    public $dir;
    public $client_id;
    public $web_id;

    public function __construct()
    {
        $this->debug = true;
        $this->client_options = [
            'base_uri'  => "https://" . $this->host . ":8080/remote/json.php",
            'timeout'   => 2.0,
            'verify'    => false,
            'timeout'   => 30
        ];

        if ($this->auth()) {
            return false;
        }
    }

    public function setClientId($id)
    {
        $this->client_id = $id;
    }

    private function auth()
    {
        $Client = new Client($this->client_options);
        $data = [
            'json' => [
                'username' => $this->username,
                'password' => $this->password
            ]
        ];

        try {
            $response = $Client->request('POST', '?login',  $data);

            $body = $response->getBody()->__toString();
            $parsed = json_decode((string) $body, true);

            $this->session_key = $parsed['response'];
            return false;
        } catch (BadResponseException $exception) {
            $responseBody = $exception->getResponse()->getBody(true)->__toString();
            $this->messages = $responseBody;
            return true;
        }
    }


    public function checkTask($info)
    {
        $Client = new Client($this->client_options);
        $data = [
            'json' => [
                'session_id' => $this->session_key,
                'username' => $info['username'],
            ]
        ];

        try {
            $response = $Client->request('POST', '?monitor_jobqueue',  $data);

            $body = $response->getBody()->__toString();
            $parsed = json_decode((string) $body, true);

            $this->code = $parsed['code'];
            $this->messages[] = $parsed['message'];
            return $parsed['response']['count'];
        } catch (BadResponseException $exception) {
            $responseBody = $exception->getResponse()->getBody(true)->__toString();
            $this->messages = $responseBody;
            return true;
        }
    }

    public function checkWeb($WebId)
    {
        $Client = new Client($this->client_options);
        $data = [
            'json' => [
                'session_id' => $this->session_key,
                'primary_id' => $WebId,
            ]
        ];

        try {
            $response = $Client->request('POST', '?sites_web_domain_get',  $data);

            $body = $response->getBody()->__toString();
            $parsed = json_decode((string) $body, true);

            $this->code = $parsed['code'];
            $this->messages[] = $parsed['message'];
            $this->document_root = $parsed['response']['document_root'];
            $this->domain = $parsed['response']['domain'];

            $this->puser = $parsed['response']['system_user'];
            $this->pgroup = $parsed['response']['system_group'];

            return is_array($parsed['response']);
        } catch (BadResponseException $exception) {
            $responseBody = $exception->getResponse()->getBody(true)->__toString();
            $this->messages = $responseBody;
            return true;
        }
    }

    function createClient($data)
    {
        $Client = new Client($this->client_options);
        $data = [
            'json' => [
                'session_id' => $this->session_key,
                'params' =>
                [
                    'server_id' => '1',
                    'reseller_id' => '1',
                    'parent_client_id' => '1',
                    'company_name' => $data['cie'],
                    'contact_name' => $data['name'],
                    'username' => $data['username'],
                    'password' => \createRandomKey(16),
                    'email' => $data['email'],
                    'telephone' => $data['phone'],
                    'country' => $data['country'],
                    'gender' => '',
                    'customer_no' => '',
                    'customer_no_org' => '',
                    'language' => 'en',
                    'usertheme' => 'default',
                    'street' => '',
                    'zip' => '',
                    'city' => '',
                    'state' => '',
                    'mobile' => '',
                    'fax' => '',
                    'internet' => 'http://',
                    'web_php_options' => 'php-fpm',
                    'ssh_chroot' => 'jailkit,ssh-chroot',
                    'limit_dns_zone' => '0',
                    'limit_web_quota' => $data['client_disk_quota'],
                    'limit_web_subdomain' => '1',
                    'limit_web_domain' => '1',
                    'default_dnsserver' => '1',
                    'force_suexec' => '1',
                    'limit_database' => '1',
                    'limit_traffic_quota' => '500',
                    'limit_client' => '0',
                    'limit_cron_type' => '1',
                    'limit_ssl' => '1',
                    'limit_shell_user' => '1',
                    'limit_database_user' => '1',
                    'limit_database_quota' => '5',
                    'canceled' => '1',
                ],
            ]
        ];

        try {
            $response = $Client->request('POST', '?client_add',  $data);

            $body = $response->getBody()->__toString();
            $parsed = json_decode((string) $body, true);

            $this->code = $parsed['code'];
            $this->messages[] = $parsed['message'];
            $this->client_id = $parsed['response'];
            return $parsed['response'];
        } catch (BadResponseException $exception) {
            $responseBody = $exception->getResponse()->getBody(true)->__toString();
            $this->messages = $responseBody;
            return true;
        }
    }


    function createWeb($data)
    {
        $Client = new Client($this->client_options);
        $data = [
            'json' => [
                'session_id' => $this->session_key,
                'client_id' => $this->client_id,
                'params' =>
                [
                    'username' => $data['username'],
                    'server_id' => '1',
                    'ip_address' => '*',
                    'ipv6_address' => '',
                    'domain' => $data['name'] . '.apigoat.com',
                    'hd_quota' => $data['web_disk_quota'],
                    'traffic_quota' => '500',
                    'suexec' => 'y',
                    'errordocs' => '1',
                    'subdomain' => 'none',
                    'vhost_type' => 'name',
                    'type' => 'vhost',
                    'php' => 'php-fpm',
                    'directive_snippets_id' => '0',
                    'active' => 'y',
                    'allow_override' => 'All',
                    'rewrite_to_https' => 'y',
                    'http_port' => '80',
                    'https_port' => '443',
                    'pm' => 'ondemand',
                    'pm_start_servers' => '1',
                    'pm_max_children' => '1',
                    'pm_max_spare_servers' => '1',
                    'pm_min_spare_servers' => '1',
                    'pm_process_idle_timeout' => '10',
                    'pm_max_requests' => '500',
                    'php_fpm_chroot' => 'y',

                    'ssl' => 'y',
                    'ssl_letencrypt' => 'y',
                    'ssl_country' => 'y',
                    'stats_type' => 'none',
                    'backup_interval' => 'none',
                    'added_by' => 'apigoat',
                    'added_date' => date("Y-m-d"),
                ],
            ]
        ];

        try {
            $response = $Client->request('POST', '?sites_web_domain_add',  $data);

            $body = $response->getBody()->__toString();
            $parsed = json_decode((string) $body, true);

            $this->code = $parsed['code'];
            $this->messages[] = $parsed['message'];
            $this->web_id = $parsed['response'];
            return false;
        } catch (BadResponseException $exception) {
            $responseBody = $exception->getResponse()->getBody(true)->__toString();
            $this->messages = $responseBody;
            return true;
        }
    }

    function createDatabaseUser($data)
    {
        if ($this->web_id || $data['web_id']) {
            if ($data['web_id']) {
                $this->web_id = $data['web_id'];
            }
            $web = $this->checkWeb($this->web_id);

            $Client = new Client($this->client_options);
            $data = [
                'json' => [
                    'session_id' => $this->session_key,
                    'params' =>
                    [
                        'server_id' => '1',
                        'parent_domain_id' => $this->web_id,
                        'username' => $data['ssh_username'],
                        'password' => $data['ssh_password'],
                        'quota_size' => $data['quota_size'],
                        'active' => 'y',
                        'chroot' => 'jailkit',
                        'puser' => $this->puser,
                        'pgroup' => $this->pgroup,
                        'shell' => '/bin/bash',
                        'dir' => '/var/www/clients/' . $this->pgroup . '/' . $this->puser . '/web/',
                    ],
                ]
            ];

            try {
                $response = $Client->request('POST', '?sites_shell_user_add',  $data);

                $body = $response->getBody()->__toString();
                $parsed = json_decode((string) $body, true);

                $this->code = $parsed['code'];
                $this->messages[] = $parsed['message'];
                return $parsed['response'];
            } catch (BadResponseException $exception) {
                $responseBody = $exception->getResponse()->getBody(true)->__toString();
                $this->messages = $responseBody;
                return true;
            }
        } else {
            $this->messages[] = "No valid webid when creating shell account.";
            return true;
        }
    }

    function createSsh($data)
    {
        if ($this->web_id || $data['web_id']) {
            if ($data['web_id']) {
                $this->web_id = $data['web_id'];
            }
            $web = $this->checkWeb($this->web_id);

            $Client = new Client($this->client_options);
            $data = [
                'json' => [
                    'session_id' => $this->session_key,
                    'params' =>
                    [
                        'server_id' => '1',
                        'parent_domain_id' => $this->web_id,
                        'username' => $data['ssh_username'],
                        'password' => $data['ssh_password'],
                        'quota_size' => $data['quota_size'],
                        'active' => 'y',
                        'chroot' => 'jailkit',
                        'puser' => $this->puser,
                        'pgroup' => $this->pgroup,
                        'shell' => '/bin/bash',
                        'dir' => '/var/www/clients/' . $this->pgroup . '/' . $this->puser . '/web/',
                    ],
                ]
            ];

            try {
                $response = $Client->request('POST', '?sites_shell_user_add',  $data);

                $body = $response->getBody()->__toString();
                $parsed = json_decode((string) $body, true);

                $this->code = $parsed['code'];
                $this->messages[] = $parsed['message'];
                return $parsed['response'];
            } catch (BadResponseException $exception) {
                $responseBody = $exception->getResponse()->getBody(true)->__toString();
                $this->messages = $responseBody;
                return true;
            }
        } else {
            $this->messages[] = "No valid webid when creating shell account.";
            return true;
        }
    }
}
