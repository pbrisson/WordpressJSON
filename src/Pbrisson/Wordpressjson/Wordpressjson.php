<?php namespace Pbrisson\Wordpressjson;

use Illuminate\Config\Repository;
use Cache;
use Session;

class Wordpressjson {

	/**
     * Illuminate config repository.
     *
     * @var Illuminate\Config\Repository
     */
    protected $config;

	/**
     * Create a new Wordpressjson instance.
     *
     * @param  Illuminate\Config\Repository  $config
     * @return void
     */
    public function __construct(Repository $config) {
    	$this->config = $config;
    }

    /**
     * Magic method for API calls.
     *
     * @param   string  $method
     * @param   array   $args
     * @return  object
     */
    public function __call($method, $args) {
        // load url
        $url = $this->config->get('wordpressjson::url');

        // catch error
        if (!$url)
        {
            trigger_error('Wordpress configuration file not found.');
        }
        
        // add query
        $url .= '?json='.$method;
        if (!empty($args))
        {
            foreach($args[0] as $key=>$value)
            {
                $url .= '&'.$key.'='.urlencode($value);
            }
        }

        // if caching...
        if ($this->config->get('wordpressjson::cache'))
        {
            // check cache
            $hash = 'wp_'.md5($url);
            $check = Cache::get($hash);
            if ($check and !Session::get('wordpress_edit_mode'))
            {
                return $check;
            }
        }
        
        // connect to api
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $result = false;
        
        // if no error...
        if (!curl_errno($ch))
        {
            $result = json_decode($response);
        }

        curl_close($ch);

        // if caching...
        if ($this->config->get('wordpressjson::cache'))
        {
            // cache result
            Cache::put($hash, $result, $this->config->get('wordpressjson::cache'));
        }

        // return
        return $result;
    }

    /**
     * Filter string according to configuration definitions.
     *
     * @param   string  $string
     * @return  string
     */
    public function filter($string) {
        // build find/replace arrays
        $find = array();
        $replace = array();
        $filters = $this->config->get('wordpressjson::filter');
        foreach ($filters as $key => $value)
        {
            $find[] = $key;
            $replace[] = $value;
        }

        // return filtered string
        return str_ireplace($find, $replace, $string);
    }

    public function config($var) {
        return $this->config->get('wordpressjson::' . $var);
    }
}

?>