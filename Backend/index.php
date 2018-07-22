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

$totalcommitsbyusers = array();
$totalcommitsbytargetusers = array();

$repos = explode(',', $_POST['repos']); //all repos in array

echo "<b>Submitted repos are:</b><br>";

$json = array();

foreach($repos as $repo){
	echo explode('/',$repo)[1]," by ",explode('/',$repo)[0],"<br>";
	
	//$url0 = "https://api.github.com/repos/".trim($repo)."/contributors";
	
	$url = "https://api.github.com/repos/".trim($repo)."/commits";
	$json[$repo] = json_decode(file_get_contents($url, false, $context),true);
	foreach($json[$repo] as $commit){
		$year = substr($commit['commit']['author']['date'],0,4);
		if($year==2018){
			$user = $commit['author']['login'];
			if(!in_array($user,$allusers))array_push($allusers,$user);
		}
		else break;
	}
}

echo "<br><b>All users who contributed to any of the above in year 2018 are:</b><br>";
foreach($allusers as $username){
	echo $username,"<br>";
	$url2 = "https://github.com/search/?q=author%3A$username&type=Commits";
	$webpage = file_get_contents($url2, false, $context);
	
	$halfpage = explode("search?q=author%3A$username&amp;type=Commits",$webpage);
	$halfhalf = explode("span",$halfpage[1]);
	$final = explode(">",$halfhalf[1]);
	$totalcommits = explode("<",$final[1]);
	//echo $totalcommits[0], "<br><br>";
	$totalcommitsbyusers[$username] = $totalcommits[0];
}

echo "<br><b>Most active to least active on Github among the above users:</b><br>";
arsort($totalcommitsbyusers);
foreach($totalcommitsbyusers as $key => $value){
	echo "$key having $value commits<br>";
}

foreach($allusers as $theuser){
	$totalcommitsbytargetusers[$theuser] = 0;
}

foreach($repos as $rep){	
	foreach($json[$rep] as $repojson => $repocommits){
		foreach($allusers as $targetuser){
			if($repocommits['author']['login']==$targetuser){
				$totalcommitsbytargetusers[$targetuser] += 1;
			}
		}
	}
}

echo "<br><b>Most to least active in submitted repos</b> (Only verified commits)<b>:</b><br>";
arsort($totalcommitsbytargetusers);
foreach($totalcommitsbytargetusers as $target => $score){
	echo "$target having $score commits<br>";
}
?>