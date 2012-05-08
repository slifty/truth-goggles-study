<?PHP
	function get_final_url( $url, $timeout = 5 ) {
	    $url = str_replace( "&amp;", "&", urldecode(trim($url)) );

	    $cookie = tempnam ("/tmp", "CURLCOOKIE");
	    $ch = curl_init();
	    curl_setopt( $ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1" );
	    curl_setopt( $ch, CURLOPT_URL, $url );
	    curl_setopt( $ch, CURLOPT_COOKIEJAR, $cookie );
	    curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
	    curl_setopt( $ch, CURLOPT_ENCODING, "" );
	    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
	    curl_setopt( $ch, CURLOPT_AUTOREFERER, true );
	    curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, $timeout );
	    curl_setopt( $ch, CURLOPT_TIMEOUT, $timeout );
	    curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
	    $content = curl_exec( $ch );
	    $response = curl_getinfo( $ch );
	    curl_close ( $ch );

	    if ($response['http_code'] == 301 || $response['http_code'] == 302)
	    {
	        ini_set("user_agent", "Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1");
	        $headers = get_headers($response['url']);

	        $location = "";
	        foreach( $headers as $value )
	        {
	            if ( substr( strtolower($value), 0, 9 ) == "location:" )
	                return get_final_url( trim( substr( $value, 9, strlen($value) ) ) );
	        }
	    }

	    if (    preg_match("/window\.location\.replace\('(.*)'\)/i", $content, $value) ||
	            preg_match("/window\.location\=\"(.*)\"/i", $content, $value)
	    )
	    {
	        return get_final_url ( $value[1] );
	    }
	    else
	    {
	        return $response['url'];
	    }
	}

	set_include_path($_SERVER['DOCUMENT_ROOT']);
	require_once("models/DBConn.php");
	require_once("models/Article.php");
	require_once("models/Claim.php");

	// Clear old data
	echo("Clearing old data\n");
	$mysqli = DBConn::connect();
	$mysqli->query("delete from articles");
	$mysqli->query("delete from claims");

	// Load in the articles in the articles directory
	echo("Loading articles\n");
	$article_directory_path = "articles/";
	$article_directory = is_dir($article_directory_path)?opendir($article_directory_path):null;

	if($article_directory == null)
		echo("No Article Directory Found!\n");
	else {
		while (($article_file = readdir($article_directory)) !== false) {
			$article_path = $article_directory_path.$article_file;
			if(!is_file($article_path)) continue;
		
			$article_handle = fopen($article_directory_path.$article_file, 'r');
			$article_content = fread($article_handle, filesize($article_path));
		
			$article = new Article();
			$article->setContent($article_content);
			$article->save();
		}
	}

	// Store the claims that exist in those articles
	echo("Identifying claims\n");
	$articles = Article::getAllObjects();
	global $TRUTH_GOGGLES_API;
	$url = get_final_url($TRUTH_GOGGLES_API."snippets");
	
	foreach($articles as $article) {
		$content = $article->getContent();
		
		$fields = array('context'=>urlencode($content));
		$fields_string = "";
	
		foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
		rtrim($fields_string,'&');
	
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_POST,count($fields));
		curl_setopt($ch,CURLOPT_POSTFIELDS,$fields_string);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		$result = json_decode(curl_exec($ch));
		curl_close($ch);
		
		foreach($result->snippets as $snippet) {
			$claim_content = $snippet->claim->content;
			if(Claim::getObjectByContent($claim_content) == null) {
				$claim = new Claim();
				$claim->setContent($claim_content);
				$claim->save();
			}
		}
	}

	echo("Rake complete!\n");
?>