CREATE DATABASE `fljobs` /*!40100 DEFAULT CHARACTER SET utf8 */;

CREATE TABLE `filters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `skill` int(11) DEFAULT NULL,
  `text` char(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `skill` (`skill`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

CREATE TABLE `prjskills` (
  `project_id` bigint(20) DEFAULT NULL,
  `skill_id` int(11) DEFAULT NULL,
  KEY `prjskills_fk` (`project_id`),
  CONSTRAINT `prjskills_fk` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `projects` (
  `id` bigint(20) NOT NULL,
  `title` char(250) DEFAULT NULL,
  `description` text,
  `link` char(200) DEFAULT NULL,
  `added` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `skills` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` char(100) DEFAULT NULL,
  `link` char(100) DEFAULT NULL,
  `watched` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=879 DEFAULT CHARSET=utf8;
