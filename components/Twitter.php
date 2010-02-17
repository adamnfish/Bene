<?php

class Twitter
{
	private $username;
	private $password;
	private $useragent = '';
	private $additional_headers = false;
	private $logFile = false;
	private $format = "xml";
	
	private $url = "http://twitter.com/api/";
	
	public function __construct($username, $password)
	{
		$this->username = $username;
		$this->password = $password;
	}
	
	private function request($method, $data=false)
	{
		$url = $this->url . $method . '.' . $format;
		$c = curl_init();
		curl_setopt($c, CURLOPT_USERPWD, $this->username . ':' . $this->password);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($c, CURLOPT_USERAGENT, $this->useragent);
		if(false === $this->additional_headers)
		{
			curl_setopt($c, CURLOPT_HTTPHEADER, $this->additional_headers);
		}
		if(false !== $data)
		{
            curl_setopt ($c, CURLOPT_POST, true);
			curl_setopt ($c, CURLOPT_POSTFIELDS, $data);
		}
		if($this->logFile)
		{
			this->log($logFile);
		}
		$response = curl_exec($c);
		curl_close($c);
		
		return $response;
	}
	
	private function log($call)
	{
		$log = fopen($this->logFile, "a");
		fwrite($log, $call . "\n");
		fclose($log);
	}
	
	public function setLog($log)
	{
		$this->log = $log;
	}
	
	public function autoFollow()
	{
	
	}
	
	public function autoUnFollow()
	{
	
	}
}

<?php
// Please note, JSON support has not been fully implemented, and should not be used

class TwitterApiComponent extends Object {
	var $username = false;
	var $password = false;
	var $user_agent = '';
	var $type = 'xml';
	var $headers = array('Expect:', 'X-Twitter-Client: ','X-Twitter-Client-Version: ','X-Twitter-Client-URL: ');
	var $responseInfo = array();
	var $suppress_response_code = false;
	var $debug = false;
    var $URL = false;
    var $words = array();
    var $to_email = false;
    var $from_email = false;
    var $email_subject  = false;
    var $do_post = false;
    var $settings = false;
    var $test_xml_dir = false;

    function startup(&$controller) {
        $this->controller = & $controller;
        $this->SiteSettings = new SiteSettings();
        $this->settings = $this->SiteSettings->findAll('twitter', 'TwitterDirectMessage');
        $this->username = $this->settings['SiteSettings']['username'];
        $this->password = $this->settings['SiteSettings']['password'];
        $this->URL = $this->settings['SiteSettings']['twitter_url'];
        $this->to_email = $this->settings['SiteSettings']['admin_to_email'];
        $this->from_email = $this->settings['SiteSettings']['admin_from_email'];
        $this->email_subject = $this->settings['SiteSettings']['admin_email_subject'];
        $this->test_xml_dir = dirname(__FILE__).DS.'twitter_xml'.DS;
    }
    
    /**
     * changeAuthDetails
     * Changes the username and password to use when authenticating with twitter
     * This allows the API instance to administer other accunts as necessary
     * (added because the tests need this - adding these methods was easier than changing the class to allow defining auth details at instanciation
     * 
     * @param $username
     * @param $password
     */
    function changeAuthDetails($username, $password){
    	$this->username = $username;
    	$this->password = $password;
    }
    
    /**
     * resetAuthDetails
     * Resets the the authentication details for th API instance
     */
    function resetAuthDetails(){
        $this->username = $this->settings['SiteSettings']['username'];
        $this->password = $this->settings['SiteSettings']['password'];
    }

    /**
     * This method is to connect the Twitter API using curl functions
     * @param <type> $url
     * @param <type> $postargs
     * @return <type>
     */
	function doProcess($url,$postargs=false) {
        // if in test mode, check to see if there is a sample xml file that can be served up for this request, do nothing if there isn't
        if(defined('DEV_MODE')){//defined('TEST_ENABLED')) {
            $test_xml_file = $this->test_xml_dir.str_replace('/', '', $url);
            if(file_exists($test_xml_file)) {
                return $this->objectify(file_get_contents($test_xml_file));
            } else {
                echo "There is no test xml for the function $url\n";
                return false;
            }
        }
        $url = ( $this->suppress_response_code ) ? $this->URL.$url . '&suppress_response_code=true' : $this->URL.$url;
        $ch = curl_init($url);
      	if(false !== $postargs) {
            curl_setopt ($ch, CURLOPT_POST, true);
			curl_setopt ($ch, CURLOPT_POSTFIELDS, $postargs);
        }
		if($this->username !== false && $this->password !== false)
		curl_setopt($ch, CURLOPT_USERPWD, $this->username.':'.$this->password );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
//		curl_setopt($ch, CURLOPT_NOBODY, 0);
        if( $this->debug ) :
            curl_setopt($ch, CURLOPT_HEADER, true);
        else :
            curl_setopt($ch, CURLOPT_HEADER, false);
        endif;
        curl_setopt($ch, CURLOPT_USERAGENT, $this->user_agent);
        @curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
        $response = curl_exec($ch);
        $this->responseInfo=curl_getinfo($ch);
        curl_close($ch);
        $this->do_post = false;
        if( $this->debug ) {
            $debug = preg_split("#\n\s*\n|\r\n\s*\r\n#m", $response);
            echo'<pre>' . $debug[0] . '</pre>'; exit;
        }
        $result = false;
        if(intval($this->responseInfo['http_code']) == 200 || intval($this->responseInfo['http_code'] ) == 403) {
			$result = $response;
        }
        $return = $this->objectify($result);
        // put the full xml into the result too, could come in handy
        $return->original_response = $response;
        return $return;
	}

    /**
	 * Function to prepare data for return to client
	 * @access private
	 * @param string $data
	 */
	function objectify( $data ) {
        if( $this->type ==  'json' )
			return json_decode( $data );
		else if( $this->type == 'xml' ) {
			if( function_exists('simplexml_load_string') ) :
			    $obj = @simplexml_load_string( $data );
			endif;
			return $obj;
		}
		else
			return false;
	} 

	/**
	 * Send a status update to Twitter.
	 * @param string $status
	 * @return string|boolean
	 */
	function update( $status, $replying_to = false ) {
	    if( !in_array( $this->type, array( 'xml','json' ) ) )
	        return false;
        $request = '/statuses/update.' . $this->type;
		$postargs = array( 'status' => $status );
        if( $replying_to )
            $postargs['in_reply_to_status_id'] = (int) $replying_to;
		return $this->doProcess($request, $postargs);
	}

    /**
     * get replies from twitter
     * @param <type> $page
     * @param <type> $since
     * @param <type> $since_id
     * @return <type>
     */
	function getReplies( $page = false, $since = false, $since_id = false ) {
	    if( !in_array( $this->type, array( 'xml','json','rss','atom' ) ) )
	        return false;
	    $args = array();
	    if( $page )
	        $args = '&page='. (int) $page;
	    if( $since )
	        $args = '&since='. (string) $since;
	    if( $since_id )
	        $args = '&since_id'. (int) $since_id;
	    $qs = '';
	    $qs = ereg_replace("^&", "?", $args);
	    $request = '/statuses/replies.' . $this->type . $qs;
	    return $this->doProcess( $request );
	}

	/**
	 * Destroy a tweet
	 * @param integer $id Required.
	 * @return string
	 **/
	function deleteStatus( $screen_name )  {
        if( !in_array( $this->type, array( 'xml','json' ) ) )
	        return false;
        $request = '/statuses/destroy/' .$screen_name . '.' . $this->type;
        return $this->doProcess( $request, true );
    }
	/**
	 * Send an unauthenticated request to Twitter for the public timeline.
	 * Returns the last 20 updates by default
	 * @param boolean|integer $sinceid Returns only public statuses with an ID greater of $sinceid
	 * @return string
	 */
	function publicTimeline( $sinceid = false ) {
	    if( !in_array( $this->type, array( 'xml','json','rss','atom' ) ) )
	        return false;
        $qs='';
        if( $sinceid !== false )
            $qs = '?since_id=' . intval($sinceid);
        $request = '/statuses/public_timeline.' . $this->type . $qs;
		return $this->doProcess($request);
	}
	/**
	 * Send an authenticated request to Twitter for the timeline of authenticating user.
	 * Returns the last 20 updates by default
	 * @param boolean|integer $id Specifies the ID or screen name of the user for whom to return the friends_timeline. (set to false if you want to use authenticated user).
	 * @param boolean|integer $since Narrows the returned results to just those statuses created after the specified date.
	 * @deprecated integer $count. As of July 7 2008, Twitter has requested the limitation of the count keyword. Therefore, we deprecate
	 * @return string
	 */
	function userTimeline($id=false,$count=20,$since=false,$since_id=false,$page=false) {
	    if( !in_array( $this->type, array( 'xml','json','rss','atom' ) ) )
	        return false;
	    $args = array();
	    if( $id )
	        $args['id'] = $id;
	    if( $count )
	        $args['count'] = (int) $count;
	    if( $since )
	        $args['since'] = (string) $since;
	    if( $since_id )
	        $args['since_id'] = (int) $since_id;
	    if( $page )
	        $args['page'] = (int) $page;
	    $qs = '';
	    if( !empty( $args ) )
	        $qs = $this->_glue( $args );
        if( $id === false )
            $request = '/statuses/user_timeline.' . $this->type . $qs;
        else
            $request = '/statuses/user_timeline/' . rawurlencode($id) . '.' . $this->type . $qs;
		return $this->doProcess($request);
	}
	/**
	 * Returns a single status, specified by the id parameter below.  The status's author will be returned inline.
	 * @param integer $id The id number of the tweet to be returned.
	 * @return string
	 */
	function showStatus( $screen_name ) 	{
	    if( !in_array( $this->type, array( 'xml','json' ) ) )
	        return false;
       $request = '/users/show/'.$screen_name . '.' . $this->type;
		return $this->doProcess($request);
    }
    /**
	 * Returns the authenticating user's friends, each with current status inline.  It's also possible to request another user's friends list via the id parameter below.
	 * @param integer|string $id Optional. The user ID or name of the Twitter user to query.
	 * @param integer $page Optional.
	 * @return string
	 */
	function friends( $id = false, $page = false ) 	{
	    if( !in_array( $this->type, array( 'xml','json' ) ) )
	        return false;
        $args = array();
	    if( $id )
	        $args['id'] = $page;
	    if( $page )
	        $args['page'] = (int) $page;
	    $qs = '';
	    if( !empty( $args ) )
	        $qs = $this->_glue( $args );
	    $request = ( $id ) ? '/statuses/friends/' . $id . '.' . $this->type . $qs : '/statuses/friends.' . $this->type . $qs;
		return $this->doProcess($request);
	}
	/**
	 * Returns the authenticating user's followers, each with current status inline.
	 * @param integer $page Optional.
	 * @return string
	 */
	function followers( $page = false ) {
	    if( !in_array( $this->type, array( 'xml','json' ) ) )
	        return false;
        $request = '/statuses/followers.' . $this->type;
        if( $page )
            $request .= '?page=' . (int) $page;
		return $this->doProcess($request);
	}

    function notification($screen_name) {
         if( !in_array( $this->type, array( 'xml','json' ) ) )
	        return false;
        $request =  '/notifications/follow/' . $screen_name . '.' .$this->type;
		return $this->doProcess($request);
    }
    /**
     * Sends a request to follow a user specified by ID
     * @param <type> $screen_name
     * @param <type> $notifications
     * @return <type>
     */
	function followUser( $screen_name, $notifications = false ) {
        if( !in_array( $this->type, array( 'xml','json' ) ) )
	        return false;
	    if($notifications)
	    {
			$this->notification($screen_name);
	    }
        $request = '/friendships/create/' . $screen_name . '.' . $this->type;
        $request.= '?follow=true';
        return $this->doProcess($request, true);
	}
	/**
	 * Unfollows a user
	 * @param integer|string $id the username or ID of a person you want to unfollow
	 * @return string
	 */
	function leaveUser( $screen_name ) 	{
	    if( !in_array( $this->type, array( 'xml','json' ) ) )
	        return false;
		 $request = '/friendships/destroy/' .$screen_name . '.' . $this->type;
		return $this->doProcess($request, true);
	}
	/**
     * Blocks a user
	 * @param integer|string $id the username or ID of a person you want to block
	 * @return string
	 */
	function blockUser( $screen_name ) 	{
	    if( !in_array( $this->type, array( 'xml','json' ) ) )
	        return false;
		$request = '/blocks/create/' . $screen_name . '.' . $this->type;
		return $this->doProcess($request);
	}
	/**
	 * Unblocks a user
	 * @param integer|string $id the username or ID of a person you want to unblock
	 * @return string
	 */
	function unblockUser($screen_name) 	{
	    if( !in_array( $this->type, array( 'xml','json' ) ) )
	        return false;
		$request = '/blocks/destroy/' . $screen_name . '.' . $this->type;
		return $this->doProcess($request);
	}
	/**
	 * Retrieves favorited tweets
	 * @param integer|string $id Required. The username or ID of the user to be fetched
	 * @param integer $page Optional. Tweets are returned in 20 tweet blocks. This int refers to the page/block
	 * @return string
	 */
	function getFavorites( $id, $page=false ) {
	    if( !in_array( $this->type, array( 'xml','json','rss','atom' ) ) )
	        return false;
		if( $page != false )
			$qs = '?page=' . $page;
		$request = '/favorites.' . $this->type . $qs;
		return $this->doProcess($request);
	}
	/**
	 * Favorites a tweet
	 * @param integer $id Required. The ID number of a tweet to be added to the authenticated user favorites
	 * @return string
	 */
	function makeFavorite( $id ) {
	    if( !in_array( $this->type, array( 'xml','json' ) ) )
	        return false;
		$request = '/favorites/create/' . $id . '.' . $this->type;
		return $this->doProcess($request);
	}

	/**
	 * Unfavorites a tweet
	 * @param integer $id Required. The ID number of a tweet to be removed to the authenticated user favorites
	 * @return string
	 */
	function removeFavorite( $id ) 	{
	    if( !in_array( $this->type, array( 'xml','json' ) ) )
	        return false;
		$request = '/favorites/destroy/' . $id . '.' . $this->type;
		return $this->doProcess($request);
	}
	/**
	 * Checks to see if a friendship already exists
	 * @param string|integer $user_a Required. The username or ID of a Twitter user
	 * @param string|integer $user_b Required. The username or ID of a Twitter user
	 * @return string
	 */
	function isFriend($screen_name) {
	    if( !in_array( $this->type, array( 'xml','json' ) ) )
	        return false;
        $request = '/statuses/friends.'.$this->type;
        $qs  = '?screen_name='.$screen_name;
        $request.=$qs;
		return $this->doProcess($request);
	}

    function friendshipExists($screen_name) {
        if( !in_array( $this->type, array( 'xml','json' ) ) )
	        return false;
        $request = '/friendships/exists.'.$this->type;
        $qs  = '?user_a='.  rawurlencode($this->username);
        $qs.='&user_b='.rawurlencode($screen_name);
        $request.=$qs;
		return $this->doProcess($request);
    }
	/**
	 * Returns a list of IDs of all friends for the specified user
	 * @param integer $id Required. User ID to request list of friend IDs for
	 * return string
	 */
	function socialGraphFollowing( $screen_name = false) {
	    if( !in_array( $this->type, array( 'xml','json' ) ) )
	        return false;
	    $request = '/friends/ids';
	    if( $screen_name )
	        $request .= '/' . $screen_name . '.' . $this->type;
	    return $this->doProcess($request);
	}
	/**
	 * Returns a list of IDs of all followers for the specified user
	 * @param integer $id Required. User ID to request list of friend IDs for
	 * return string
	 */
	function socialGraphFollowedBy( $screen_name = false ) 	{
	    if( !in_array( $this->type, array( 'xml','json' ) ) )
	        return false;
	    $request = '/followers/ids';
	    if( $screen_name )
	        $request .= '/' . $screen_name . '.' . $this->type;
	    return $this->doProcess($request);
	}
	/**
     *  Shows the user details
     * @param <type> $screen_name
     * @return <type> 
     */
    function showUser( $screen_name) {
	    if( !in_array( $this->type, array( 'xml','json' ) ) )
	        return false;
          if ( $screen_name ) {
	        $qs = '?screen_name=' . (string) $screen_name;
          }
         $request = '/users/show/' . $qs . $this->type;
    	return $this->doProcess($request);
	}
    /**
     * @param <type> $since
     * @param <type> $count
     * @param <type> $since_id
     * @param <type> $page
     * @return <type>
     */
	function directMessages( $since = false, $count = null, $since_id = false, $page = false ) {
	    if( !in_array( $this->type, array( 'xml','json','rss','atom' ) ) )
	        return false;
        $qs='?';
        $qsparams = '';
        if( $since !== false )
            $qsparams .= '&since='.rawurlencode($since);
        if( $since_id )
            $qsparams .= '&since_id='.$since_id;
        if( $page )
            $qsparams .= '&page=' . (int) $page;
        if( $count)
            $qsparams .= '&count = ' . (int) $count;
         $qsparams = ereg_replace('^&', '?', $qsparams);
        $request = '/direct_messages.' . $this->type . $qsparams;
		return $this->doProcess($request);
	}
	/**
     * @param <type> $since
     * @param <type> $since_id
     * @param <type> $page
     * @return <type>
     */
	function sentDirectMessage( $since = false, $since_id = false, $page = false ) 	{
	    if( !in_array( $this->type, array( 'xml','json' ) ) )
	        return false;
	    $qs = '?';
	    $qsparams = array();
	    if( $since !== false )
            $qsparams['since'] = rawurlencode($since);
        if( $since_id )
            $qsparams['since_id'] = (int) $since_id;
        if( $page )
            $qsparams['page'] = (int) $page;
        $request = '/direct_messages/sent.' . $this->type . implode( '&', $qsparams );
        return $this->doProcess($request);
	}
    /**
     * @param <type> $user
     * @param <type> $text
     * @return <type>
     */
    function sendDirectMessage($screen_name, $text ) {
        if(!in_array($this->type, array('xml','json'))) {
            return false;
        }
        if(defined('DEV_MODE')) {
            return time();
        } else {
            $request = '/direct_messages/new.' . $this->type;
            $postargs = 'user=' . rawurlencode($screen_name) . '&text=' . rawurlencode($text);
            $result = $this->doProcess($request, $postargs);
            if(isset($result->id)) {
                return (string)$result->id;
            } else {
                return false;
            }
        }
    }
	/**
	 * Deletes a direct message
	 * @param integer $id Required
	 * @return string
	 */
	function deleteDirectMessage( $id ) {
	    if( !in_array( $this->type, array( 'xml','json' ) ) )
	        return false;
	    $request = '/direct_messages/destroy/' . (int) $id . '.' . $this->type;
	    return  $this->doProcess($request, true);
	}
	/**
	 * Updates delivery device
	 * @param string $device Required. Must be of type 'im', 'sms' or 'none'
	 * @return string
	 */
	function updateDevice( $device ) {
	    if( !in_array( $this->type, array( 'xml','json' ) ) )
	        return false;
		if( !in_array( $device, array('im','sms','none') ) )
			return false;
		$qs = '?device=' . $device;
		$request = '/account/update_delivery_device.' . $this->type . $qs;
		return $this->doProcess($request);
	}
	/**
	 * @param binary Required. Use your script to pass a binary image (GIF, JPG, PNG <700kb) to update Twitter profile avatar
	 * @return string
	 */
	function updateAvatar( $file ) 	{
	    if( !in_array( $this->type, array( 'xml','json' ) ) )
	        return false;
	    // Adding @ ensures the POST will be raw multipart data encoded. This MUST be a file, not a URL. Handle it outside of the class.
	    $postdata = array( 'image' => "@$file");
	    $request = '/account/update_profile_image.' . $this->type;
	    return $this->doProcess($request, $postdata);
	}
	/**
	 * @param binary Required. Use your script to pass a binary image (GIF, JPG, PNG <800kb) to update Twitter profile avatar. Images over 2048px wide will be scaled down
	 * @return string
	 */
	function updateBackground( $file ) 	{
	    if( !in_array( $this->type, array( 'xml','json' ) ) )
	        return false;
	    // Adding @ ensures the POST will be raw multipart data encoded. This MUST be a file, not a URL. Handle it outside of the class.
	    $postdata = array( 'image' => "@$file");
	    $request = '/account/update_profile_background_image.' . $this->type;
	    return $this->doProcess($request, $postdata );
	}
	/**
	 * @param array Requires. Pass an array of all optional members: name, email, url, location, or description. Email address must be valid if passed. Refer to Twitter RESTful API instructions on length allowed for other members
	 * @return string
	 */
	function updateProfile( $fields = array() ) {
	    if( !in_array( $this->type, array( 'xml','json' ) ) )
	        return false;
	    $postdata = array();
	    foreach( $fields as $pk => $pv ) :
	        switch( $pk )  {
	            case 'name' :
	                $postdata[$pk] = (string) substr( $pv, 0, 20 );
	                break;
	            case 'email' :
	                if( preg_match( '/\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b/i', $pv ) )
	                    $postdata[$pk] = (string) $pv;
	                break;
	            case 'url' :
	                $postdata[$pk] = (string) substr( $pv, 0, 100 );
	                break;
	            case 'location' :
	                $postdata[$pk] = (string) substr( $pv, 0, 30 );
	                break;
	            case 'description' :
	                $postdata[$pk] = (string) substr( $pv, 0, 160 );
	                break;
                case 'protected' :
	                $postdata[$pk] = (string) substr( $pv, 0, 1);
	                break;
	            default :
	                break;
	        }
	    endforeach;
	    $request = '/account/update_profile.' . $this->type;
	    return $this->doProcess($request, $postdata);
	}
	/**
	 * Pass an array of values to Twitter to update Twitter profile colors
	 * @param array Required. All array members are optional. Optional color fields are: profile_background_color, profile_text_color, profile_link_color, profile_sidebar_fill_color, profile_sidebar_border_color
	 * @return string
	 */
	function updateColors( $colors = array() ) 	{
	    if( !in_array( $this->type, array( 'xml','json' ) ) )
	        return false;
	    $postdata = array();
	    foreach( $colors as $ck => $cv ) :
	        if( preg_match('/^(?:(?:[a-f\d]{3}){1,2})$/i', $hex) ) :
                $postdata[$ck] = (string) $cv;
            endif;
	    endforeach;
		$request = '/account/update_profile_colors.' . $this->type;
	    return $this->doProcess($request, $postdata);
	}
	/**
	 * Rate Limit API Call. Sometimes Twitter needs to degrade. Use this non-ratelimited API call to work your logic out
	 * @return integer|boolean
	 */
	function ratelimit() {
	    if( !in_array( $this->type, array( 'xml','json' ) ) )
	        return false;
		$request = '/account/rate_limit_status.' . $this->type;
		return $this->doProcess($request);
	}
	/**
	 * Rate Limit statuses (extended). Provides helper data like remaining-hits, hourly limit, reset time and reset time in seconds
	 * @deprecated
 	 */
	function ratelimit_status() {
		return $this->ratelimit();
	}
	/**
	 * Detects if Twitter is up or down. Chances are, it will be down. ;-) Here's a hint - display CPM ads whenever Twitter is down
	 * @return boolean
	 */
	function twitterStatus() {
	    if( !in_array( $this->type, array( 'xml','json' ) ) )
	        return false;
		$request = '/help/test.' . $this->type;
        $details = $this->doProcess($request);
        $arr = (array) $details;
        if($arr[0]=='true') {
                return true;
        }
        return false;
		
	}
	/**
	 * Updates Geo location
	 * @deprecated
	 * @param string $location Required.
	 * @return string
	 */
	function updateLocation( $location ) {
		return $this->updateProfile( array( 'location' => $location ) );
	}
	/**
	 * Send an authenticated request to Twitter for the timeline of authenticating users friends.
	 * Returns the last 20 updates by default
	 * @deprecated true
	 * @param boolean|integer $id Specifies the ID or screen name of the user for whom to return the friends_timeline. (set to false if you want to use authenticated user).
	 * @param boolean|integer $since Narrows the returned results to just those statuses created after the specified date.
	 * @return string
	 */
	function friendsTimeline( $id = false, $since = false, $since_id = false, $count = 20, $page = false ) {
		return $this->userTimeline( $id, $count, $since, $since_id, $page );
	}
	

    // ************   Utility Functions *************** //
      /**
     * This method is for Interpreting Preferences
     */
    function filterMessage($message) {
        $message = html_entity_decode($message);
        //$allowed = "/[^a-z0-9\\040\\,\\s]/i";
        //$message = preg_replace($allowed,"",$message);
        $this->words = preg_split("/[\,]+/", $message);
        if(strlen($message) > '160') {
            return false;
        }
        if(count($this->words) == 4) {
            $station1=$this->parseStation(0);
            $station2=$this->parseStation(1);
            $time1=(int) $this->parseTime(2);
            $time2=(int) $this->parseTime(3);
            if( $time2 <  $time1) {
                return false;
            }
            if( $station1 &&  $station2 && $time1 && $time2) {
                $message = implode(',', $this->words);
                return array($message, $this->words);
            } else {
                return false;
            }
        }
        return false;
    }
    /**
     *
     * @param <type> $key
     * @return <type>
     */
    function parseTime($key=2 ) {
            $allowed = "/[^0-9]/i";
            $str = preg_replace($allowed,"",$this->words[$key]);
             if(strlen($str) > 4) {
                 return false;
             } else {
                    $informat = '%H%M';
                    $outformat =  '%H%M';
                    $ftime = strptime($str,$informat);
                    $time = mktime($ftime['tm_hour'], $ftime['tm_min'],  0, 0 , 0,  0 );
                    $time =  strftime($outformat , $time );
                    if($time == '0000') {
                        return false;
                    }
                    $this->words[$key] =trim($time);
                    return true;
             }
    }
    /**
     *
     * @param <type> $key
     * @return <type>
     */
    function parseStation($key=0) {
       $this->words[$key] =  trim($this->words[$key]);
       if(strlen($this->words[$key]) > '30') {
             return false;
       }
       $str  = $this->words[$key];
       if (ereg('[a-zA-Z]', $str)) {
             return true;
         } else {
         return false;
         }
    }

    function mailToAdmin($screen_name, $date, $body) {
        $content = "Dear Admin, \r\n User $screen_name is having problems setting preference. Please correct at http://www.twitter.com/. Message copy as follows:  Date: $date \r\n Direct Message: \r\n $body \r\n FCC Twitter Mailer.";
        mail($this->to_email, $this->email_subject, $content, "From: $this->from_email\r\n", "-f$this->from_email");
    }
    
	/* new function for Autofollow/ Autounfollow */
	
	function friendIds($user=false)
	{
		if( !in_array( $this->type, array( 'xml','json' ) ) )
		{
			return false;
		}
		if(false === $user)
		{
			$user = $this->username;
		}
		$request = '/friends/ids.' . $this->type . "?user=" . rawurlencode($user);
		$result = get_object_vars($this->doProcess($request));
		return $result["id"];
	}
	
	function followerIds($user=false)
	{
		if( !in_array( $this->type, array( 'xml','json' ) ) )
		{
			return false;
		}
		if(false === $user)
		{
			$user = $this->username;
		}
		$request = '/followers/ids.' . $this->type . "?user=" . rawurlencode($user);
		$result = get_object_vars($this->doProcess($request));
		return $result["id"];
	}
	
	// returns array of people following the supplied user (or default) that aren't being followed by user
	function followersNotFollowing($user=false)
	{
		$friends = $this->friendIds($user);
		$followers = $this->followerIds($user);
		
		return array_diff($followers, $friends);
	}
	
	// returns an array of people user is following that aren't following user
	function friendsNotFriending($user=false)
	{
		$friends = $this->friendIds($user);
		$followers = $this->followerIds($user);
		
		return array_diff($friends, $followers);
	}
	
	function autoFollow()
	{
		$toFollow = $this->followersNotFollowing();
		$newFollowers = array();
		foreach($toFollow as $newFollowerId)
		{
			$newFollower = $this->followUser($newFollowerId);
			if(false == isset($newFollower->error))
			{
				$newFollowers[] = $newFollower;
			}
		}
		return $newFollowers;
	}
	
	function autoUnfollow()
	{
		$toUnfollow = $this->friendsNotFriending();
		$oldFollowers = array();
		foreach($toUnfollow as $oldFollowerId)
		{
			$oldFollower = $this->leaveUser($oldFollowerId);
			if(false == isset($oldFollower->error))
			{
				$oldFollowers[] = $oldFollower;
			}
		}
		return $oldFollowers;
	}
}
?>

?>

