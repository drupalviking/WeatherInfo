<?php
/**
 * Created by PhpStorm.
 * User: drupalviking
 * Date: 2/10/14
 * Time: 4:47 PM
 */

namespace WeatherInfo\View\Helper;

use Zend\View\Helper\AbstractHelper;

class Slugify extends AbstractHelper{
  public function __invoke($value){
    if( $value=='' ){
      return '';
    }
    //SINGLE INSTANCE
    //	only create one instance of Parser
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

    return $value;
  }
}