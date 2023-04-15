create table b_kb_autologin
(
	id INT NOT NULL PRIMARY KEY,
	date_create DATETIME,
	date_end DATETIME,
	qnt INT,
	hash CHAR(32) NOT NULL
);
