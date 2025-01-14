<?php

namespace Mokhosh\LaravelYoutubeApi\Auth;

/**
 *  Api Service For Auth
 */
class AuthService
{
    protected $client;
    protected $ytLanguage;

    public function __construct()
    {
        $this->client = new \Google_Client;

        $this->client->setClientId(\Config::get('youtube-api.client_id'));
        $this->client->setClientSecret(\Config::get('youtube-api.client_secret'));
        $this->client->setDeveloperKey(\Config::get('youtube-api.api_key'));
        $this->client->setRedirectUri(\Config::get('youtube-api.redirect_url'));

        $this->client->setScopes([
            'https://www.googleapis.com/auth/youtube',
            'https://www.googleapis.com/auth/youtubepartner',
            'https://www.googleapis.com/auth/youtube.force-ssl',
        ]);

        $this->client->setAccessType('offline');
        $this->client->setPrompt('consent');
        $this->ytLanguage = \Config::get('google.yt_language');
    }

    /**
     * [getToken -generate token from response code recived on visiting the login url generated]
     * @param  [type] $code [code for auth]
     * @return [type]       [authorization token]
     */
    public function getToken($code)
    {
        $this->client->authenticate($code);
        $token = $this->client->getAccessToken();

        return $token;
    }

    /**
     * [getLoginUrl - generates the url login url to generate auth token]
     * @param  [type] $youtube_email [account to be authenticated]
     * @param  [type] $channelId     [return identifier]
     * @return [type]                [auth url to generate]
     */
    public function getLoginUrl($youtube_email, $channelId = null)
    {
        if (!empty($channelId)) {
            $this->client->setState($channelId);
        }

        $this->client->setLoginHint($youtube_email);
        $authUrl = $this->client->createAuthUrl();

        return $authUrl;
    }

    /**
     * [setAccessToken -setting the access token to the client]
     * @param [type] $google_token [googel auth token]
     */
    public function setAccessToken($google_token = null)
    {
        if (!is_null($google_token)) {
            $this->client->setAccessToken($google_token);
        }

        if (!is_null($google_token) && $this->client->isAccessTokenExpired()) {
            $refreshed_token = $this->client->getRefreshToken();
            $this->client->fetchAccessTokenWithRefreshToken($refreshed_token);
            $newToken = $this->client->getAccessToken();
            $newToken = json_encode($newToken);
        }

        return !$this->client->isAccessTokenExpired();
    }

    /**
     * [createResource creating a resource array and addind properties to it]
     * @param  $properties [param properties to be added to channel]
     * @return             [resource array]
     */
    public function createResource($properties)
    {
        $resource = array();
        foreach ($properties as $prop => $value) {

            if ($value) {
                /**
                 * add property to resource
                 */
                $this->addPropertyToResource($resource, $prop, $value);
            }
        }

        return $resource;
    }

    /**
     * [addPropertyToResource description]
     * @param &$ref     [using reference of array from createResource to add property to it]
     * @param $property [property to be inserted to resource array]
     */
    public function addPropertyToResource(&$ref, $property, $value)
    {
        $keys = explode(".", $property);
        $isArray = false;
        foreach ($keys as $key) {

            /**
             * snippet.tags[]  [convert to snippet.tags]
             * a boolean variable  [to handle the value like an array]
             */
            if (substr($key, -2) == "[]") {
                $key = substr($key, 0, -2);
                $isArray = true;
            }

            $ref = &$ref[$key];
        }

        /**
         * Set the property value [ handling the array values]
         */
        if ($isArray && $value) {

            $ref = $value;
            $ref = explode(",", $value);
        } elseif ($isArray) {

            $ref = array();
        } else {

            $ref = $value;
        }
    }

    /**
     * [parseTime - parse the video time in to description format]
     * @param  $time [youtube returned time format]
     * @return       [string parsed time]
     */
    public function parseTime($time)
    {
        $tempTime = str_replace("PT", " ", $time);
        $tempTime = str_replace('H', " Hours ", $tempTime);
        $tempTime = str_replace('M', " Minutes ", $tempTime);
        $tempTime = str_replace('S', " Seconds ", $tempTime);

        return $tempTime;
    }
}
