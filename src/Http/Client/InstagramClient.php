<?php
/**
 * cakephp-instagram (https://github.com/smartsolutionsitaly/cakephp-instagram)
 * Copyright (c) 2019 Smart Solutions S.r.l. (https://smartsolutions.it)
 *
 * Instagram client for CakePHP
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @category  cakephp-plugin
 * @package   cakephp-instagram
 * @author    Lucio Benini <dev@smartsolutions.it>
 * @copyright 2019 Smart Solutions S.r.l. (https://smartsolutions.it)
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 * @link      https://smartsolutions.it Smart Solutions
 * @since     1.0.0
 */

namespace SmartSolutionsItaly\CakePHP\Instagram\Http\Client;

use Cake\Core\Configure;
use Cake\Http\Client;

/**
 * Instagram client.
 * @package SmartSolutionsItaly\CakePHP\Instagram\Http\Client
 * @author Lucio Benini <dev@smartsolutions.it>
 * @since 1.0.0
 */
class InstagramClient
{
    /**
     * HTTP client instance.
     * @var \Cake\Http\Client
     */
    protected $_client;

    /**
     * Constructor.
     * Sets the base client and Instagram's API URL.
     */
    public function __construct()
    {
        $this->_client = new Client([
            'host' => 'api.instagram.com',
            'scheme' => 'https'
        ]);
    }

    /**
     * Returns the login URL.
     * @param string $url The redirect URL.
     * @return string Returns the Instagram login URL.
     */
    public static function getLoginUrl(string $url): string
    {
        return sprintf('https://api.instagram.com/oauth/authorize/?client_id=%1s&redirect_uri=%2s&response_type=code', Configure::read('Socials.instagram.appid'), $url);
    }

    /**
     * Authenticate the user to Instagram and returns a token.
     * @param string $code Authorization code from Instagram.
     * @param string $url The redirect URL.
     * @return string|null The access token or null.
     */
    public function authorize(string $code, string $url)
    {
        try {
            $res = $this->_client->post('/oauth/access_token', [
                'client_id' => Configure::read('Socials.instagram.appid'),
                'client_secret' => Configure::read('Socials.instagram.appsecret'),
                'grant_type' => 'authorization_code',
                'redirect_uri' => $url,
                'code' => $code
            ]);

            $json = json_decode($res->getBody()->getContents());

            return !empty($json->access_token) ? $json->access_token : null;
        } catch (\Exception $ex) {
            return null;
        }
    }

    /**
     * Gets media objects of the user from Instagram.
     * @param string $token The access token.
     * @param int $count The max number of retrieved media.
     * @return array An array of media objects.
     */
    public function getImages(string $token, int $count = 6): array
    {
        $res = [];
        $medias = $this->getMedia($token, $count);

        if ($medias) {
            foreach ($medias as $media) {
                if (!empty($media['images']['standard_resolution']['url'])) {
                    $res[] = [
                        'high' => $media['images']['standard_resolution']['url'],
                        'low' => $media['images']['low_resolution']['url']
                    ];
                }
            }
        }

        return $res;
    }

    /**
     * Gets media entries of the user from Instagram.
     * @param string $token The access token.
     * @param int $count The max number of retrieved media.
     * @return array An array of medias or the error message.
     */
    public function getMedia(string $token, int $count = 6): array
    {
        try {
            $res = $this->_client->get('/v1/users/self/media/recent', [
                'access_token' => $token,
                'count' => abs($count)
            ]);

            $json = json_decode($res->getBody()->getContents(), true);

            return !empty($json['data']) ? $json['data'] : [];
        } catch (\Exception $ex) {
            return [];
        }
    }
}
