<?php
set_time_limit(6000);
require_once 'simple_html_dom.php';
require_once 'dbconnect.php';

function getSkillID($name, $link) {
	$query = "SELECT `id` FROM `skills` WHERE `link` LIKE '$link'";
	$res = SqlQuery($query);
	if ($res == FALSE) {
		$insQry = "INSERT INTO skills (`name`, `link`) VALUES ('$name', '$link')";
		SqlQuery($insQry);
		return mysql_insert_id();
	} else {
		return $res[0]["id"];
	}
}

/*
$skillLink = 'Java';
$pageCount = 4;

$content = file_get_contents("https://www.freelancer.com/jobs/".$skillLink."/".($pageCount++)."/", false, null, -1);
//$content = file_get_contents("https://www.freelancer.com/jobs/Java/6/", false, null, -1);
	

 $dir = "d:\\";
 if ($myfile = fopen($dir."fl-$skillLink-".($pageCount-1).".txt", "w")) {// or die("Unable to open file!");
 	fwrite($myfile, $content);
 	fclose($myfile);
 }
*/

	$html = file_get_html('d:\\fl-Java-5.txt ');
	//$ret = $html->find('div[class=JobSearchCard-item]'); // div class="JobSearchCard-item "
	//echo $html->find('div[class=JobSearchCard-item]')->children(1);
	$projectsTag = $html->find("#project-list", 0);
	$cnt = 0;
	foreach($projectsTag->children() as $prj) {
		//$prj = $projectsTag->children(0);
		//echo $prj;
		//$prj = $projects[0];
		$heading = $prj->find('div[class=JobSearchCard-primary-heading]', 0);
	//	echo $heading.'\n';
		$anchor = $heading->find('a', 0);
		echo $anchor->href."  ".$cnt++;
		echo "\n";
	
		$content = file_get_html("https://www.freelancer.com".$anchor->href, false, null, -1);
		echo "href = ".$anchor->href."\n<br>";
		 $dir = "d:\\";
		 if ($myfile = fopen($dir."fl-prj-".$cnt.".txt", "w")) {// or die("Unable to open file!");
		 fwrite($myfile, $content);
		 fclose($myfile);
		 }
		$prjID = 0;
		$skills = array();
		$matches = array();
		
		foreach ($content->find('p[class=PageProjectViewLogout-detail-tags]') as $tag) {
			$inner = $tag->innertext;
			if (strpos($inner, 'Project ID') !== false && preg_match("/#(\d+)/", $inner, $matches) == 1) {
				$prjID = $matches[1];
			}
			if (strpos($inner, 'Skills') !== false) {
				$skillsTag = $tag->find('a');
				foreach ($skillsTag as $skill) {
					$href = $skill->href;
					$link = '';
					if (preg_match("/\/jobs\/([\w,-]+)\//", $href, $matches) == 1) {
						$link = $matches[1];
					}
					$name = $skill->innertext;
					$skillID = getSkillID($name, $link);
					array_push($skills, $skillID);
				}
			}
		}
		$prjTitle = $content->find("h1[class=PageProjectViewLogout-header-title]", 0)->innertext;
		echo $prjTitle."\n";
		
		if (count($content->find("p[class=PageProjectViewLogout-detail-paragraph]")) == 0) {
			contunue;
		} else {
			$prjDescr = $content->find("p[class=PageProjectViewLogout-detail-paragraph]", 0)->find('p', 0)->innertext;
			echo $prjDescr;
		}
		echo "Prj to save: [$prjID], '$prjTitle', '$prjDescr' <br>\n";
	}
	
/*
	$html = file_get_html('d:\fl-prj-4.txt');
	$prjID = 0;
	$skills = array();
	$matches = array();
	
	foreach ($html->find('p[class=PageProjectViewLogout-detail-tags]') as $tag) {
		$inner = $tag->innertext;
		if (strpos($inner, 'Project ID') !== false && preg_match("/#(\d+)/", $inner, $matches) == 1) {
			$prjID = $matches[1];
		}
		if (strpos($inner, 'Skills') !== false) {
			$skillsTag = $tag->find('a');
			foreach ($skillsTag as $skill) {
				$href = $skill->href;
				$link = '';
				if (preg_match("/\/jobs\/([\w,-]+)\//", $href, $matches) == 1) {
					$link = $matches[1];
				}
				$name = $skill->innertext;
				$skillID = getSkillID($name, $link);
				array_push($skills, $skillID);
			}
		}
	}
	$prjTitle = $html->find("h1[class=PageProjectViewLogout-header-title]", 0)->innertext;
	echo $prjTitle."\n";
	
	if (count($html->find("p[class=PageProjectViewLogout-detail-paragraph]")) == 0) {
		echo "Oh, shi...";
	} else {
		$prjDescr = $html->find("p[class=PageProjectViewLogout-detail-paragraph]", 0)->find('p', 0)->innertext;
		echo $prjDescr;
	}
*/
?>