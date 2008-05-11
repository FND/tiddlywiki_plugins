CREATE TABLE pluginLibrary.repositories (
	ID INT NOT NULL AUTO_INCREMENT ,
	URI TEXT NOT NULL ,
	type TEXT NOT NULL ,
	name TEXT NOT NULL ,
	contact TEXT NOT NULL ,
	added DATE NOT NULL ,
	updated DATE NOT NULL ,
	skipped INT NOT NULL DEFAULT '0',
	disabled BOOL NOT NULL ,
	PRIMARY KEY ( ID )
) ENGINE = MYISAM;

CREATE TABLE pluginLibrary.plugins (
	ID INT NOT NULL AUTO_INCREMENT ,
	repository_ID INT NOT NULL ,
	available BOOL NOT NULL ,
	title TEXT NOT NULL ,
	text TEXT NOT NULL ,
	created DATE NULL ,
	modified DATE NULL ,
	modifier TEXT NULL ,
	updated DATE NOT NULL ,
	documentation TEXT NULL ,
	views INT NOT NULL DEFAULT '0',
	annotation TEXT NULL ,
	PRIMARY KEY ( ID )
) ENGINE = MYISAM;

 CREATE TABLE pluginLibrary.pluginTags (
	plugin_ID INT NOT NULL ,
	tag_ID INT NOT NULL
) ENGINE = MYISAM;

CREATE TABLE pluginLibrary.tags (
	ID INT NOT NULL AUTO_INCREMENT ,
	name TEXT NOT NULL ,
	`user-generated` BOOL NOT NULL ,
	PRIMARY KEY ( ID )
) ENGINE = MYISAM;

CREATE TABLE pluginLibrary.fields (
	ID INT NOT NULL AUTO_INCREMENT ,
	plugin_ID INT NOT NULL ,
	name TEXT NOT NULL ,
	value TEXT NOT NULL ,
	PRIMARY KEY ( ID )
) ENGINE = MYISAM;

CREATE TABLE pluginLibrary.metaslices (
	ID INT NOT NULL AUTO_INCREMENT ,
	plugin_ID INT NOT NULL ,
	name TEXT NOT NULL ,
	value TEXT NOT NULL ,
	PRIMARY KEY ( ID )
) ENGINE = MYISAM;

CREATE TABLE pluginLibrary.ratings (
	ID INT NOT NULL AUTO_INCREMENT ,
	plugin_ID INT NOT NULL ,
	IP_address TEXT NOT NULL ,
	timestamp DATE NOT NULL ,
	value INT NULL ,
	PRIMARY KEY ( ID )
) ENGINE = MYISAM;

CREATE TABLE pluginLibrary.comments (
	ID INT NOT NULL AUTO_INCREMENT ,
	plugin_ID INT NOT NULL ,
	author TEXT NOT NULL ,
	IP_address TEXT NOT NULL ,
	timestamp DATE NOT NULL ,
	text INT NULL ,
	PRIMARY KEY ( ID )
) ENGINE = MYISAM;





INSERT INTO pluginLibrary.repositories (
	ID ,
	URI ,
	type ,
	name ,
	contact ,
	added ,
	updated ,
	skipped ,
	disabled
)
VALUES (
	NULL ,
	'http://www.tiddlywiki.com/coreplugins.html',
	'TiddlyWiki',
	'Core Plugins',
	'MartinBudden@gmail.com',
	'2008-05-10',
	'2008-05-10',
	'',
	'0'
);

INSERT INTO pluginLibrary.repositories (
	ID ,
	URI ,
	type ,
	name ,
	contact ,
	added ,
	updated ,
	skipped ,
	disabled
)
VALUES (
	NULL ,
	'http://mptw.tiddlyspot.com',
	'TiddlyWiki',
	'MPTW',
	'SimonBaird@gmail.com',
	'2008-05-10',
	'2008-05-10',
	'',
	'0'
);
