<?php
/**
 * Created by PhpStorm.
 * User: drupalviking
 * Date: 29/06/15
 * Time: 15:48
 */
namespace WeatherInfo\Service;

use PDOException;
use WeatherInfo\Lib\DataSourceAwareInterface;


class WeatherStation implements DataSourceAwareInterface{
  use DatabaseService;

  /**
   * @var \PDO
   */
  private $pdo;

  /**
   * Gets one route by id
   *
   * @param $id
   * @return bool|mixed
   * @throws Exception
   */
  public function get($id){
    try{
      $statement = $this->pdo->prepare("
        SELECT * FROM WeatherStation
        WHERE id = :id
      ");

      $statement->execute(array(
        'id' => $id
      ));
      $route = $statement->fetchObject();

      if(!$route){
        return false;
      }

      return $route;
    }
    catch( PDOException $e ){
      throw new Exception("Can't get WeatherStation item [{$id}]", 0, $e);
    }
  }

  /**
   * Gets all Weather stations ids
   *
   * @return array
   * @throws Exception
   */
  public function fetchAllIds(){
    try{
      $statement = $this->pdo->prepare("
        SELECT id FROM WeatherStation
        ORDER BY id
      ");

      $statement->execute();

      return $statement->fetchAll();
    }
    catch( PDOException $e){
      throw new Exception("Can't get Weather stations");
    }
  }


  /**
   * Gets all Weather stations
   *
   * @return array
   * @throws Exception
   */
  public function fetchAll(){
    try{
      $statement = $this->pdo->prepare("
        SELECT * FROM WeatherStation
      ");

      $statement->execute();

      return $statement->fetchAll();
    }
    catch( PDOException $e){
      throw new Exception("Can't get Weather stations");
    }
  }

  /**
   * Creates a WeatherStation entry in the database
   *
   * @param array $data
   * @return int
   * @throws Exception
   */
  public function create(array $data){
    try{
      $insertString = $this->insertString('WeatherStation', $data);
      $statement = $this->pdo->prepare($insertString);
      $statement->execute($data);
      $id = (int)$this->pdo->lastInsertId();

      return $this->get($id);
    }
    catch( PDOException $e){
      echo "<pre>";
      print_r($e->getMessage());
      echo "</pre>";
      throw new Exception("Can't create Weather station entry");
    }
  }

  /**
   * Updates a WeatherStation entry in the database
   *
   * @param $id
   * @param array $data
   * @return int
   * @throws Exception
   */
  public function update($id, array $data){
    try{
      $updateString = $this->updateString('WeatherStation', $data, "id={$id}");
      $statement = $this->pdo->prepare($updateString);
      $statement->execute($data);

      return $statement->rowCount();
    }
    catch( PDOException $e){
      echo "<pre>";
      print_r($e->getMessage());
      echo "</pre>";
      throw new Exception("Can't update Weather station entry with id [{$id}]");
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