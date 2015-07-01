<?php
/**
 * Created by PhpStorm.
 * User: drupalviking
 * Date: 01/07/15
 * Time: 10:13
 */
namespace WeatherInfo\Service;

use PDOException;
use WeatherInfo\Lib\DataSourceAwareInterface;


class TextForecast implements DataSourceAwareInterface{
  use DatabaseService;

  /**
   * @var \PDO
   */
  private $pdo;

  /**
   * Gets one text forecast by id and creation_date
   *
   * @param int $id
   * @param int $creation_date
   * @return bool|mixed
   * @throws Exception
   */
  public function get($id, $creation_date){
    try{
      $statement = $this->pdo->prepare("
        SELECT * FROM TextForecast tf
        INNER JOIN ForecastType ft on tf.id = ft.id
        WHERE tf.id = :id
        AND creation_date = :creation_date
      ");

      $statement->execute(array(
        'id' => $id,
        'creation_date' => $creation_date,
      ));
      $forecast = $statement->fetchObject();

      if(!$forecast){
        return false;
      }

      return $forecast;
    }
    catch( PDOException $e ){
      throw new Exception("Can't get Text forecast item: id = {$id}
        AND creation_date = {$creation_date}", 0, $e);
    }
  }

  /**
   * Gets all Weather forecasts for date range
   *
   * @return array
   * @throws Exception
   */
  public function fetchAll($date_from, $date_to){
    try{
      $statement = $this->pdo->prepare("
        SELECT * FROM TextForecast tf
        WHERE valid_from <= :date_from
        AND valid_to >= :date_to
      ");

      $statement->execute(array(
        "valid_from" => $date_from,
        "valid_to" => $date_to
      ));

      return $statement->fetchAll();
    }
    catch( PDOException $e){
      throw new Exception("Can't get Text forecasts");
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
      $insertString = $this->insertString('TextForecast', $data);
      $statement = $this->pdo->prepare($insertString);
      $result = $statement->execute($data);


      return $this->get($data['id'],
        $data['creation_date']);
    }
    catch( PDOException $e){
      echo "<pre>";
      print_r($e->getMessage());
      echo "</pre>";
      throw new Exception("Can't create Text forecast entry");
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
      $updateString = $this->updateString('TextForecast',
        $data,
        "id={$data['id']}
        AND creation_date={$data['creation_date']}");
      $statement = $this->pdo->prepare($updateString);
      $statement->execute($data);

      return $this->get($data['id'],
        $data['creation_date']);
    }
    catch( PDOException $e){
      echo "<pre>";
      print_r($e->getMessage());
      echo "</pre>";
      throw new Exception("Can't update Text firecast entry with id={$data['id']}
        AND creation_date={$data['creation_date']}");
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