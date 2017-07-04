<?php
namespace AppBundle\Util;


class ReadFile
{
    public function loadJsonFile($jsonFile){
        $loadJson = file_get_contents($jsonFile);

        return json_decode($loadJson, true);
    }
    public function loadCsvFile($csvFile){

        $content = array();
        $header = [];

        if (file_exists($csvFile) == 1){

            $file = fopen($csvFile, "r");
            $row = 0;

            while(($line = fgetcsv($file)) !== false){

                if ($row == 0){
                    foreach ($line as $key => $value){
                        $header[$key] = $value;
                    }
                }
                else{
                    $temp = [];
                    foreach ($line as $key => $value){
                        $temp[$header[$key]] = $value;
                    }
                    array_push($content, $temp);
                }
                $row++;
            }
            fclose($file);
        }
        return $content;
    }
}