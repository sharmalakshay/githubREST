<?php

$context = stream_context_create(
    array(
        "http" => array(
            "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36"
        )
    )
);
$mailbody ="";

$allusers = array();

$totalcommitsbyusers = array();
$totalcommitsbytargetusers = array();

$repos = explode(',', $_GET['repos']);

echo "<b>Submitted repos are:</b><br>";
$mailbody .= "<b>Submitted repos are:</b><br>"

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
$mailbody .= "<br><hr><br><b>All users who contributed to any of the above in year 2018 are:</b><br><br>";
foreach($allusers as $username){
	echo $username,"<br>";
	$mailbody .= $username."<br>";
	/*$webpage = file_get_contents("https://github.com/search/?q=author%3A$username&type=Commits", false, $context);
	
	$halfpage = explode("search?q=author%3A$username&amp;type=Commits",$webpage);
	$halfhalf = explode("span",$halfpage[1]);
	$final = explode(">",$halfhalf[1]);
	$totalcommits = explode("<",$final[1]);
	//echo $totalcommits[0], "<br><br>";
	$totalcommitsbyusers[$username] = str_replace("K","000",$totalcommits[0]);
	*/
	//^^^^^^^^^^FOUND A BETTER METHOD ^^^^^^^^^^^^^^^^^^
	
	/*
	$jsonfortotalcommits = json_decode(file_get_contents("https://api.github.com/search/commits?q=author:$username", false, $context));
	$totalcommitsofuser[$username] = $jsonfortotalcommits['total_count'];
	*/
	//^^^^^^^^^^^^^^DID NOT WORK QUITE RIGHT^^^^^^^^^^^^^^
	$ch = curl_init(); //basically making a cURL object with all the options to hit the server
	curl_setopt($ch, CURLOPT_URL, "https://api.github.com/search/commits?q=author:$username");
	$curlheaders = [
		'Accept: application/vnd.github.cloak-preview+json',
		'User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36'
	];
	curl_setopt($ch, CURLOPT_HTTPHEADER, $curlheaders);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //return transfer as a string = YES
	$outputfromcurl = json_decode(curl_exec($ch),true);
	$totalcommitsbyusers[$username] = $outputfromcurl['total_count'];
	curl_close($ch);
}

echo "<br><hr><br><b>Most active to least active on Github among the above users:</b><br><br>";
$mailbody .= "<br><hr><br><b>Most active to least active on Github among the above users:</b><br><br>";
arsort($totalcommitsbyusers);
foreach($totalcommitsbyusers as $key => $value){
	echo "$key having $value commits<br>";
	$mailbody .= "$key having $value commits<br>";
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
$mailbody .= "<br><hr><br><b>Most to least active in submitted repos</b> (Only verified commits)<b>:</b><br><br>";
arsort($totalcommitsbytargetusers);
foreach($totalcommitsbytargetusers as $target => $score){
	echo "$target having $score commits<br>";
	$mailbody .= "$target having $score commits<br>";
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
$mailbody .= "<br><hr><br><b>Ranking based on languages known:</b><br><br>";
foreach($langcount as $u => $c){
	echo "$u = $c (";
	$mailbody .= "$u = $c (";
	foreach($langsknown[$u] as $l){
		echo "$l; ";
		$mailbody .= "$l; ";
	}
	echo ")<br>";
	$mailbody .= ")<br>";
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
echo "<br><hr><br><b>Weekly ranked commits of users on the submitted repos, for 2018</b><br><br>";
$mailbody .= "<br><hr><br><b>Weekly ranked commits of users on the submitted repos, for 2018</b><br><br>";
foreach($alltheweeks as $theweek => $data){
	echo "Week ", date('W',$theweek), ":<br>";
	$mailbody .= "Week ".date('W',$theweek).":<br>";
	arsort($data);
	foreach($data as $userr => $contri_score){
		echo "$userr commited $contri_score times<br>";
		$mailbody .= "$userr commited $contri_score times<br>";
	}
	echo "<br>";
	$mailbody .= "<br>";
}


$newarrayofweeks = array();

foreach($alltheweeks as $weekscalcname => $usersarray){
	foreach($usersarray as $usernamesforcalc => $userscoreforcalc){
		if(!isset($newarrayofweeks[$usernamesforcalc]['numofweeks'])) $newarrayofweeks[$usernamesforcalc]['numofweeks'] = 1;
		else $newarrayofweeks[$usernamesforcalc]['numofweeks'] +=1;
		
		if(!isset($newarrayofweeks[$usernamesforcalc]['numofcommits'])) $newarrayofweeks[$usernamesforcalc]['numofcommits'] = $userscoreforcalc;
		else $newarrayofweeks[$usernamesforcalc]['numofcommits'] += $userscoreforcalc;
	}
}

$totalweeklyaverage = array();

foreach($newarrayofweeks as $usernameforcal => $scoreforcal){
	$totalweeklyaverage[$usernameforcal] = round($scoreforcal['numofcommits']/$scoreforcal['numofweeks']);
}

arsort($totalweeklyaverage);

echo "<br><b>Weekly commit rate of users for submitted repos</b><br><br>";
$mailbody .= "<br><b>Weekly commit rate of users for submitted repos</b><br><br>";

foreach($totalweeklyaverage as $weekrateusername => $weekcommits){
	echo "$weekrateusername has an average of $weekcommits commits per week<br>";
	$mailbody .= "$weekrateusername has an average of $weekcommits commits per week<br>";
}


$totalcommitsbyusersbeafter2018 = array();

foreach($allusers as $username2){
	$ch2 = curl_init(); //basically making a cURL object with all the options to hit the server
	curl_setopt($ch2, CURLOPT_URL, "https://api.github.com/search/commits?q=author:$username2+committer-date:>2018-01-01");
	$curlheaders2 = [
		'Accept: application/vnd.github.cloak-preview+json',
		'User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36'
	];
	curl_setopt($ch2, CURLOPT_HTTPHEADER, $curlheaders2);
	curl_setopt($ch2, CURLOPT_RETURNTRANSFER, 1); //return transfer as a string = YES
	$outputfromcurl2 = json_decode(curl_exec($ch2),true);
	$totalcommitsbyusersbeafter2018[$username2] = $outputfromcurl2['total_count'];
	curl_close($ch2);
}

echo "<br><hr><br><b>Average commit rate of each user to any project, for 2018:</b><br><br>";
$mailbody .= "<br><hr><br><b>Average commit rate of each user to any project, for 2018:</b><br><br>";
arsort($totalcommitsbyusersbeafter2018);
foreach($totalcommitsbyusersbeafter2018 as $key22 => $value22){
	echo "$key22 having $value22 commits<br>";
	$mailbody .= "$key22 having $value22 commits<br>";
}


$queryforallrepos = "";
foreach ($allusers as $usertostring){
	$queryforallrepos .= "user:".$usertostring."+";	
}
$queryforallrepos .= "pushed:>2018-01-01";

$ch3 = curl_init();
curl_setopt($ch3, CURLOPT_URL, "https://api.github.com/search/repositories?q=$queryforallrepos");
$curlheaders3 = [
	'Accept: application/vnd.github.cloak-preview+json',
	'User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36'
];
curl_setopt($ch3, CURLOPT_HTTPHEADER, $curlheaders3);
curl_setopt($ch3, CURLOPT_RETURNTRANSFER, 1); //return transfer as a string = YES
$outputfromcurl3 = json_decode(curl_exec($ch3),true);
$allreposfromallusers = array();
foreach($outputfromcurl3['items'] as $repofromusers){
	if(!in_array($repofromusers['full_name'],$allreposfromallusers))array_push($allreposfromallusers,$repofromusers['full_name']);
}
$allcontributors = array();
foreach($allreposfromallusers as $onerepo){
	$contributorsinrepo = json_decode(file_get_contents("https://api.github.com/repos/$onerepo/contributors", false, $context), true);
	foreach($contributorsinrepo as $eachcontributor){
		if(!in_array($eachcontributor['login'],$allcontributors))array_push($allcontributors,$eachcontributor['login']);
	}
}

$totalnumberofcontributors = count($allcontributors);
echo "<br><hr><br><b>Total number of contributors who have contributed to any project our user has, in 2018:</b><br><br>";
$mailbody .= "<br><hr><br><b>Total number of contributors who have contributed to any project our user has, in 2018:</b><br><br>";
echo "<span style='font-size:200%'> $totalnumberofcontributors </span>";
$mailbody .= "<span style='font-size:200%'> $totalnumberofcontributors </span>";
curl_close($ch3);

if(isset($_POST['email'])){
	$to = $_POST['email'];
	$headers = "From: oh-please-do-not-reply@iamlakshay.com";
	if(mail($to,"IamLakshay: Your Github report",$mailbody,$headers))echo "<br><hr><b>REPORT EMAIL SENT</b>";
}
?>