<?php
/**
 * Created by PhpStorm.
 * User: drupalviking
 * Date: 01/07/15
 * Time: 09:27
 */
namespace WeatherInfo\Service;

use PDOException;
use WeatherInfo\Lib\DataSourceAwareInterface;


class ForecastType implements DataSourceAwareInterface {
  use DatabaseService;

  /**
   * @var \PDO
   */
  private $pdo;

  /**
   * Gets one Forecast Type by id
   *
   * @param $id
   * @return bool|mixed
   * @throws Exception
   */
  public function get($id) {
    try {
      $statement = $this->pdo->prepare("
        SELECT * FROM ForecastType
        WHERE id = :id
      ");

      $statement->execute(array(
        'id' => $id
      ));
      $type = $statement->fetchObject();

      if (!$type) {
        return FALSE;
      }

      return $type;
    } catch (PDOException $e) {
      throw new Exception("Can't get Forecast Type item [{$id}]", 0, $e);
    }
  }

  /**
   * Gets all Forecast Types
   *
   * @return array
   * @throws Exception
   */
  public function fetchAll() {
    try {
      $statement = $this->pdo->prepare("
        SELECT * FROM ForecastType
      ");

      $statement->execute();

      return $statement->fetchAll();
    } catch (PDOException $e) {
      throw new Exception("Can't get Forecast Types");
    }
  }

  /**
   * Creates a Forecast Type entry in the database
   *
   * @param array $data
   * @return int
   * @throws Exception
   */
  public function create(array $data) {
    try {
      $insertString = $this->insertString('ForecastType', $data);
      $statement = $this->pdo->prepare($insertString);
      $statement->execute($data);
      $id = (int) $this->pdo->lastInsertId();
      return $this->get($id);

    } catch (PDOException $e) {
      throw new Exception("Can't create Forecast Type entry");
    }
  }

  /**
   * Updates a Forecast Type in the database
   *
   * @param $id
   * @param array $data
   * @return int
   * @throws Exception
   */
  public function update($id, array $data) {
    try {
      $updateString = $this->updateString('ForecastType', $data, "id={$id}");
      $statement = $this->pdo->prepare($updateString);
      $statement->execute($data);

      return $statement->rowCount();
    } catch (PDOException $e) {
      throw new Exception("Can't update Forecast Type entry with id [{$id}]");
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
