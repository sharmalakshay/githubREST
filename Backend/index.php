<?php

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

$repos = explode(',', $_POST['repos']);

echo "<b>Submitted repos are:</b><br>";

$json = array();

foreach($repos as $repo){
	echo explode('/',$repo)[1],"    by    ",explode('/',$repo)[0],"<br>";
	
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

echo "<br><hr><br><b>All users who contributed to any of the above in year 2018 are:</b><br><br>";
foreach($allusers as $username){
	echo $username,"<br>";
	$webpage = file_get_contents("https://github.com/search/?q=author%3A$username&type=Commits", false, $context);
	
	$halfpage = explode("search?q=author%3A$username&amp;type=Commits",$webpage);
	$halfhalf = explode("span",$halfpage[1]);
	$final = explode(">",$halfhalf[1]);
	$totalcommits = explode("<",$final[1]);
	//echo $totalcommits[0], "<br><br>";
	$totalcommitsbyusers[$username] = str_replace("K","000",$totalcommits[0]);
}

echo "<br><hr><br><b>Most active to least active on Github among the above users:</b><br><br>";
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

echo "<br><hr><br><b>Most to least active in submitted repos</b> (Only verified commits)<b>:</b><br><br>";
arsort($totalcommitsbytargetusers);
foreach($totalcommitsbytargetusers as $target => $score){
	echo "$target having $score commits<br>";
}

$reposbyusers = array();
$langsknown = array();
$langcount = array();

foreach($allusers as $currentuser){
	$reposbyusers[$currentuser] = json_decode(file_get_contents("https://api.github.com/users/$currentuser/repos", false, $context),true);
	$langsknown[$currentuser] = array();
	$langcount[$currentuser] = 0;
}

foreach($reposbyusers as $usser => $reppos){
	foreach($reppos as $reppo){
		if(!in_array($reppo['language'],$langsknown[$usser])){
			array_push($langsknown[$usser],$reppo['language']);
			$langcount[$usser] += 1;
		}
	}
}

arsort($langcount);
echo "<br><hr><br><b>Ranking based on languages known:</b><br><br>";
foreach($langcount as $u => $c){
	echo "$u = $c (";
	foreach($langsknown[$u] as $l){
		echo "$l; ";
	}
	echo ")<br>";
}


$contributions = array();
$alltheweeks = array();

foreach($repos as $repo_contributions){
	$contributions[$repo_contributions] = json_decode(file_get_contents("https://api.github.com/repos/$repo_contributions/stats/contributors", false, $context),true);
	foreach($contributions[$repo_contributions] as $contributor){
		if(in_array($contributor['author']['login'],$allusers)){
			foreach($contributor['weeks'] as $week){
				if($week['c']>0 && $week['w']>1514764800){
					if(!isset($alltheweeks[$week['w']][$contributor['author']['login']])){
						$alltheweeks[$week['w']][$contributor['author']['login']] = $week['c'];
					}
					else{
						$alltheweeks[$week['w']][$contributor['author']['login']] += $week['c'];
					}
				}
			}
		}
	}
}

ksort($alltheweeks);
echo"<br><hr><br><b>Weekly commit rate of users for the submitted repos, for 2018</b><br><br>";
foreach($alltheweeks as $theweek => $data){
	echo "Year 2018, Week ", date('W',$theweek), "<br>";
	arsort($data);
	foreach($data as $userr => $contri_score){
		echo "$userr commited $contri_score times<br>";
	}
	echo "<br>";
}


$contributionstoall = array();
$alltheweekstoall = array();





?>