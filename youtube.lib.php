<?php

/*
 * Youtube public API
 *
 * @author Vitor Barbosa <vitor.barbosa@byside.com>
 */

class Youtube {

    /**
     * Version
     */
    const VERSION = '0.1';
    const LAST_UPDATE = 'JULY 15, 2010';

    /**
    * Default options for curl.
    */
    protected static $CURL_OPTS = array(
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 60,
        CURLOPT_USERAGENT      => 'youtube-php-0.1',
    );

    /**
    * Maps aliases to Youtube domains.
    */
    protected static $URL_MAP = array(
        'www'           => 'http://www.youtube.com/',
        'gdata'         => 'http://gdata.youtube.com/',
        'feeds'         => 'http://gdata.youtube.com/feeds/',
        'api'           => 'http://gdata.youtube.com/feeds/api/',
        'users'         => 'http://gdata.youtube.com/feeds/api/users/',
        'standardfeeds' => 'http://gdata.youtube.com/feeds/api/standardfeeds/',
        'playlists'     => 'http://gdata.youtube.com/feeds/api/playlists/',
        'search'        => 'http://gdata.youtube.com/feeds/api/videos?q='
    );
    
    protected static $XMLNS_MAP = array(
        "xmlns"         => "http://www.w3.org/2005/Atom",
        "media"         => "http://search.yahoo.com/mrss/",
        "openSearch"    => "http://a9.com/-/spec/opensearchrss/1.0/",
        "gd"            => "http://schemas.google.com/g/2005",
        "yt"            => "http://gdata.youtube.com/schemas/2007"
    );


    protected static $AVAILABLE_TYPES = array(
        'uploads', 'favorites', 'standardfeed', 'playlist', 'search'
    );

    protected static $JSON_ALT_FLAG = "json";

    /**
    * The Youtube Application Options.
    */
    private $options = array(
        'type'          => 'uploads',
        'limit'         => 5,
        'user'          => '',
        'search'        => '',
        'standardfeed'  => '',
        'playlist'      => '',
        'format'        => 'mpeg',
        'time'          => 'all_time',
        'orderby'       => 'published'
    );

    /**
	 * the last HTTP status code returned
	 * @access private
	 * @var integer
	 */
	private $last_http_status;

	/**
	 * the whole URL of the last API call
	 * @access private
	 * @var string
	 */
	private $last_api_call;

    /**
	 * the whole data retrieved from the last API call
	 * @access private
	 * @var string
	 */
	private $last_api_data;

    /**
    * Initialize a Youtube Application.
    *
    * The configuration:
    * - options mixed
    *
    * @param Array $config the application configuration
    */
    public function __construct(/* polymorphic */) {

        $args = func_get_args();
        if(!isset($args[0])){
            return $this;
        }

        if (is_array($args[0])) {
            return $this->setOptions($args[0]);
        } else {
            return $this->_setUser($args[0]);
        }

    }

    /**
    * Set the Youtube application options.
    *
    * @param String $user the Youtube User ID
    */
    public function setOptions($options) {
        foreach ($options as $key => $value) {
            array_key_exists($key, $this->options) && isset($value) ? $this->options[$key] = $value : '';
        }
        return $this;
    }

    public function getOptions() {
        return $this->options;
    }

    protected function _setLimit($limit){
        isset($limit) ? $this->options['limit'] = $limit : '';
        return $this;
    }

    protected function _getLimit(){
        return $this->options['limit'];
    }

    protected function _setCallType($type){
        isset($type) ? $this->options['type'] = $type : '';
        return $this;
    }

    protected function _getCallType(){
        return $this->options['type'];
    }

    protected function _setUser($user){
        isset($user) ? $this->options['user'] = $user : '';
        return $this;
    }

    protected function _getUser(){
        return $this->options['user'];
    }
    
    protected function _setSearch($search){
        isset($search) ? $this->options['search'] = $search : '';
        return $this;
    }

    protected function _getSearch(){
        return $this->options['search'];
    }

    protected function _setStandardFeed($feed){
        isset($feed) ? $this->options['standardfeed'] = $feed : '';
        return $this;
    }

    protected function _getStandardFeed(){
        return $this->options['standardfeed'];
    }

    protected function _setPlaylist($playlist){
        isset($playlist) ? $this->options['playlist'] = $playlist : '';
        return $this;
    }

    protected function _getPlaylist(){
        return $this->options['playlist'];
    }

    protected function _setFormat($format){
        isset($format) ? $this->options['format'] = $format : '';
        return $this;
    }

    protected function _getFormat(){
        return $this->options['format'];
    }

    protected function _setTime($time){
        isset($time) ? $this->options['time'] = $time : '';
        return $this;
    }

    protected function _getTime(){
        return $this->options['time'];
    }

    protected function _setOrderBy($order){
        isset($order) ? $this->options['orderby'] = $order : '';
        return $this;
    }

    protected function _getOrderBy(){
        return $this->options['orderby'];
    }

    public function printOptionsArray(){
        print_r($this->options);
    }

    /**
     *
     * Checks if the request type is valid
     *
     * @return Boolean
     */
    protected function isValidType(){
        return in_array($this->_getCallType(), self::AVAILABLE_TYPES);
    }

    /**
    * Make an API call.
    *
    * options [optional]
    *
    * @param Array $params the API call parameters
    * @return the decoded response
    */
    public function apiCall($options = array()) {
        if(isset($options) && is_array($options) && count($options) > 0){
            $this->configure($options);
        }

        if($this->isValidType()){
            return call_user_func_array(array($this, "_".$this->_getCallType()), array());
        } else {
            return null;
        }
    }

    public function getUserUploads( $options = array() ){
        $this->setOptions($options);

        if($this->options['user'] != ''){
            return $this->_uploads();
        }
    }

    public function getUserFavorites( $options = array() ){
        if(is_array($options)){
            $this->setOptions($options);
        }

        if($this->options['user'] != ''){
            return $this->_favorites();
        }
    }


    /**
     * 
     */
    protected function _uploads(){
        $path = self::$URL_MAP['users'].$this->options['user']."/"."uploads";
        
        $data = $this->requestData($path);
    }

    protected function _favorites(){
        $path = self::$URL_MAP['users'].$this->options['user']."/"."favorites";

        $data = $this->requestData($path);
    }


    /**
    * Prepares the request and retrieves the result data.
    *
    * @param $path String path (without a leading slash)
    * @param $params Array optional query parameters
    * @return $data JSON result data for the given parameters
    */
    protected function requestData($path){
        $data = json_decode($this->makeRequest( $this->getUrl($path, $this->getUrlParams() ) ), true);
        $this->last_api_data = $data;

        return $data;
    }

    /**
    * Build the URL for given domain alias, path and parameters.
    *
    * @param $path String path (without a leading slash)
    * @param $params Array optional query parameters
    * @return String the URL for the given parameters
    */
    protected function getUrl($path, $params = array()) {
        if ($params) {
            $path .= '?' . http_build_query($params);
        }
        return $path;
    }

    /**
    * Build the URL for given domain alias, path and parameters.
    *
    * @param $path String path (without a leading slash)
    * @param $params Array optional query parameters
    * @return String the URL for the given parameters
    */
    protected function getUrlParams() {
        $format = $this->_getFormat();
        $format = $format == "mpeg" ? 6 : ($format == "h263" ? 1 : 5);

        $params = array(
            "alt" => self::$JSON_ALT_FLAG,
            "max-results" => $this->_getLimit(),
            "format" => $format,
            "orderby" => $this->_getOrderby(),
            "time" => $this->_getTime()
        );

        /*$type = $this->_getCallType();
        if($type != 'uploads' && $type != 'favorites' && $type != 'subscriptions'){
            array_merge($params, array("time" => $this->_getTime()) );
        }*/

        return $params;
    }

    /**
    * Makes an HTTP request.
    *
    * @param String $url the URL to make the request to
    * @param Array $params the parameters to use for the POST body
    * @param CurlHandler $ch optional initialized curl handle
    * @return String the response text
    */
    protected function makeRequest($url, $params = array(), $ch = null) {
        if (!$ch) {
            $ch = curl_init();
        }

        $opts = self::$CURL_OPTS;
        //$opts[CURLOPT_POSTFIELDS] = http_build_query($params, null, '&');
        $opts[CURLOPT_URL] = $url;
        curl_setopt_array($ch, $opts);
        $result = curl_exec($ch);
        if ($result === false) {
            
        }
        $this->last_http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $this->last_api_call = $url;
        print_r($url);
        curl_close($ch);
        return $result;
    }

}



?>
