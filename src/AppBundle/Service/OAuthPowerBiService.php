<?php


namespace AppBundle\Service;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use kamermans\OAuth2\GrantType\ClientCredentials;
use kamermans\OAuth2\OAuth2Middleware;


class OAuthPowerBiService
{
    protected $redirectUri;
    protected $responseType;
    protected $clientId;
    protected $clientSecret;
    protected $grantType;
    protected $code;

    public function __construct(array $params)
    {
        $this->redirectUri = $params['redirect_uri'];
        $this->clientId = $params['client_id'];
        $this->clientSecret = $params['client_secret'];
        $this->grantType = $params['grant_type'];
        $this->code = $params['code'];
    }

    public function getAccessToken(){
        try{
            $reauth_config = [
                "client_id" => $this->clientId,
                "client_secret" => $this->clientSecret,
                'grant_type' => $this->grantType,
                'redirect_uri' => $this->redirectUri,
                'code' => $this->code,
            ];
            $reauth_client = new Client([
                // URL for access_token request
                'base_uri' => 'https://login.windows.net/bece100a-d156-46eb-b19f-999db030121f/oauth2/token',
            ]);

            $grant_type = new ClientCredentials($reauth_client, $reauth_config);
            $oauth = new OAuth2Middleware($grant_type);
            return $oauth->getAccessToken();
        }
        catch (ClientException $e){
            echo $e->getMessage().' ********* PowerBI';
        }
    }
}