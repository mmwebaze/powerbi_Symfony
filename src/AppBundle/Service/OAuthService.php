<?php

namespace AppBundle\Service;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use kamermans\OAuth2\GrantType\ClientCredentials;
use kamermans\OAuth2\OAuth2Middleware;

class OAuthService
{
    protected $baseUrl;
    protected $grantType;
    protected $clientId;
    protected $clientSecret;
    protected $tokenEndpoint;

    public function __construct(array $params)
    {
        $this->baseUrl = $params['url'];
        $this->grantType = $params['grant_type'];
        $this->clientId = $params['client_id'];
        $this->clientSecret = $params['client_secret'];
        $this->tokenEndpoint = $params['token_endpoint'];
    }

    private function getAccessToken(){
        try{
            $reauth_config = [
                "client_id" => $this->clientId,
                "client_secret" => $this->clientSecret,
                'grant_type' => $this->grantType,
                'scope' => 'datasets'
            ];
            $reauth_client = new Client([
                // URL for access_token request
                'base_uri' => $this->tokenEndpoint,
            ]);

            $grant_type = new ClientCredentials($reauth_client, $reauth_config);
            $oauth = new OAuth2Middleware($grant_type);
            return $oauth->getAccessToken();

            //$response = $client->post($baseUrl.'/api/1807/eggs-collect');

            //echo "Status: ".$response->getStatusCode()."\n";
            // die();
           /*

            echo "\n\n";*/
        }
        catch (ClientException $e){
            echo $e->getMessage().' *********';
        }
    }
    public function getResouce($resourceEndpoint){
        try{
            $http = new Client();
            $request = $http->request('POST', $resourceEndpoint,
                [
                    'headers' => [
                        'Authorization' => 'Bearer '.$this->getAccessToken()
                    ]
                ]);

            $response = $request->getBody();
            return $response;
        }
        catch(ClientException $e){
            echo $e->getMessage().' *********';
        }
    }
}