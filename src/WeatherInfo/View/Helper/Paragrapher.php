<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 2/10/14
 * Time: 4:47 PM
 */

namespace WeatherInfo\View\Helper;

use Zend\View\Helper\AbstractHelper;
use WeatherInfo\Lib\Parsedown;

class Paragrapher extends AbstractHelper{

	private static $parser;

    public function __invoke($value){
		if( $value=='' ){
            return '';
        }
		//SINGLE INSTANCE
		//	only create one instance of Parser
		if( !self::$parser ){
			self::$parser = new Parsedown();
		}
		return self::$parser->text($value);
    }
} 