<?php

namespace Mostafaznv\Recaptcha;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;

class Recaptcha
{
    const VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';
    const CLIENT_API = 'https://www.google.com/recaptcha/api.js';

    /**
     * Recaptcha Secret Key.
     *
     * @var string
     */
    protected $secretKey;

    /**
     * Recaptcha Site Key.
     *
     * @var string
     */
    protected $siteKey;

    /**
     * Guzzle Http Client.
     *
     * @var Client
     */
    protected $http;

    /**
     * Http Request Instance.
     *
     * @var Request
     */
    protected $request;

    /**
     * Recaptcha constructor.
     *
     */
    public function __construct()
    {
        $config = config('recaptcha');

        $this->secretKey = $config['secret_key'];
        $this->siteKey = $config['site_key'];

        $this->http = new Client($config['options']);
        $this->request = app('request');
    }

    /**
     * Site Key Getter.
     *
     * @return string
     */
    public function getSiteKey()
    {
        return $this->siteKey;
    }

    /**
     * Verify Token.
     *
     * If token is valid, returns score
     * Otherwise returns false
     *
     * @param $token
     * @param null $action
     * @param float $score
     * @return bool
     */
    public function verify($token, $action = null, $score = 0.5)
    {
        try {
            $response = $this->http->request('POST', static::VERIFY_URL, [
                'form_params' => [
                    'secret'   => $this->secretKey,
                    'response' => $token,
                    'remoteip' => $this->request->getClientIp(),
                ],
            ]);

            $body = json_decode($response->getBody());

            if (!isset($body->success) or $body->success !== true) {
                return false;
            }

            if ($action and (!isset($body->action) or $action != $body->action)) {
                return false;
            }

            return (isset($body->score) and $body->score >= $score) ? $body->score : false;
        }
        catch (GuzzleException $e) {
            logger()->error($e->getMessage());

            return false;
        }
    }

    /**
     * Render JS Script.
     *
     * @param null $lang
     * @return string
     */
    public function renderJs($lang = null)
    {
        $link = $this->getJsLink($lang);

        return "<script src='$link'></script>\n";
    }

    /**
     * Render HTML Input Field.
     *
     * @param $action
     * @param string $name
     * @param array $attributes
     * @param null $callback
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function field($action, $name = 'g-recaptcha-response', array $attributes = [], $callback = null)
    {
        if (!isset($attributes['id'])) {
            $attributes['id'] = uniqid("$name-", false);
        }

        $id = $attributes['id'];
        $attributes = $this->buildAttributes($attributes);
        $siteKey = $this->siteKey;


        return view('recaptcha::field', compact('siteKey', 'action', 'name', 'id', 'attributes', 'callback'));
    }

    /**
     * Generate Recaptcha JS Link.
     *
     * @param null $lang
     * @return string
     */
    protected function getJsLink($lang = null)
    {
        $params = [
            'render' => $this->siteKey
        ];

        if ($lang) {
            $params['hl'] = $lang;
        }


        return self::CLIENT_API . '?' . http_build_query($params);
    }

    /**
     * Build HTML Attributes from Array.
     *
     * @param array $attributes
     * @return string
     */
    protected function buildAttributes(array $attributes)
    {
        if (empty($attributes)) {
            return '';
        }

        $attributePairs = [];

        foreach ($attributes as $key => $value) {
            if (is_int($key)) {
                $attributePairs[] = $value;
            }
            else {
                $value = htmlspecialchars($value, ENT_QUOTES);
                $attributePairs[] = "{$key}=\"{$value}\"";
            }
        }

        return implode(' ', $attributePairs);
    }
}