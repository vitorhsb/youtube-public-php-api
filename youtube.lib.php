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
    const VERSION = '0.1.2';
    const LAST_UPDATE = 'JULY 20, 2010';

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
        'search'        => 'http://gdata.youtube.com/feeds/api/videos',
        'videos'        => 'http://gdata.youtube.com/feeds/api/videos/'
    );
    
    protected static $XMLNS_MAP = array(
        "xmlns"         => "http://www.w3.org/2005/Atom",
        "media"         => "http://search.yahoo.com/mrss/",
        "openSearch"    => "http://a9.com/-/spec/opensearchrss/1.0/",
        "gd"            => "http://schemas.google.com/g/2005",
        "yt"            => "http://gdata.youtube.com/schemas/2007"
    );


    protected static $AVAILABLE_TYPES = array(
        'uploads', 'favorites', 'subscriptions', 'standardfeed', 'playlist', 'search', 'singlevideo'
    );

    protected static $AVAILABLE_STANDARD_FEEDS = array(
        'top_rated', 'top_favorites', 'most_viewed', 'most_popular', 'most_recent', 'most_discussed', 'most_responded', 'recently_featured', 'watch_on_mobile'
    );

    protected static $AVAILABLE_FORMATS = array(
        'mpeg', 'h263', 'flash'
    );

    protected static $AVAILABLE_TIME_FORMATS = array(
        'today', 'this_week', 'this_month', 'all_time'
    );

    protected static $AVAILABLE_ORDERBY_FORMATS = array(
        'relevance', 'published', 'viewCount', 'rating'
    );

    protected static $JSON_ALT_FLAG = "json";

    protected static $EXTRA_API_PARAMETERS = array(
        'v' => 2
    );

    protected static $NUM_MAX_VIDEOS = 100;

    /**
    * The Youtube Application Options.
    */
    private $options = array(
        'type'          => 'uploads',
        'limit'         => 5,
        'user'          => '',
        'search'        => '',
        'feed'          => '',
        'playlist'      => '',
        'format'        => 'flash',
        'time'          => 'all_time',
        'orderby'       => 'published',
        'videoid'       => ''
    );

    /**
    * The Youtube Application Options.
    */
    private $embed_options = array(
        'width'         => 300,
        'height'        => 240,
        'related'       => true,
        'autoplay'      => false,
        'loop'          => false,
        'keyboard'      => true,
        'genie'         => false,
        'border'        => false,
        'start'         => 0,
        'fullscreen'    => true,
        'highdef'       => true
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
            return $this->_options_user($args[0]);
        }

    }

    /**
    * Set the Youtube application options.
    *
    * @param String $user the Youtube User ID
    */
    public function setOptions($options) {
        if(!is_array($options)){
            return $this;
        }

        foreach ($options as $key => $value) {
            if(array_key_exists($key, $this->options) && isset($value) ){
                call_user_func(array($this, '_options_'.$key), $value);
            }
        }
        return $this;
    }

    public function getOptions() {
        return $this->options;
    }

    public function printOptionsArray(){
        print_r($this->options);
    }

    protected function _options_user($user = null){
        return isset($user) ? ($this->options["user"] = $user) : $this->options["user"];
    }

    protected function _options_limit($limit = null){
        if(isset($limit)){
            $limit = min(array($limit, 100));
            $this->options["limit"] = $limit;
        }
        return $this->options["limit"];
    }
    
    protected function _options_type($type = null){
        if(isset($type) && $this->isValidType($type)){
            $this->options["type"] = $type;
        }
        return $this->options["type"];
    }

    protected function _options_search($search = null){
        if(isset($search)){
            $this->options["search"] = $search;
        }
        return $this->options["search"];
    }

    protected function _options_feed($feed = null){
        if(isset($feed) && $this->isValidFeed($feed)){
            $this->options["feed"] = $feed;
        }
        return $this->options["feed"];
    }

    protected function _options_playlist($playlist = null){
        if(isset($playlist)){
            $this->options["playlist"] = $playlist;
        }
        return $this->options["playlist"];
    }

    protected function _options_format($format = null){
        if(isset($format) && $this->isValidFormat($format)){
            $this->options["format"] = $format;
        }
        return $this->options["format"];
    }

    protected function _options_time($time = null){
        if(isset($time) && $this->isValidTime($time)){
            $this->options["time"] = $time;
        }
        return $this->options["time"];
    }

    protected function _options_orderby($order = null){
        if(isset($order) && $this->isValidOrderBy($order)){
            $this->options["orderby"] = $order;
        }
        return $this->options["orderby"];
    }

    protected function _options_videoid($video_id = null){
        if(isset($video_id) && $video_id != ""){
            $this->options["videoid"] = $video_id;
        }
        return $this->options["videoid"];
    }

    /**
     * Checks if the request type is valid
     *
     * @return Boolean
     */
    protected function isValidType($type = null){
        $type = !is_null($type) ? $type : $this->_options_type();
        return in_array($type, self::$AVAILABLE_TYPES);
    }

    /**
     * Checks if the request type is valid
     *
     * @return Boolean
     */
    protected function isValidFeed($feed = null){
        $feed = isset($feed) ? $feed : $this->_options_standardfeed();
        return in_array($feed, self::$AVAILABLE_STANDARD_FEEDS);
    }

    /**
     * Checks if the request type is valid
     *
     * @return Boolean
     */
    protected function isValidFormat($format = null){
        $format = isset($format) ? $format : $this->_options_format();
        return in_array($format, self::$AVAILABLE_FORMATS);
    }

    /**
     * Checks if the request type is valid
     *
     * @return Boolean
     */
    protected function isValidTime($time = null){
        $time = isset($time) ? $time : $this->_options_time();
        return in_array($time, self::$AVAILABLE_TIME_FORMATS);
    }

    /**
     * Checks if the request type is valid
     *
     * @return Boolean
     */
    protected function isValidOrderBy($order = null){
        $order = isset($order) ? $order : $this->_options_orderby();
        return in_array($order, self::$AVAILABLE_ORDERBY_FORMATS);
    }


    /**
    * Set the Youtube Embed application options.
    *
    * @param String $user the Youtube User ID
    */
    public function setEmbedOptions($options) {
        if(!is_array($options)){
            return $this;
        }

        foreach ($options as $key => $value) {
            if(array_key_exists($key, $this->embed_options) && isset($value) ){
                $this->embed_options[$key] = $value;
            }
        }
        return $this;
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
            $this->setOptions($options);
        }

        if($this->isValidType()){
            return call_user_func_array(array($this, "_".$this->_options_type()), array());
        } else {
            return null;
        }
    }

    public function getSingleVideo( $video_id ){
        if(!isset($video_id) || $video_id == ""){
            return null;
        }

        is_array($video_id) ? $this->setOptions($video_id) : $this->_options_videoid($video_id);
        $this->_options_type("singlevideo");
        
        $data = $this->_singlevideo();
        $videos = !is_null($data) && $data != '' ? $this->getVideosFromData($data) : array();
        return count($videos) > 0 ? $videos[0] : null;
    }

    public function getUserUploads( $options = array() ){

        is_array($options) ? $this->setOptions($options) : $this->_options_user($options);
        $this->_options_type("uploads");
        
        $data = $this->options['user'] != '' ? $this->_uploads() : null;
        $videos = !is_null($data) && $data != '' ? $this->getVideosFromData($data) : array();
        return $videos;
    }

    public function getUserFavorites( $options = array() ){
        is_array($options) ? $this->setOptions($options) : $this->_options_user($options);
        $this->_options_type("favorites");

        $data = $this->options['user'] != '' ? $this->_favorites() : null;
        $videos = !is_null($data) && $data != '' ? $this->getVideosFromData($data) : array();
        return $videos;
    }

    public function getUserSubscriptions( $options = array() ){
        is_array($options) ? $this->setOptions($options) : $this->_options_user($options);
        $this->_options_type("subscriptions");

        $videos = $this->options['user'] != '' ? $this->_subscriptions() : array();
        return $videos;
    }

    public function getStandardFeed( $options = array() ){
        is_array($options) ? $this->setOptions($options) : $this->_options_feed($options);
        $this->_options_type("standardfeed");

        $data = $this->options['feed'] != '' ? $this->_standardfeed() : null;
        $videos = !is_null($data) && $data != '' ? $this->getVideosFromData($data) : array();
        return $videos;
    }

    public function searchForVideos( $options = array() ){
        is_array($options) ? $this->setOptions($options) : $this->_options_search($options);
        $this->_options_type("search");

        $data = $this->options['search'] != '' ? $this->_search() : null;
        $videos = !is_null($data) && $data != '' ? $this->getVideosFromData($data) : array();
        return $videos;
    }


    public function getVideosFromData($data){
        $videos = array();
        if(isset($data["feed"]["entry"])){
            foreach ($data["feed"]["entry"] as $i => $entry) {
                $video = $this->getVideoInfo($entry);
                $videos[] = $video;
            }
        } elseif ( isset($data["entry"]) ){
            $videos[] = $this->getVideoInfo($data["entry"]);
        }

        return $videos;
    }

    private function getVideoInfo($entry){
        $video = array(
            "title" => $entry["title"]['$t'],
            "link" => $entry["link"][0]["href"],
            "published" => $entry['published']['$t'],
            "author" => array(
                "name" => $entry['author'][0]['name']['$t'],
                "uri" => $entry['author'][0]['uri']['$t']
            ),
            "thumbnail" => array(
                "url" => $entry['media$group']['media$thumbnail'][0]["url"],
                "height" => $entry['media$group']['media$thumbnail'][0]["height"],
                "width" => $entry['media$group']['media$thumbnail'][0]["width"]
            )
        );

        if(isset($entry['media$group']['media$description'])){
            $video["description"] = $entry['media$group']['media$description']['$t'];
        }

        $videoFormats = array();
        foreach($entry['media$group']['media$content'] as $i => $c){
            $videoFormats[ $c['yt$format'] ] = $c['url'];
        }

        switch ($this->options['format']) {
            case "mpeg": $video['video'] = $videoFormats[6]; break;
            case "flash": $video['video'] = $videoFormats[5]; break;
            default: $video['video'] = $videoFormats[1]; break;
        }

        if(isset($entry['media$group']['yt$duration'])){
            $video["duration"] = $this->getFormatedDuration($entry['media$group']['yt$duration']['seconds']);
        }
        if(isset($entry['yt$statistics'])){
            $video['views'] = $entry['yt$statistics']['viewCount'];
        }
        if(isset($entry['gd$rating'])){
            $video['rating'] = $entry['gd$rating']['average'];
        }
        return $video;
    }

    public function getEmbedHTML($video, $embed_options = array()){
        $this->setEmbedOptions($embed_options);
        
        $html = "";
        
        // A single video object
        if(is_array($video) && isset($video["video"])){
            $html = $this->_embedHTML($video);
        }
        // Array of videos
        elseif(is_array($video) && isset($video[0]["video"])) {
            foreach ($video as $i => $v) {
                $html .= $this->_embedHTML($v);
            }
        }

        return $html;
    }


    /**
     *
     *
     */
    protected function _singlevideo(){
        $path = self::$URL_MAP['videos'].$this->options['videoid'];
        $data = $this->requestData($path);
        return $data;
    }

    protected function _uploads(){
        $path = self::$URL_MAP['users'].$this->options['user']."/"."uploads";
        $data = $this->requestData($path);
        return $data;
    }

    protected function _favorites(){
        $path = self::$URL_MAP['users'].$this->options['user']."/"."favorites";
        $data = $this->requestData($path);
        return $data;
    }

    protected function _subscriptions(){
        $path = self::$URL_MAP['users'].$this->options['user']."/"."subscriptions";
        $data = $this->requestData($path);
        
        $videos = array();
        if(isset($data["feed"]["entry"])){
            foreach ($data["feed"]["entry"] as $i => $entry) {

                if(isset ($entry["content"]["src"]) ){
                    $src = $entry["content"]["src"];
                    preg_match("#users/(.*?)/#", $src, $s);

                    if(!is_null($s) && is_array($s) && isset($s[1])){
                        $this->_options_user($s[1]);
                        $d = $this->_uploads();
                        $v = $this->getVideosFromData($d);
                        $videos = array_merge($videos, $v);
                    }
                }
            }
        } else {
            return $videos;
        }

        return count($videos) == 0 ? $videos : $this->sortVideosByPublishedTime($videos, $this->_options_limit());
    }

    protected function _standardfeed(){
        if($this->options['feed'] == ""){
            return array();
        }

        $path = self::$URL_MAP['standardfeeds'].$this->options['feed'];
        $data = $this->requestData($path);
        return $data;
    }

    protected function _search(){
        if($this->options['search'] == ""){
            return array();
        }

        $path = self::$URL_MAP['search'];
        $data = $this->requestData($path);
        return $data;
    }

    protected function _embedHTML($video){
        $url = $video["video"]."?".
            "rel=".($this->embed_options['related'] == true ? 1 : 0).
            "&autoplay=".($this->embed_options['autoplay'] == true ? 1 : 0).
            "&loop=".($this->embed_options['loop'] == true ? 1 : 0).
            "&disablekb".($this->embed_options['keyboard'] == true ? 1 : 0).
            "&egm=".($this->embed_options['genie'] == true ? 1 : 0).
            "&border=".($this->embed_options['border'] == true ? 1 : 0).
            "&start=".($this->embed_options['start']).
            "&fs=".($this->embed_options['fullscreen'] == true ? 1 : 0).
            "&hd=".($this->embed_options['highdef'] == true ? 1 : 0);

        $embed = '<object width="'.$this->embed_options['width'].'" height="'.$this->embed_options['height'].'">';
        $embed .= '<param name="movie" value="'.$video['video'].'"</param>';
        $embed .= '<param name="allowScriptAccess" value="always"></param>';
        $embed .= $this->embed_options['fullscreen'] == true ? '<param name="allowFullScreen" value="true"></param>' : '';
        
        $embed .= '<embed src="'.$url.'" type="application/x-shockwave-flash" ';
        $embed .= $this->embed_options['fullscreen'] == true ? ' allowfullscreen="true" ' : '';
        $embed .= ' allowscriptaccess="always" width="'.$this->embed_options['width'].'" height="'.$this->embed_options['height'].'" ';
        $embed .= '</embed>';
        $embed .= '</object>';

        return $embed;
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
        $format = $this->_options_format();
        $format = $format == "mpeg" ? 6 : ($format == "h263" ? 1 : 5);
        $type = $this->options['type'];

        $params = array(
            "alt" => self::$JSON_ALT_FLAG,
            "format" => $format,
            "orderby" => $this->_options_orderby(),
            "time" => $this->_options_time(),
            "max-results" => $this->_options_limit()
        );
        $params = array_merge($params, self::$EXTRA_API_PARAMETERS);

        switch ($type) {
            case "singlevideo":
                unset($params["max-results"]);
                unset($params["orderby"]);
                unset($params["time"]);
                break;
            case "subscriptions":
                unset($params["max-results"]);
                unset($params["format"]);
                unset($params["orderby"]);
                unset($params["time"]);
                break;
            case "standardfeed":
                unset($params["orderby"]);
                break;
            case "search":
                $params["q"] = $this->options["search"];
                break;
            default:
                break;
        }

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
        
        $this->last_http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        //echo "status: ".  $this->last_http_status. PHP_EOL;
        $this->last_api_call = $url;
        //echo($url.PHP_EOL);
        curl_close($ch);
        if ($result === false) {
            return null;
        }
        return $result;
    }

    protected function getFormatedDuration($duration){
        $hours = $minutes = $seconds = 0;
        $length = "";

        //Hours
        while($duration >= 3600){
            $hours++;
            $duration -= 3600;
        }

        //Minutes
        while($duration >= 60){
            $minutes++;
            $duration -= 60;
        }
        $minutes = $hours > 0 && $minutes < 10 ? "0".$minutes : $minutes;
        //Seconds
        $seconds = $duration < 10 ? "0".$duration : $duration;
        $length = $minutes.":".$seconds;
        if($hours>0){
            $hours = $hours < 10 ? "0".$hours : $hours;
            $length = $hours.":".$length;
        }
        return $length;
    }

    protected function sortVideosByPublishedTime($videos, $limit = self::NUM_MAX_VIDEOS){
        $vs = array();
        foreach($videos as $i => $v){
            $ts = strtotime(substr($v["published"], 0, 10).' '.substr($v["published"], 11, 8));
            $vs[ $ts ] = $v;
        }
        krsort($vs);
        $videos = array();
        $items = min(array($limit, count($vs)));
        foreach ($vs as $key => $v) {
            if($items <= 0){ break; }
            $videos[] = $v;
            $items--;
        }
        return $videos;
    }

}

?>
