<?php

//because github does not want to give data to scripts, lets fool it ;)
$context = stream_context_create(
    array(
        "http" => array(
            "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36"
        )
    )
);

$allusers = array();

$repos = explode(',', $_POST['repos']); //all repos in array

echo "<b>Submitted repos are:</b><br>";

foreach($repos as $repo){
	echo explode('/',$repo)[1]," by ",explode('/',$repo)[0],"<br>";
	
	//$url0 = "https://api.github.com/repos/".trim($repo)."/contributors";
	
	$url = "https://api.github.com/repos/".trim($repo)."/commits";
	$json = json_decode(file_get_contents($url, false, $context),true);
	foreach($json as $commit){
		$year = substr($commit['commit']['author']['date'],0,4);
		if($year==2018){
			$user = $commit['author']['login'];
			if(!in_array($user,$allusers))array_push($allusers,$user);
		}
		else break;
	}
	//echo count($json);
}
echo "<br><b>All users who contributed to any of the above in year 2018 are:</b><br>";
foreach($allusers as $username)echo $username,"<br>";

//print_r($repos);

?>