<?php
/**
 * Created by PhpStorm.
 * User: drupalviking
 * Date: 29/06/15
 * Time: 15:55
 */
namespace WeatherInfo\Service;

use PDOException;
use WeatherInfo\Lib\DataSourceAwareInterface;


class StationType implements DataSourceAwareInterface{
  use DatabaseService;

  /**
   * @var \PDO
   */
  private $pdo;

  /**
   * Gets one Station Type by id
   *
   * @param $id
   * @return bool|mixed
   * @throws Exception
   */
  public function get($id){
    try{
      $statement = $this->pdo->prepare("
        SELECT * FROM StationType
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
      throw new Exception("Can't get Station Type item [{$id}]", 0, $e);
    }
  }

  public function getByName($type){
    try{
      $statement = $this->pdo->prepare("
        SELECT * FROM StationType
        WHERE `name` = :stationtype
      ");

      $statement->execute(array(
        'stationtype' => $type
      ));
      $route = $statement->fetchObject();

      if(!$route){
        return false;
      }

      return $route;
    }
    catch( PDOException $e ){
      throw new Exception("Can't get Station Type item [{$type}]", 0, $e);
    }
  }


  /**
   * Gets all Station Types
   *
   * @return array
   * @throws Exception
   */
  public function fetchAll(){
    try{
      $statement = $this->pdo->prepare("
        SELECT * FROM StationType
      ");

      $statement->execute();

      return $statement->fetchAll();
    }
    catch( PDOException $e){
      throw new Exception("Can't get Station Types");
    }
  }

  /**
   * Creates a Station Type entry in the database
   *
   * @param array $data
   * @return int
   * @throws Exception
   */
  public function create(array $data){
    try{
      $insertString = $this->insertString('StationType', $data);
      $statement = $this->pdo->prepare($insertString);
      $statement->execute($data);
      $id = (int)$this->pdo->lastInsertId();

      return $this->get($id);
    }
    catch( PDOException $e){
      throw new Exception("Can't create Station Type entry");
    }
  }

  /**
   * Updates a Station Type entry in the database
   *
   * @param $id
   * @param array $data
   * @return int
   * @throws Exception
   */
  public function update($id, array $data){
    try{
      $updateString = $this->updateString('StationType', $data, "id={$id}");
      $statement = $this->pdo->prepare($updateString);
      $statement->execute($data);

      return $statement->rowCount();
    }
    catch( PDOException $e){
      throw new Exception("Can't update Station Type entry with id [{$id}]");
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