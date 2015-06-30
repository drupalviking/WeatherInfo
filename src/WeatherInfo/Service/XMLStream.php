<?php
/**
 * Created by PhpStorm.
 * User: drupalviking
 * Date: 29/06/15
 * Time: 11:36
 */
namespace WeatherInfo\Service;

use WeatherInfo\Lib\DataSourceAwareInterface;
use GuzzleHttp;
use Zend\Dom\Query;

class XMLStream implements DataSourceAwareInterface{
  use DatabaseService;

  /**
   * @var \PDO
   */
  private $pdo;

  public function processWeatherForecasts(){
    $weatherStationService = new WeatherStation();
    $weatherStationService->setDataSource($this->pdo);
    $weatherStationIds = $weatherStationService->fetchAllIds();
    $idString = "";
    foreach($weatherStationIds as $id){
      $idString .= $id->id . ";";
    }
    $fetchString = "http://xmlweather.vedur.is/?op_w=xml&type=forec&lang=en&view=xml&ids={$idString}&time=1h";
    $weatherForecasts = $this->fetch($fetchString);

    foreach($weatherForecasts->station as $forecast){
      if($forecast->{"@attributes"}->valid){

      }
    }
  }

  public function processWeatherStations(){
    $html = $this->fetchHttp("http://www.vedur.is/vedur/stodvar/");
    $dom = new Query($html);
    $results = $dom->execute('.listtable tr.alt a');
    foreach($results as $result){
      $href = $result->nodeValue;
      $url = $result->getAttribute('href');
      if($href == 'Uppl.'){
        $rawInfo = $this->extractStationInfo($url);
        $this->processStationInfo($rawInfo);
      }
    }
  }

  /**
   * Fetches data from paths as a XML object, with GuzzleHttp client, and returns it as a stdClass objects
   *
   * @param $path
   * @return mixed|object
   */
  public function fetch($path){
    $client = new GuzzleHttp\Client();
    $result = $client->get($path);

    $res = $result->getBody()->getContents();
    $res = simplexml_load_string($res);
    $res = json_decode(json_encode($res));
    return $res;
  }

  public function fetchHttp($path){
    $client = new GuzzleHttp\Client();
    $result = $client->get($path);

    $res = $result->getBody()->getContents();
    return $res;
  }

  public function setDataSource(\PDO $pdo) {
    $this->pdo = $pdo;
  }

  /**
   * @param $url
   * @return array $stationDataRaw
   */
  private function extractStationInfo($url) {
    $stationHtml = $this->fetchHttp("http://www.vedur.is" . $url);
    $stationQuery = new Query($stationHtml);
    $stationResult = $stationQuery->execute('tbody tr td');
    $size = sizeof($stationResult);
    $stationDataRaw = array();
    $counter = 0;
    foreach ($stationResult as $sresult) {
      $stationDataRaw[] = $sresult->nodeValue;
    }

    return $stationDataRaw;
  }

  private function processStationInfo($station){
    for($i = 0; $i < 20; $i++){
      $data[$this->slugify($station[$i])] = $station[++$i];
    }

    $openParen = strpos($data['spasvaedi'], '(');
    $data['spasvaedi_short'] = substr($data['spasvaedi'], $openParen + 1, 2);
    $data['spasvaedi'] = substr($data['spasvaedi'], 0, $openParen);

    $openParen = strpos($data['stadsetning'], '(');
    $latLngValues = substr($data['stadsetning'], $openParen);
    $stringsize = strlen($latLngValues);
    $latLngValues = substr($latLngValues, 1, -1);

    $space = strrpos($latLngValues, ' ');
    $data['lat'] = str_replace(",", ".", substr($latLngValues, 0, $space-1));
    $data['lng'] = str_replace(",", ".", substr($latLngValues, $space + 1));

    $data['haed_yfir_sjo'] = (int)$data['haed_yfir_sjo'];

    $this->saveStationInfo($data);
    echo "Processing of " . $data['nafn'] . " is done\n";
  }

  private function saveStationInfo($data){
    $typeService = new StationType();
    $typeService->setDataSource($this->pdo);
    $ownerService = new StationOwner();
    $ownerService->setDataSource($this->pdo);
    $forecastAreaService = new ForecastArea();
    $forecastAreaService->setDataSource($this->pdo);
    $weatherStationService = new WeatherStation();
    $weatherStationService->setDataSource($this->pdo);

    $typeFromDatabase = $typeService->getByName($data["tegund"]);
    $ownerFromDatabase = $ownerService->getByName($data["eigandi_stodvar"]);
    $forecastAreaFromDatabase = $forecastAreaService->getByAreaName($data["spasvaedi"]);
    $weatherStationFromDatabase = $weatherStationService->get($data['stodvanumer']);

    if(!$typeFromDatabase){
      $object = array(
        "name" => $data["tegund"]
      );
      $typeFromDatabase = $typeService->create($object);
    }
    if(!$ownerFromDatabase){
      $object = array(
        "name" => $data["eigandi_stodvar"]
      );
      $ownerFromDatabase = $ownerService->create($object);
    }
    if(!$forecastAreaFromDatabase){
      $object = array(
        "name" => $data["spasvaedi"],
        "abbr" => $data["spasvaedi_short"],
      );
      $forecastAreaFromDatabase = $forecastAreaService->create($object);
    }

    $data['id'] = $data['stodvanumer'];
    $data['wmo_numer'] = $data['wmo-numer'];

    unset($data['stodvanumer']);
    unset($data['tegund']);
    unset($data['eigandi_stodvar']);
    unset($data['spasvaedi']);
    unset($data['spasvaedi_short']);
    unset($data['wmo-numer']);

    $data['type_id'] = $typeFromDatabase->id;
    $data['forecast_area_id'] = $forecastAreaFromDatabase->id;
    $data['owner_id'] = $ownerFromDatabase->id;


    if($weatherStationFromDatabase){
      $weatherStationService->update($data['id'], $data);
    }
    else{
      $weatherStationService->create($data);
    }
  }

  private function slugify($value){
    $value = strtolower($value);
    $value = str_replace(' ', '_', $value);
    $value = str_replace('á', 'a', $value);
    $value = str_replace('Á', 'a', $value);
    $value = str_replace('é', 'e', $value);
    $value = str_replace('É', 'e', $value);
    $value = str_replace('í', 'i', $value);
    $value = str_replace('Í', 'i', $value);
    $value = str_replace('ó', 'o', $value);
    $value = str_replace('Ó', 'o', $value);
    $value = str_replace('ú', 'u', $value);
    $value = str_replace('Ú', 'u', $value);
    $value = str_replace('ö', 'o', $value);
    $value = str_replace('Ö', 'o', $value);
    $value = str_replace('ð', 'd', $value);
    $value = str_replace('Ð', 'd', $value);
    $value = str_replace('æ', 'ae', $value);
    $value = str_replace('Æ', 'ae', $value);
    return $value;
  }
}