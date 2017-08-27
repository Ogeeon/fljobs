<?php
	set_time_limit(6000);
	error_reporting(E_ALL);
	mb_internal_encoding("utf-8");
	
	require_once 'dbconnect.php';
	
	function addPrjSkill($prjId, $skillId) {
		$query = "INSERT INTO prjskills (project_id, skill_id) VALUES ($prjId, $skillId)";
		SqlQuery($query);
	}
	
	function unjson($input) {
		$input = str_replace("\r\n", "<br>", $input);
		$input = str_replace("\n\n", "<br>", $input);
		$input = str_replace("\n", "<br>", $input);
		$str = json_decode('{"0": "'.$input.'"}', true);
		return $str[0];
	}
	
	function loadProjectsForSkill($skillLink) {
		echo "getting $skillLink projects<br/>";
		$pageCount = 1;
		while(true) {
 			$content = file_get_contents("https://www.freelancer.com/jobs/".$skillLink."/".($pageCount++)."/", false, null, -1);
            //$content = file_get_contents("https://www.freelancer.com/jobs/Java/6/", false, null, -1);
			
//			$dir = getenv('OPENSHIFT_PHP_LOG_DIR')."/parses/";
/*
			$dir = "d:\\";
 			if ($myfile = fopen($dir."fl-$skillLink-".($pageCount-1).".txt", "w")) {// or die("Unable to open file!");
			fwrite($myfile, $content);
 			fclose($myfile);			
			}
*/
			
			//$content = file_get_contents("fl-j-1.txt");
			$matches = array();
			$projectsArray = "";
			if (preg_match("/var aaData = \[(.*?)\];/s", $content, $matches) == 1) {
				$projectsArray = $matches[1];
			}
			if ($projectsArray == "") {
				break;
			}
			if (preg_match_all("/\[(\d+),\"(.+?)\",\"(.+?)\",(.+?)\]/", $projectsArray, $prjMatches, PREG_SET_ORDER) > 0) {
				$count = 0;
				foreach($prjMatches as $project) {
					$count++;
					$id = $project[1];
					$prjTitle = str_replace("\\", "", unjson($project[2]));
					$prjDescr = str_replace("\\", "", unjson($project[3]));
					echo "id=$id<br>";
					$fltrd = preg_replace("/\\{.*?\\},/", "", $project[4]);
					$text = '$data=array('.$fltrd.');';
					eval($text);
					$prjSkills = $data[1];
					$prjLink = str_replace("\\", "", $data[18]);
                    
					
					$query = "SELECT count(*) as num FROM projects WHERE id=$id";
					$res = SqlQuery($query);
					if ($res[0]["num"] == 1) {
						continue;
					}
					
					$query = "INSERT INTO projects (id, link, title, description, added) VALUES ($id, '$prjLink', '$prjTitle', '$prjDescr', ".time().")";
					SqlQuery($query);
					
					if (preg_match_all("/(\d+)/", $prjSkills, $skills, PREG_PATTERN_ORDER) > 0) {
						$skillsArray = $skills[0];
						foreach ($skillsArray as $skill)
							addPrjSkill($id, $skill);
					}
                    
  					//if ($count > 2) break;
				}
			}
			unset($content);
			unset($projectsArray);
		}
	}

	function saveFeed($skillId,  $skillLink) {
		$query = "select `text` from `filters` where `skill`=$skillId";
		$filters = array();
		if ($res = SqlQuery($query)) {
			foreach ($res as $row) {
				$filters[] = $row["text"];
			}
		}
		$rss = "<?xml version='1.0' encoding='UTF-8'?><rss version='2.0' xmlns:content='http://purl.org/rss/1.0/modules/content/' xmlns:wfw='http://wellformedweb.org/CommentAPI/' xmlns:dc='http://purl.org/dc/elements/1.1/' xmlns:atom='http://www.w3.org/2005/Atom'><channel><title>$skillLink projects</title><link>https://freelancer.com/</link><atom:link href='http://fljobs-ogeeon.rhcloud.com/Translation.xml' rel='self' type='application/rss+xml' /><description>$skillLink Projects</description><language>en</language>";
		$rss .= "<lastBuildDate>".strftime("%a, %d %b %Y %T %Z", time())."</lastBuildDate>";
		$query = "select projects.* from projects inner join prjskills on (projects.id = prjskills.project_id)
					inner join skills on (prjskills.skill_id = skills.id) where skills.link like '$skillLink' order by projects.id desc limit 0,100";
		if ($res = SqlQuery($query))
			foreach ($res as $row) {
				$title = $row["title"];
				$description = $row["description"];
				if (count($filters) > 0) {
					$canShow = false;
					foreach ($filters as $f) {
						$pos1 = stripos($title, $f);
						$pos2 = stripos($description, $f);
						if ($pos1 !== false || $pos2 !== false)
							$canShow = true;
					}
					if ($canShow == false)
						continue;
				}
				
				$rss .= "<item><title><![CDATA[".$title."]]></title>";
				$rss .= "<guid isPermaLink='true'>https://www.freelancer.com".$row["link"]."</guid>";
				$rss .= "<link>https://www.freelancer.com".$row["link"]."</link>";
				$rss .= "<pubDate>".strftime("%a, %d %b %Y %T %Z", $row["added"])."</pubDate>";
				$rss .= "<description><![CDATA[".$description."]]></description></item>";
			}
		$rss .= "</channel></rss>";
		$dir = getenv('OPENSHIFT_REPO_DIR');
		$myfile = fopen($dir."/$skillLink.xml", "w") or die("Unable to open file ".$dir."/$skillLink.xml");
		fwrite($myfile, $rss);
		fclose($myfile);
	}

	function loadProjects() {
		$query = "SELECT id, link FROM skills WHERE watched=1";
		if ($res = SqlQuery($query))
		foreach ($res as $row) {
			loadProjectsForSkill($row["link"]);
			saveFeed($row["id"], $row["link"]);
		}
	}
	loadProjects();
?>