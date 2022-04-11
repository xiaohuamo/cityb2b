SELECT id,displayName,name,businessName FROM `cc_user` WHERE name like "%'%" or displayName like  "%'%" or businessName like "%'%";
UPDATE `cc_user` SET `name` = replace (`name`,"'","");
UPDATE `cc_user` SET `displayName` = replace (`displayName`,"'","");
UPDATE `cc_user` SET `businessName` = replace (`businessName`,"'","");

select * from cc_user_factory where nickname like "%'%"


UPDATE `cc_user_factory` SET `nickname` = replace (`nickname`,"'","");



SELECT * FROM `cc_wj_abn_application` WHERE business_name like "%'%" or `untity_name` like "%'%"

update `cc_wj_abn_application` SET  `business_name` = replace (`business_name`,"'","");
update `cc_wj_abn_application` SET  `untity_name` = replace (`untity_name`,"'","");

SELECT * FROM `cc_wj_user_delivery_info` WHERE `displayName` like "%'%"
update `cc_wj_user_delivery_info` SET  `displayName` = replace (`displayName`,"'","");
