CREATE TABLE b_tasks (
	ID int(11) NOT NULL AUTO_INCREMENT,
	TITLE varchar(255) DEFAULT NULL,
	DESCRIPTION text,
	DESCRIPTION_IN_BBCODE char(1) NOT NULL DEFAULT 'N',
	PRIORITY char(1) NOT NULL DEFAULT '1',
	STATUS char(1) DEFAULT NULL,
	RESPONSIBLE_ID int(11) DEFAULT NULL,
	DATE_START datetime DEFAULT NULL,
	DURATION_PLAN int(11) DEFAULT NULL,
	DURATION_FACT int(11) DEFAULT NULL,
	DURATION_TYPE enum('hours','days') NOT NULL DEFAULT 'days',
	TIME_ESTIMATE int(11) NOT NULL DEFAULT 0,
	REPLICATE char(1) NOT NULL DEFAULT 'N',
	DEADLINE datetime DEFAULT NULL,
	START_DATE_PLAN datetime DEFAULT NULL,
	END_DATE_PLAN datetime DEFAULT NULL,
	CREATED_BY int(11) DEFAULT NULL,
	CREATED_DATE datetime DEFAULT NULL,
	CHANGED_BY int(11) DEFAULT NULL,
	CHANGED_DATE datetime DEFAULT NULL,
	STATUS_CHANGED_BY int(11) DEFAULT NULL,
	STATUS_CHANGED_DATE datetime DEFAULT NULL,
	CLOSED_BY int(11) DEFAULT NULL,
	CLOSED_DATE datetime DEFAULT NULL,
	GUID varchar(50) DEFAULT NULL,
	XML_ID varchar(50) DEFAULT NULL,
	EXCHANGE_ID varchar(196) DEFAULT NULL,
	EXCHANGE_MODIFIED varchar(196) DEFAULT NULL,
	OUTLOOK_VERSION int(11) DEFAULT NULL,
	MARK char(1) DEFAULT NULL,
	ALLOW_CHANGE_DEADLINE char(1) NOT NULL DEFAULT 'N',
	ALLOW_TIME_TRACKING char(1) NOT NULL DEFAULT 'N',
	TASK_CONTROL char(1) NOT NULL DEFAULT 'N',
	ADD_IN_REPORT char(1) NOT NULL DEFAULT 'N',
	GROUP_ID int(11) DEFAULT NULL,
	PARENT_ID int(11) DEFAULT NULL,
	FORUM_TOPIC_ID bigint(20) DEFAULT NULL,
	MULTITASK char(1) NOT NULL DEFAULT 'N',
	SITE_ID char(2) NOT NULL,
	DECLINE_REASON text,
	FORKED_BY_TEMPLATE_ID int(11) DEFAULT NULL,
	ZOMBIE char(1) NOT NULL DEFAULT 'N',
	DEADLINE_COUNTED int(11) NOT NULL DEFAULT 0,
	PRIMARY KEY (ID),
	KEY FORUM_TOPIC_ID (FORUM_TOPIC_ID),
	KEY PARENT_ID (PARENT_ID),
	KEY CREATED_BY (CREATED_BY),
	KEY RESPONSIBLE_ID (RESPONSIBLE_ID),
	KEY CHANGED_BY (CHANGED_BY),
	UNIQUE KEY GUID (GUID)
);

CREATE INDEX b_tasks_deadline_ibuk ON b_tasks(DEADLINE, DEADLINE_COUNTED);


CREATE TABLE b_tasks_files_temporary (
	USER_ID int(11) NOT NULL,
	FILE_ID int(11) NOT NULL,
	UNIX_TS int(11) NOT NULL,
	PRIMARY KEY (FILE_ID),
	KEY UNIX_TS (UNIX_TS),
	KEY USER_ID (USER_ID)
);


CREATE TABLE b_tasks_dependence (
	TASK_ID int(11) NOT NULL DEFAULT '0',
	DEPENDS_ON_ID int(11) NOT NULL DEFAULT '0',
	PRIMARY KEY (TASK_ID,DEPENDS_ON_ID),
	KEY DEPENDS_ON_ID (DEPENDS_ON_ID)
);

CREATE TABLE b_tasks_file (
	TASK_ID int(11) NOT NULL DEFAULT '0',
	FILE_ID int(11) NOT NULL DEFAULT '0',
	PRIMARY KEY (TASK_ID,FILE_ID),
	KEY FILE_ID (FILE_ID)
);

CREATE TABLE b_tasks_member (
	TASK_ID int(11) NOT NULL DEFAULT '0',
	USER_ID int(11) NOT NULL DEFAULT '0',
	TYPE char(1) NOT NULL DEFAULT '',
	PRIMARY KEY (TASK_ID,USER_ID,TYPE),
	KEY USER_ID_TYPE (USER_ID, TYPE)
);

CREATE TABLE b_tasks_tag (
	TASK_ID int(11) NOT NULL,
	USER_ID int(11) NOT NULL,
	NAME varchar(255) NOT NULL,
	PRIMARY KEY (TASK_ID,USER_ID,NAME),
	KEY NAME (NAME),
	KEY USER_ID (USER_ID)
);

CREATE TABLE b_tasks_template (
	ID int(11) NOT NULL AUTO_INCREMENT,
	TASK_ID int(11) DEFAULT NULL,
	TITLE varchar(255) DEFAULT NULL,
	DESCRIPTION text,
	DESCRIPTION_IN_BBCODE char(1) NOT NULL DEFAULT 'N',
	PRIORITY char(1) NOT NULL DEFAULT '1',
	STATUS char(1) NOT NULL DEFAULT '1',
	RESPONSIBLE_ID int(11) DEFAULT NULL,
	DEADLINE_AFTER int(11) DEFAULT NULL,
	REPLICATE char(1) NOT NULL DEFAULT 'N',
	REPLICATE_PARAMS text,
	CREATED_BY int(11) DEFAULT NULL,
	XML_ID varchar(50) DEFAULT NULL,
	ALLOW_CHANGE_DEADLINE char(1) NOT NULL DEFAULT 'N',
	ALLOW_TIME_TRACKING char(1) NOT NULL DEFAULT 'N',
	TASK_CONTROL char(1) NOT NULL DEFAULT 'N',
	ADD_IN_REPORT char(1) NOT NULL DEFAULT 'N',
	GROUP_ID int(11) DEFAULT NULL,
	PARENT_ID int(11) DEFAULT NULL,
	MULTITASK char(1) NOT NULL DEFAULT 'N',
	SITE_ID char(2) NOT NULL,
	ACCOMPLICES text,
	AUDITORS text,
	RESPONSIBLES text,
	FILES text,
	TAGS text,
	DEPENDS_ON text,
	TPARAM_TYPE int,
	PRIMARY KEY (ID),
	UNIQUE KEY TASK_ID (TASK_ID),
	KEY PARENT_ID (PARENT_ID),
	KEY CREATED_BY (CREATED_BY),
	KEY RESPONSIBLE_ID (RESPONSIBLE_ID)
);

CREATE TABLE b_tasks_template_dep (
	TEMPLATE_ID int(11) NOT NULL,
	PARENT_TEMPLATE_ID int(11) NOT NULL,
	DIRECT tinyint default '0',

	PRIMARY KEY (TEMPLATE_ID,PARENT_TEMPLATE_ID),
	KEY IX_TASKS_TASK_DEP_DIR (DIRECT)
);

CREATE TABLE b_tasks_viewed (
	TASK_ID int(11) NOT NULL,
	USER_ID int(11) NOT NULL,
	VIEWED_DATE timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (TASK_ID,USER_ID),
	KEY USER_ID (USER_ID)
);

CREATE TABLE b_tasks_log (
  CREATED_DATE datetime NOT NULL,
  USER_ID int(11) NOT NULL,
  TASK_ID int(11) NOT NULL,
  FIELD varchar(50) NOT NULL,
  FROM_VALUE text,
  TO_VALUE text
);

CREATE INDEX b_tasks_log1 ON b_tasks_log(TASK_ID, CREATED_DATE);

CREATE TABLE b_tasks_elapsed_time (
  ID int(11) NOT NULL AUTO_INCREMENT,
  CREATED_DATE timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  DATE_START datetime DEFAULT NULL,
  DATE_STOP datetime DEFAULT NULL,
  USER_ID int(11) NOT NULL,
  TASK_ID int(11) NOT NULL,
  MINUTES int(11) NOT NULL,
  SECONDS int(11) NOT NULL DEFAULT '0',
  SOURCE int(11) NOT NULL DEFAULT '1',
  COMMENT_TEXT text,
  PRIMARY KEY (ID),
  KEY TASK_ID (TASK_ID)
);

CREATE INDEX USER_ID ON b_tasks_elapsed_time(USER_ID);

CREATE TABLE b_tasks_reminder (
  USER_ID int(11) NOT NULL,
  TASK_ID int(11) NOT NULL,
  REMIND_DATE datetime NOT NULL,
  TYPE char(1) NOT NULL,
  TRANSPORT char(1) NOT NULL,
  KEY USER_ID (USER_ID,TASK_ID)
);

CREATE TABLE b_tasks_filters (
	ID int(11) NOT NULL AUTO_INCREMENT,
	USER_ID int(11) NOT NULL,
	NAME varchar(255) DEFAULT NULL,
	PARENT int(11) NOT NULL,
	SERIALIZED_FILTER text,
	PRIMARY KEY (ID),
	KEY USER_ID (USER_ID)
);

CREATE TABLE b_tasks_checklist_items (
	ID int(11) NOT NULL AUTO_INCREMENT,
	TASK_ID int(11) NOT NULL,
	CREATED_BY int(11) NOT NULL,
	TOGGLED_BY int(11) DEFAULT NULL,
	TOGGLED_DATE datetime DEFAULT NULL,
	TITLE varchar(255) DEFAULT NULL,
	IS_COMPLETE char(1) NOT NULL DEFAULT 'N',
	SORT_INDEX int(11) NOT NULL DEFAULT '0',
	PRIMARY KEY (ID),
	KEY USER_ID (TASK_ID)
);

create table b_tasks_template_chl_item (
	ID int NOT NULL AUTO_INCREMENT,
	TEMPLATE_ID int(11) NOT NULL,
	SORT int(11) DEFAULT '0',
	TITLE varchar(255) NOT NULL,
	CHECKED tinyint default '0',

	PRIMARY KEY (ID),
	KEY ix_tasks_templ_chl_item_tid(TEMPLATE_ID)
);

CREATE TABLE b_tasks_timer (
	TASK_ID int(11) NOT NULL,
	USER_ID int(11) NOT NULL,
	TIMER_STARTED_AT int(11) NOT NULL DEFAULT '0',
	TIMER_ACCUMULATOR int(11) NOT NULL DEFAULT '0',
	PRIMARY KEY (USER_ID),
	KEY TASK_ID (TASK_ID)
);

CREATE TABLE b_tasks_columns (
	ID int(11) NOT NULL AUTO_INCREMENT,
	USER_ID int(11) NOT NULL,
	CONTEXT_ID int(11) NOT NULL,
	NAME varchar(255) DEFAULT NULL,
	SERIALIZED_COLUMNS text,
	PRIMARY KEY (ID),
	KEY USER_ID (USER_ID)
);
