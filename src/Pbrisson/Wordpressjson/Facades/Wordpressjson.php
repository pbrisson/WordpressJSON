<?php namespace Pbrisson\Wordpressjson\Facades;
 
use Illuminate\Support\Facades\Facade;
 
class Wordpressjson extends Facade {
 
  /**
   * Get the registered name of the component.
   *
   * @return string
   */
  protected static function getFacadeAccessor() { return 'wordpressjson'; }
 
}