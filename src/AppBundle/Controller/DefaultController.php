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
        $readFile = new ReadFile();
        $secrets = $readFile->loadJsonFile($this->getParameter('kernel.project_dir').'/secrets.json');
        $csv = $readFile->loadCsvFile($this->getParameter('kernel.project_dir').'/data.csv');

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
            $req = $http->request('POST', 'https://login.windows.net/bece100a-d156-46eb-b19f-999db030121f/oauth2/token',
                [
                    'form_params' => $parameters
                ]);
            $responseBody = \GuzzleHttp\json_decode($req->getBody(), true);
            $access_token = $responseBody['access_token'];
           // $http = new Client();
            /*$request = $http->request('GET', 'https://api.powerbi.com/v1.0/myorg/datasets/7d17e64f-d071-4ab6-a007-46d03c93da38/tables/',
                [
                    'headers' => [
                        'Authorization' => 'Bearer '.$access_token
                    ]
                ]);*/

            $data = [
                'name'=> 'Uganda',
                'uid' => 'UG',
                'value' => 1200
            ];
            $data1 = [
                'name'=> 'Kenya',
                'uid' => 'KE',
                'value' => 1450
            ];
            $arr = [];
            array_push($arr, $data);
            array_push($arr, $data1);
            $topost_data = json_encode($csv);
            /*var_dump($topost_data);
            var_dump('*******************************************');
            var_dump($arr);
            die();*/

            //https://api.powerbi.com/beta/bece100a-d156-46eb-b19f-999db030121f/datasets/7d17e64f-d071-4ab6-a007-46d03c93da38/rows
            $request = $http->request('POST', 'https://api.powerbi.com/beta/bece100a-d156-46eb-b19f-999db030121f/datasets/7d17e64f-d071-4ab6-a007-46d03c93da38/rows?key=MZfMIe4XGAII2YNaJxN1BFIVFtJQu%2FKrTnLtG3%2FP6SfiUFibtxRIb44T8s31Om8aCqru29Si4GQy5zU5ZtFuLA%3D%3D',
                [
                    'headers' => [
                        'Authorization' => 'Bearer '.$access_token,
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
        $url = 'https://login.windows.net/bece100a-d156-46eb-b19f-999db030121f/oauth2/authorize?'.http_build_query([
                'response_type' => 'code',
                'client_id' => '1d99b42d-5c48-414e-acff-6e9ac123d9f0',
                'redirect_uri' => 'http://localhost:8000/powerbi',
                'response_mode' => 'query'
            ]);

        return $this->redirect($url);
    }
    /**
     * @Route("/code", name="code")
     */
    public function powerbiCode(Request $request){
        //$code = $request->get('code');

        return $this->render('default/auth.html.twig', ['variable_name' => 'Sirabo Busingye and Tabitha']);
    }
}

