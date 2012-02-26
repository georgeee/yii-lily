PRAGMA foreign_keys=OFF;
BEGIN TRANSACTION;
CREATE TABLE 'tbl_migration' (
	"version" varchar(255) NOT NULL PRIMARY KEY,
	"apply_time" integer
);
INSERT INTO "tbl_migration" VALUES('m000000_000000_base',1329929588);
INSERT INTO "tbl_migration" VALUES('m120131_112629_lily_tables_create',1329929590);
CREATE TABLE 'tbl_lily_user' (
	"uid" integer PRIMARY KEY AUTOINCREMENT NOT NULL,
	"deleted" integer,
	"active" tinyint(1),
	"inited" tinyint(1)
);
CREATE TABLE 'tbl_lily_account' (
	"aid" integer PRIMARY KEY AUTOINCREMENT NOT NULL,
	"uid" integer,
	"service" varchar(255) NOT NULL,
	"id" varchar(255) NOT NULL,
	"hidden" tinyint(1),
	"data" blob,
	"created" integer
);
CREATE TABLE 'tbl_lily_email_account_activation' (
	"code_id" integer PRIMARY KEY AUTOINCREMENT NOT NULL,
	"uid" integer,
	"email" varchar(255) NOT NULL,
	"password" varchar(255) NOT NULL,
	"code" varchar(255) NOT NULL,
	"created" integer
);
CREATE TABLE 'tbl_lily_session' (
	"sid" integer PRIMARY KEY AUTOINCREMENT NOT NULL,
	"aid" integer,
	"data" blob,
	"ssid" varchar(255) NOT NULL,
	"created" integer
);
CREATE TABLE 'tbl_lily_onetime' (
	"tid" integer PRIMARY KEY AUTOINCREMENT NOT NULL,
	"uid" integer,
	"token" varchar(255) NOT NULL,
	"created" integer
);
CREATE UNIQUE INDEX 'service_id' ON 'tbl_lily_account' ("service", "id");
COMMIT;
