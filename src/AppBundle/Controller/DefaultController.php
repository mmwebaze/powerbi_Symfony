<?php

namespace AppBundle\Controller;

use AppBundle\Service\OAuthPowerBiService;
use AppBundle\Util\ReadFile;
use GuzzleHttp\Client;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Service\OAuthService;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        // replace this example code with whatever you need
        $readFile = new ReadFile();
        $secrets = $readFile->loadJsonFile($this->getParameter('kernel.project_dir').'/secrets.json');
        $csv = $readFile->loadCsvFile($this->getParameter('kernel.project_dir').'/data.csv');
        var_dump($csv);
        return $this->render('default/auth.html.twig', ['variable_name' => $this->getParameter('kernel.project_dir')]);
    }
    /**
     * @Route("/oauth", name="oauth")
     */
    public function testOAthu(Request $request){
        $baseUrl = 'http://coop.apps.knpuniversity.com';
        $parameters = [
            'url' => $baseUrl,
            'grant_type' => 'client_credentials',
            'client_id' => 'Trudy',
            'client_secret' => '88732b568736eef5da08a5a89c388dd0',
            'token_endpoint' => $baseUrl.'/token'
        ];
        $oauthserv = new OAuthService($parameters);
        $myResource = $oauthserv->getResouce($baseUrl.'/api/1807/eggs-collect');
        return $this->render('default/auth.html.twig', ['variable_name' => $myResource]);
    }

    /**
     * @Route("/powerbi", name="powerbi")
     */
    public function powerbi(Request $request)
    {
        $code = $request->get('code');
        $state = $request->get('state');

        if ($code){
            if(!$state || $_SESSION['state'] != $state) {
                header('Location: ' . $_SERVER['PHP_SELF']);
                die();
            }
        }
        //$readFile = new ReadFile();
        $secrets = ReadFile::loadJsonFile($this->getParameter('kernel.project_dir').'/secrets.json');


        $parameters = [
            'client_id' => $secrets['client_id'],
            'client_secret' => $secrets['client_secret'],
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri'  => $secrets['redirect_uri'],
            'resource' => $secrets['resource'],
        ];

        try{
            $http = new Client();
            $req = $http->request('POST', $secrets['login_url'].'/'.$secrets['tenant'].'/oauth2/token',
                [
                    'form_params' => $parameters
                ]);
            $responseBody = \GuzzleHttp\json_decode($req->getBody(), true);
            $access_token = $responseBody['access_token'];
            $_SESSION['access_token'] = $access_token;

            //Starting here, this to be removed to its own controller responsible for posting data
            $csv = ReadFile::loadCsvFile($this->getParameter('kernel.project_dir').'/data.csv');
            $topost_data = json_encode($csv);

            //Posting data to a specific dataset in PowerBi

            $request = $http->request('POST', $secrets['base_api_url'].'/'.$secrets['tenant'].'/datasets/7d17e64f-d071-4ab6-a007-46d03c93da38/rows?key=MZfMIe4XGAII2YNaJxN1BFIVFtJQu%2FKrTnLtG3%2FP6SfiUFibtxRIb44T8s31Om8aCqru29Si4GQy5zU5ZtFuLA%3D%3D',
                [
                    'headers' => [
                        'Authorization' => 'Bearer '.$_SESSION['access_token'],
                        'Content-Type' => 'application/json'
                    ],
                    'body' => $topost_data
                ]);

            $response = $request->getStatusCode();
            //return $response;
        }
        catch(ClientException $e){
            echo $e->getMessage().' *********';
        }
        return $this->render('default/auth.html.twig', ['variable_name' => $response]);
        //return $this->render('default/auth.html.twig', ['variable_name' => $url]);
    }

    /**
     * @Route("/pbcode", name="pbcode")
     */
    public function receiveAuthorizationCode( Request $request){
        $_SESSION['state'] = hash('sha256', microtime(TRUE).rand().$_SERVER['REMOTE_ADDR']);
        unset($_SESSION['access_token']);

        $secrets = ReadFile::loadJsonFile($this->getParameter('kernel.project_dir').'/secrets.json');

        $url = $secrets['login_url'].'/'.$secrets['tenant'].'/oauth2/authorize?'.http_build_query([
                'response_type' => 'code',
                'client_id' => $secrets['client_id'],
                'redirect_uri' => 'http://localhost:8000/powerbi',
                'response_mode' => 'query',
                'state' => $_SESSION['state']
            ]);

        return $this->redirect($url);
    }
    /**
     * @Route("/pbtables", name="pbtables")
     */
    public function powerbiTables(Request $request){
        $http = new Client();
        $request = $http->request('GET', 'https://api.powerbi.com/v1.0/myorg/datasets/7d17e64f-d071-4ab6-a007-46d03c93da38/tables/',
            [
                'headers' => [
                    'Authorization' => 'Bearer '.$_SESSION['access_token']
                ]
            ]);

        return $this->render('default/auth.html.twig', ['variable_name' => 'Sirabo Busingye and Tabitha'.$request->getBody()]);
    }
}

