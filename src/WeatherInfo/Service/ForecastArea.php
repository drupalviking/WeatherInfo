<?php
/**
 * Created by PhpStorm.
 * User: drupalviking
 * Date: 29/06/15
 * Time: 16:01
 */
namespace WeatherInfo\Service;

use PDOException;
use WeatherInfo\Lib\DataSourceAwareInterface;


class ForecastArea implements DataSourceAwareInterface {
  use DatabaseService;

  /**
   * @var \PDO
   */
  private $pdo;

  /**
   * Gets one Forecast Area by id
   *
   * @param $id
   * @return bool|mixed
   * @throws Exception
   */
  public function get($id) {
    try {
      $statement = $this->pdo->prepare("
        SELECT * FROM ForecastArea
        WHERE id = :id
      ");

      $statement->execute(array(
        'id' => $id
      ));
      $area = $statement->fetchObject();

      if (!$area) {
        return FALSE;
      }

      return $area;
    } catch (PDOException $e) {
      throw new Exception("Can't get WeatherStation item [{$id}]", 0, $e);
    }
  }

  public function getByAreaName($name){
    try {
      $statement = $this->pdo->prepare("
        SELECT * FROM ForecastArea
        WHERE `name` = :area_name
      ");

      $statement->execute(array(
        'area_name' => $name
      ));
      $area = $statement->fetchObject();

      if (!$area) {
        return FALSE;
      }

      return $area;
    } catch (PDOException $e) {
      throw new Exception("Can't get WeatherStation item [{$name}]", 0, $e);
    }
  }

  /**
   * Gets all Forecast Areas
   *
   * @return array
   * @throws Exception
   */
  public function fetchAll() {
    try {
      $statement = $this->pdo->prepare("
        SELECT * FROM ForecastArea
      ");

      $statement->execute();

      return $statement->fetchAll();
    } catch (PDOException $e) {
      throw new Exception("Can't get Forecast Areas");
    }
  }

  /**
   * Creates a Forecast Area entry in the database
   *
   * @param array $data
   * @return int
   * @throws Exception
   */
  public function create(array $data) {
    try {
      $insertString = $this->insertString('ForecastArea', $data);
      $statement = $this->pdo->prepare($insertString);
      $statement->execute($data);
      $id = (int) $this->pdo->lastInsertId();
      return $this->get($id);

    } catch (PDOException $e) {
      throw new Exception("Can't create Forecast Area entry");
    }
  }

  /**
   * Updates a Forecast Area in the database
   *
   * @param $id
   * @param array $data
   * @return int
   * @throws Exception
   */
  public function update($id, array $data) {
    try {
      $updateString = $this->updateString('ForecastArea', $data, "id={$id}");
      $statement = $this->pdo->prepare($updateString);
      $statement->execute($data);

      return $statement->rowCount();
    } catch (PDOException $e) {
      throw new Exception("Can't update Forecast Area entry with id [{$id}]");
    }
  }

  /**
   * Sets the Datasource
   *
   * @param \PDO $pdo
   * @return null
   */
  public function setDataSource(\PDO $pdo) {
    $this->pdo = $pdo;
  }
}