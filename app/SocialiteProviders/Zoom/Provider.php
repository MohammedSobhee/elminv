<?php

namespace App\SocialiteProviders\Zoom;

use SocialiteProviders\Manager\OAuth2\AbstractProvider;
use SocialiteProviders\Manager\OAuth2\User;

class Provider extends AbstractProvider {
    /**
     * Unique Provider Identifier.
     */
    const IDENTIFIER = 'ZOOM';

    protected $scopes = [
        'meeting'
    ];

    /**
     * {@inheritdoc}
     */
    protected function getAuthUrl($state) {
        return $this->buildAuthUrlFromBase(
            'https://zoom.us/oauth/authorize', $state
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl() {
        return 'https://zoom.us/oauth/token';
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token) {
        $response = $this->getHttpClient()->get(
            'https://api.zoom.us/v2/users/me', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token
                ]
            ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user) {
        return (new User())->setRaw($user)->map([
            'id' => $user['id'],
            'nickname' => null,
            'name' => $user['first_name'] . " " . $user['last_name'],
            'email' => $user['email'],
            'avatar' => $user['pic_url'],
            'account_id' => $user['account_id'],
            'personal_meeting_url' => $user['personal_meeting_url']

        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenFields($code) {
        return array_merge(parent::getTokenFields($code), [
            'grant_type' => 'authorization_code'
        ]);
    }
}
