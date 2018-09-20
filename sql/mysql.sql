CREATE DATABASE `captcha` DEFAULT CHARSET = utf8;
USE `captcha`;
GRANT SELECT,INSERT,UPDATE,EXECUTE ON captcha.* TO captcha_user@localhost IDENTIFIED BY 'Password_qwerty1';
FLUSH PRIVILEGES;

CREATE TABLE account
(
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `email` VARCHAR(100) NOT NULL,
  `token` CHAR(40) NOT NULL,
  `pass` CHAR(40) NOT NULL DEFAULT '000000000000000000000000000000000000',
  `date_create` TIMESTAMP DEFAULT now() NOT NULL,
  `status` SMALLINT DEFAULT 0 NOT NULL
);
CREATE UNIQUE INDEX account_email_uindex ON account (`email`);
CREATE UNIQUE INDEX account_token_uindex ON account (`token`);

--INSERT INTO account (`email`,`token`) VALUES ('test@test.com',lower(concat(hex(crc32(now())),md5(concat(`email`,'0')))));
--SELECT `token`,lower(concat(hex(crc32(`date_create`)),md5(concat(`email`,CONVERT(`status`,CHAR))))) FROM account;

