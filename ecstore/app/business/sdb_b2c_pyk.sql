/*
Navicat MySQL Data Transfer

Source Server         : 172.16.2.105
Source Server Version : 50530
Source Host           : localhost:3306
Source Database       : sz_b2b2c

Target Server Type    : MYSQL
Target Server Version : 50530
File Encoding         : 65001

Date: 2013-08-20 13:58:19
*/

SET FOREIGN_KEY_CHECKS=0;
-- ----------------------------
-- Table structure for `sdb_b2c_pyk`
-- ----------------------------
DROP TABLE IF EXISTS `sdb_b2c_pyk`;
CREATE TABLE `sdb_b2c_pyk` (
  `PY` char(1) CHARACTER SET utf8 NOT NULL,
  `HZ` char(3) NOT NULL DEFAULT '',
  PRIMARY KEY (`PY`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of sdb_b2c_pyk
-- ----------------------------
INSERT INTO `sdb_b2c_pyk` VALUES ('A', '骜');
INSERT INTO `sdb_b2c_pyk` VALUES ('B', '簿');
INSERT INTO `sdb_b2c_pyk` VALUES ('C', '错');
INSERT INTO `sdb_b2c_pyk` VALUES ('D', '鵽');
INSERT INTO `sdb_b2c_pyk` VALUES ('E', '樲');
INSERT INTO `sdb_b2c_pyk` VALUES ('F', '鳆');
INSERT INTO `sdb_b2c_pyk` VALUES ('G', '腂');
INSERT INTO `sdb_b2c_pyk` VALUES ('H', '夻');
INSERT INTO `sdb_b2c_pyk` VALUES ('J', '攈');
INSERT INTO `sdb_b2c_pyk` VALUES ('K', '穒');
INSERT INTO `sdb_b2c_pyk` VALUES ('L', '鱳');
INSERT INTO `sdb_b2c_pyk` VALUES ('M', '旀');
INSERT INTO `sdb_b2c_pyk` VALUES ('N', '桛');
INSERT INTO `sdb_b2c_pyk` VALUES ('O', '沤');
INSERT INTO `sdb_b2c_pyk` VALUES ('P', '曝');
INSERT INTO `sdb_b2c_pyk` VALUES ('Q', '囕');
INSERT INTO `sdb_b2c_pyk` VALUES ('R', '鶸');
INSERT INTO `sdb_b2c_pyk` VALUES ('S', '蜶');
INSERT INTO `sdb_b2c_pyk` VALUES ('T', '箨');
INSERT INTO `sdb_b2c_pyk` VALUES ('W', '鹜');
INSERT INTO `sdb_b2c_pyk` VALUES ('X', '鑂');
INSERT INTO `sdb_b2c_pyk` VALUES ('Y', '韵');
INSERT INTO `sdb_b2c_pyk` VALUES ('Z', '咗');

delimiter $$
DROP FUNCTION IF EXISTS `func_get_first_letter_CN`;
CREATE FUNCTION `func_get_first_letter_CN`(words   varchar(255)) RETURNS varchar(10) CHARSET utf8
BEGIN  
  declare fpy char(1); 
  declare refpy varchar(255);
  declare allfpy varchar(10);
  declare pc char(1);  
  declare cc char(4);
  declare wl int;  
  Declare i int default 0;
  set @fpy='';
  set @refpy=trim(words);
  set @allfpy='';
  set @wl=CHAR_LENGTH(trim(words));
  while i<@wl do
  begin
   set @fpy=UPPER(SUBSTRING(@refpy,i+1,1));
   set @pc = (CONVERT(@fpy   USING   utf8));  
   set @cc = hex(@pc);  
   if @cc >= "B0A1" and @cc <="FEA0" then
   begin
    select PY from sdb_b2c_pyk where hz>=@pc limit 1 into @fpy;
   end;
   end if;
   if (@fpy<'A' or @fpy>'Z') then 
   begin 
    if((@cc>='30' and @cc<='39') or (@cc>='A3B0' and @cc<='A3B9') or (@cc>='A4A0' and @cc<='A8F0') ) then
    begin
     set @fpy='0';
    end;
    else
    begin
     set @fpy='';
    end;
    end if;
   end;  
   end if;  
  end;
  set @allfpy=CONCAT(@allfpy,@fpy);
  set i=i+1;
  end while;
  Return @allfpy;  
END$$