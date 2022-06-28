<?php
	$countAll = 35;
	$limit = 10;
	
	$maxPage = intval($countAll/$limit);
	echo $maxPage . "<br>";
	if(!is_int($countAll/$limit))
		$maxPage++;
	echo $maxPage;
?>