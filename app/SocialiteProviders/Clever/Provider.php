<?php

namespace App\SocialiteProviders\Clever;

use SocialiteProviders\Manager\OAuth2\AbstractProvider;
use SocialiteProviders\Manager\OAuth2\User;

class Provider extends AbstractProvider {
    /**
     * Unique Provider Identifier.
     */
    const IDENTIFIER = 'CLEVER';

    /**
     * {@inheritdoc}
     */
    protected function getAuthUrl($state) {
        return $this->buildAuthUrlFromBase(
            'https://clever.com/oauth/authorize',
            $state
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl() {
        return 'https://clever.com/oauth/tokens';
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token) {
        $response = $this->getHttpClient()->get(
            'https://api.clever.com/me',
            [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $token
                ]
            ]
        );

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user) {
        return (new User())->setRaw($user)->map([
            'id' => $user['data']['id'],
            'email' => isset($user['data']['email']) ? $user['data']['email'] : '',
            'first_name' => isset($user['data']['name']['first']) ? $user['data']['name']['first'] : '',
            'last_name' => isset($user['data']['name']['last']) ? $user['data']['name']['last'] : ''
        ]);
    }

    public function getAccessTokenResponse($code) {
        $response = $this->getHttpClient()->post($this->getTokenUrl(), [
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode($this->clientId . ':' . $this->clientSecret)
            ],
            'form_params' => $this->getTokenFields($code)
        ]);
        $this->credentialsResponseBody = json_decode($response->getBody(), true);

        return $this->credentialsResponseBody;
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenFields($code) {
        return array_merge(parent::getTokenFields($code), [
            'code' => $code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $this->redirectUrl
        ]);
    }
}
