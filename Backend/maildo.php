<style>
body{
	background-picture: url('bkg.png');
	background-color: #151515;
	color: #eaeaea;
}
b{
	color: #b5e853;
}
a{
	text-decoration: none;
	color: green;
	font-size: 110%;
}
</style>

<?php
$to = $_POST['to'];
$subject = "Copy of github repositories report from lakshay";
$headers = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
$headers .= "From: oh-plese-do-no-reply@iamlakshay.com" . "\r\n";
$body = $_POST['body'];
if(mail($to, $subject, $body, $headers))echo "<br><br><b>Mail sent!</b><hr><br>";
else print_r(error_get_last());
echo "<a href='https://github.com/sharmalakshay/githubREST'>GO BACK TO MY GITHUB?</a><br><br>";
echo "<a href='https://sharmalakshay.github.io/githubREST/'>TRY THIS APPLICATION AGAIN?</a>";
?>