<?php
/**
 * Created by PhpStorm.
 * User: drupalviking
 * Date: 30/06/15
 * Time: 10:00
 */
namespace WeatherInfo\Controller;

use Zend\Console\Request as ConsoleRequest;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver;

class ConsoleController extends AbstractActionController{
  public function processWeatherStationsAction(){
    $sm = $this->getServiceLocator();
    $xmlStreamService = $sm->get('WeatherInfo\Service\XMLStream');
    $xmlStreamService->processWeatherStations();
  }

  public function processWeatherForecastsAction(){
    $sm = $this->getServiceLocator();
    $xmlStreamService = $sm->get('WeatherInfo\Service\XMLStream');
    $xmlStreamService->processWeatherForecasts();
  }

  public function processTextForecastsAction(){
    $sm = $this->getServiceLocator();
    $xmlStreamService = $sm->get('WeatherInfo\Service\XMLStream');
    $xmlStreamService->processTextForecasts();
  }

  public function processWeatherObservationsAction(){
    $sm = $this->getServiceLocator();
    $xmlStreamService = $sm->get('WeatherInfo\Service\XMLStream');
    $xmlStreamService->processWeatherObservations();
  }
}
