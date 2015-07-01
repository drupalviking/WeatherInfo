<?php
/**
 * Created by PhpStorm.
 * User: drupalviking
 * Date: 30/06/15
 * Time: 14:09
 */
namespace WeatherInfo\Service;

use PDOException;
use WeatherInfo\Lib\DataSourceAwareInterface;


class WeatherForecast implements DataSourceAwareInterface{
  use DatabaseService;

  /**
   * @var \PDO
   */
  private $pdo;

  /**
   * Gets one weather forecast by station_id, analysis_time, forecast_time and observation/forecast
   *
   * @param int $station_id
   * @param int $analysis_time
   * @param int $forecast_time
   * @param string $obs_forecast
   * @return bool|mixed
   * @throws Exception
   */
  public function get($station_id, $analysis_time, $forecast_time, $obs_forecast){
    try{
      $statement = $this->pdo->prepare("
        SELECT * FROM WeatherForecast
        WHERE station_id = :station_id
        AND analysis_time = :analysis_time
        AND forecast_time = :forecast_time
        AND obs_forecast = :obs_forecast
      ");

      $statement->execute(array(
        'station_id' => $station_id,
        'analysis_time' => $analysis_time,
        'forecast_time' => $forecast_time,
        'obs_forecast' => $obs_forecast
      ));
      $forecast = $statement->fetchObject();

      if(!$forecast){
        return false;
      }

      return $forecast;
    }
    catch( PDOException $e ){
      throw new Exception("Can't get Weather forecast item: station_id = :station_id
        AND analysis_time = :analysis_time
        AND forecast_time = :forecast_time
        AND obs_forecast = :obs_forecast", 0, $e);
    }
  }

  /**
   * Gets all Weather forecast by analysis time
   *
   * @return array
   * @throws Exception
   */
  public function fetchAll($analysis_time){
    try{
      $statement = $this->pdo->prepare("
        SELECT * FROM WeatherForecast
        WHERE analysis_time = :analysis_time
      ");

      $statement->execute(array(
        "analysis_time" => $analysis_time,
      ));

      return $statement->fetchAll();
    }
    catch( PDOException $e){
      throw new Exception("Can't get Weather forecasts");
    }
  }

  /**
   * Creates a Weather forecast entry in the database
   *
   * @param array $data
   * @return int
   * @throws Exception
   */
  public function create(array $data){
    try{
      $insertString = $this->insertString('WeatherForecast', $data);
      $statement = $this->pdo->prepare($insertString);
      $result = $statement->execute($data);


      return $this->get($data['station_id'],
        $data['analysis_time'],
        $data['forecast_time'],
        $data['obs_forecast']);
    }
    catch( PDOException $e){
      echo "<pre>";
      print_r($e->getMessage());
      echo "</pre>";
      throw new Exception("Can't create Weather forecast entry");
    }
  }

  /**
   * Updates a Weather forecast entry in the database
   *
   * @param $id
   * @param array $data
   * @return int
   * @throws Exception
   */
  public function update(array $data){
    try{
      $updateString = $this->updateString('WeatherForecast',
        $data,
        "station_id={$data['station_id']}
        AND analysis_time={$data['analysis_time']}
        AND forecast_time={$data['forecast_time']}
        AND obs_forecast='{$data['obs_forecast']}'");
      $statement = $this->pdo->prepare($updateString);
      $statement->execute($data);

      return $statement->rowCount();
    }
    catch( PDOException $e){
      echo "<pre>";
      print_r($e->getMessage());
      echo "</pre>";
      throw new Exception("Can't update Weather station entry with station_id={$data['station_id']}
        AND analysis_time={$data['analysis_time']}
        AND forecast_time={$data['forecast_time']}
        AND obs_forecast={$data['obs_forecast']}");
    }
  }

  /**
   * Sets the Datasource
   *
   * @param \PDO $pdo
   * @return null
   */
  public function setDataSource(\PDO $pdo){
    $this->pdo = $pdo;
  }
}