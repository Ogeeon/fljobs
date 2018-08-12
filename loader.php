<?php
	set_time_limit(6000);
	error_reporting(E_ALL);
	mb_internal_encoding("utf-8");
	
	require_once 'dbconnect.php';
	require_once 'simple_html_dom.php';
	
	function addPrjSkill($prjId, $skillId) {
		$query = "INSERT INTO prjskills (project_id, skill_id) VALUES ($prjId, $skillId)";
		SqlQuery($query);
	}
	
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
	
	
	function loadProjectsForSkill($skillLink) {
		echo "getting $skillLink projects<br/>";
		$pageCount = 1;
		while(true) {
 			echo "Processing page "."https://www.freelancer.com/jobs/".$skillLink."/".($pageCount)."/"."<br>\n";
			$html = file_get_html("https://www.freelancer.com/jobs/".$skillLink."/".($pageCount++)."/", false, null, -1);

			$projectsTag = $html->find("#project-list", 0);
 			$cnt = 0;
 			if (count($projectsTag->children()) == 0) {
 				break;
 			}
 			/*
 			foreach($projectsTag->children() as $prj) {
 				$heading = $prj->find('div[class=JobSearchCard-primary-heading]', 0);
 				$anchor = $heading->find('a', 0);
 				$content = file_get_html("https://www.freelancer.com".$anchor->href, false, null, -1);
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
 				
 				$query = "SELECT count(*) as num FROM projects WHERE id=$prjID";
 				$res = SqlQuery($query);
 				if ($res[0]["num"] == 1) {
 					continue;
 				}

 				$query = "INSERT INTO projects (id, link, title, description, added) VALUES ($prjID, '".$anchor->href."', '$prjTitle', '$prjDescr', ".time().")";
 				SqlQuery($query);
 					
 				foreach ($skills as $skill)
 					addPrjSkill($prjID, $skill);
 				
 				unset($content);
 			}
 			*/
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
		$dir = 'd:\\';
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
			echo "Skill link ".$row["link"]." done.<br>\n";
		}
	}
	loadProjects();
?>