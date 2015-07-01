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
        $weatherForecastService = new WeatherForecast();
        $weatherForecastService->setDataSource($this->pdo);
        foreach($forecast->forecast as $fcast){
          $data = $this->createWeatherDataArray($forecast, $fcast, "forecast");

          $forecastFromDatabase = $weatherForecastService->get(
            $forecast->{"@attributes"}->id,
            strtotime($forecast->atime),
            strtotime($fcast->ftime),
              "forecast");

          if($forecastFromDatabase){
            $weatherForecastService->update($data);
          }
          else{
            $weatherForecastService->create($data);
          }
          echo "Processed " . $forecast->name . " @" . strftime("%d.%m.%Y %H:%M", $data['forecast_time']) . "\n";
        }
      }
    }
  }

  public function processWeatherObservations(){
    $weatherStationService = new WeatherStation();
    $weatherStationService->setDataSource($this->pdo);
    $weatherStationIds = $weatherStationService->fetchAllIds();
    $idString = "";
    foreach($weatherStationIds as $id){
      $idString .= $id->id . ";";
    }
    $fetchString = "http://xmlweather.vedur.is/?op_w=xml&type=obs&lang=en&view=xml&ids={$idString}&time=1h";
    $weatherForecasts = $this->fetch($fetchString);

    foreach($weatherForecasts->station as $forecast){
      if($forecast->{"@attributes"}->valid){
        $weatherForecastService = new WeatherForecast();
        $weatherForecastService->setDataSource($this->pdo);

        $data = array(
          "station_id" => $forecast->{"@attributes"}->id,
          "analysis_time" => strtotime($forecast->time),
          "forecast_time" => strtotime($forecast->time),
          "obs_forecast" => "obs"
        );
        if(isset($forecast->F)){
          $data["wind_force"] = (is_string($forecast->F) ? $forecast->F : null);
        }
        if(isset($forecast->FX)){
          $data["wind_force_peak"] = (is_string($forecast->FX) ? $forecast->FX : null);
        }
        if(isset($forecast->FG)){
          $data["wind_force_gust"] = (is_string($forecast->FG) ? $forecast->FG : null);
        }
        if(isset($forecast->D)){
          $data["wind_direction"] = (is_string($forecast->D) ? $forecast->D : null);
        }
        if(isset($forecast->T)){
          $data["temperature"] = (is_string($forecast->T) ? $forecast->T : null);
        }
        if(isset($forecast->W)){
          $data["weather_description"] = (is_string($forecast->W) ? $forecast->W : null);
        }
        if(isset($forecast->V)){
          $data["visibility"] = (is_string($forecast->V) ? $forecast->V : null);
        }
        if(isset($forecast->N)){
          $data["clouds"] = (is_string($forecast->N) ? $forecast->N : null);
        }
        if(isset($forecast->P)){
          $data["pressure"] = (is_string($forecast->P) ? $forecast->P : null);
        }
        if(isset($forecast->RH)){
          $data["percipitation"] = (is_string($forecast->RH) ? $forecast->RH : null);
        }
        if(isset($forecast->SNC)){
          $data["snow_description"] = (is_string($forecast->SNC) ? $forecast->SNC : null);
        }
        if(isset($forecast->SND)){
          $data["snow_depth"] = (is_string($forecast->SND) ? $forecast->SND : null);
        }
        if(isset($forecast->SED)){
          $data["ocean"] = (is_string($forecast->SED) ? $forecast->SED : null);
        }
        if(isset($forecast->RTE)){
          $data["road_temperature"] = (is_string($forecast->RTE) ? $forecast->RTE : null);
        }
        if(isset($forecast->TD)){
          $data["dew_point"] = (is_string($forecast->TD) ? $forecast->TD : null);
        }
        if(isset($forecast->R)){
          $data["accumulated_rain"] = (is_string($forecast->R) ? $forecast->R : null);
        }

        $forecastFromDatabase = $weatherForecastService->get(
          $forecast->{"@attributes"}->id,
          strtotime($forecast->time),
          strtotime($forecast->time),
          "obs");

        if($forecastFromDatabase){
          $weatherForecastService->update($data);
        }
        else{
          $weatherForecastService->create($data);
        }
        echo "Processed " . $forecast->name . " @" . strftime("%d.%m.%Y %H:%M", $data['forecast_time']) . "\n";

      }
    }
  }

  public function createWeatherDataArray($forecast, $fcast, $forecast_obs){
    $data = array(
      "station_id" => $forecast->{"@attributes"}->id,
      "analysis_time" => strtotime($forecast->atime),
      "forecast_time" => strtotime($fcast->ftime),
      "obs_forecast" => $forecast_obs,
      "wind_force" => (isset($fcast->F) ? $fcast->F : null),
      "wind_force_peak" => (isset($fcast->FX) ? $fcast->FX : null),
      "wind_force_gust" => (isset($fcast->FG) ? $fcast->FG : null),
      "wind_direction" => (isset($fcast->D) ? $fcast->D : null),
      "temperature" => (isset($fcast->T) ? $fcast->T : null),
      "weather_description" => (isset($fcast->W) ? $fcast->W : null),
      "visibility" => (isset($fcast->V) ? $fcast->V : null),
      "clouds" => (isset($fcast->N) ? $fcast->N : null),
      "pressure" => (isset($fcast->P) ? $fcast->P : null),
      "percipitation" => (isset($fcast->RH) ? $fcast->RH : null),
      "snow_description" => (isset($fcast->SNC) ? $fcast->SNC : null),
      "snow_depth" => (isset($fcast->SND) ? $fcast->SND : null),
      "ocean" => (isset($fcast->SED) ? $fcast->SED : null),
      "road_temperature" => (isset($fcast->RTE) ? $fcast->RTE : null),
      "dew_point" => (isset($fcast->TD) ? $fcast->TD : null),
      "accumulated_rain" => (isset($fcast->R) ? $fcast->R : null)
    );

    return $data;
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

  public function processTextForecasts(){
    $forecastTypeService = new ForecastType();
    $forecastTypeService->setDataSource($this->pdo);
    $forecastTypes = $forecastTypeService->fetchAll();
    $textForecastService = new TextForecast();
    $textForecastService->setDataSource($this->pdo);

    $typeString = "";
    foreach($forecastTypes as $ftype){
      $typeString .= $ftype->id . ";";
    }

    $textForecasts = $this->fetch("http://xmlweather.vedur.is/?op_w=xml&type=txt&lang=is&view=xml&ids=" . $typeString);
    foreach($textForecasts->text as $tfcast){
      if(is_string($tfcast->content)){
        $creation_date = strtotime($tfcast->creation);
        $valid_from = strtotime($tfcast->valid_from);
        $valid_to = strtotime($tfcast->valid_to);
        $id = $tfcast->{"@attributes"}->id;
        $type = $forecastTypeService->get($id);

        $data = array(
          "id" => $id,
          "creation_date" => $creation_date,
          "valid_from" => $valid_from,
          "valid_to" => $valid_to,
          "content" => $tfcast->content
        );

        $forecastFromDatabase = $textForecastService->get($id, $creation_date);

        if($forecastFromDatabase){
          $textForecastService->update($data);
        }
        else{
          $textForecastService->create($data);
        }
        echo "Done processing " . $type->type . "\n";
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
    $value = mb_strtolower($value);
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