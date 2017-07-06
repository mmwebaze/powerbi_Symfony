<?php

namespace AppBundle\Controller;

use AppBundle\Util\ReadFile;
use GuzzleHttp\Client;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;

class PowerbiController extends FOSRestController {
  /**
   * @Rest\Get("/api/", name="api_home")
   */
  public function apiHomeAction() {
    // replace this example code with whatever you need
    //$readFile = new ReadFile();
    //$secrets = $readFile->loadJsonFile($this->getParameter('kernel.project_dir') . '/secrets.json');
    //$csv = $readFile->loadCsvFile($this->getParameter('kernel.project_dir') . '/data.csv');
    //var_dump($csv);
    //return $this->render('default/auth.html.twig', ['variable_name' => $this->getParameter('kernel.project_dir')]);

    return 'michael';
  }
}