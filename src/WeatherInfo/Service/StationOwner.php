<?php
/**
 * Created by PhpStorm.
 * User: drupalviking
 * Date: 29/06/15
 * Time: 15:53
 */
namespace WeatherInfo\Service;

use PDOException;
use WeatherInfo\Lib\DataSourceAwareInterface;


class StationOwner implements DataSourceAwareInterface{
  use DatabaseService;

  /**
   * @var \PDO
   */
  private $pdo;

  /**
   * Gets one Station Owner by id
   *
   * @param $id
   * @return bool|mixed
   * @throws Exception
   */
  public function get($id){
    try{
      $statement = $this->pdo->prepare("
        SELECT * FROM StationOwner
        WHERE id = :id
      ");

      $statement->execute(array(
        'id' => $id
      ));
      $owner = $statement->fetchObject();

      if(!$owner){
        return false;
      }

      return $owner;
    }
    catch( PDOException $e ){
      throw new Exception("Can't get Station Owner item [{$id}]", 0, $e);
    }
  }

  public function getByName($name){
    try{
      $statement = $this->pdo->prepare("
        SELECT * FROM StationOwner
        WHERE `name` = :stationname
      ");

      $statement->execute(array(
        'stationname' => $name
      ));
      $owner = $statement->fetchObject();

      if(!$owner){
        return false;
      }

      return $owner;
    }
    catch( PDOException $e ){
      throw new Exception("Can't get Station Owner item [{$name}]", 0, $e);
    }
  }

  /**
   * Gets all Station Owners
   *
   * @return array
   * @throws Exception
   */
  public function fetchAll(){
    try{
      $statement = $this->pdo->prepare("
        SELECT * FROM StationOwner
      ");

      $statement->execute();

      return $statement->fetchAll();
    }
    catch( PDOException $e){
      throw new Exception("Can't get Station Owners");
    }
  }

  /**
   * Creates a Station Owner entry in the database
   *
   * @param array $data
   * @return int
   * @throws Exception
   */
  public function create(array $data){
    try{
      $insertString = $this->insertString('StationOwner', $data);
      $statement = $this->pdo->prepare($insertString);
      $statement->execute($data);
      $id = (int)$this->pdo->lastInsertId();

      return $this->get($id);
    }
    catch( PDOException $e){
      throw new Exception("Can't create Station Owner entry");
    }
  }

  /**
   * Updates a Station Owner entry in the database
   *
   * @param $id
   * @param array $data
   * @return int
   * @throws Exception
   */
  public function update($id, array $data){
    try{
      $updateString = $this->updateString('StationOwner', $data, "id={$id}");
      $statement = $this->pdo->prepare($updateString);
      $statement->execute($data);

      return $statement->rowCount();
    }
    catch( PDOException $e){
      throw new Exception("Can't update Station Owner entry with id [{$id}]");
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