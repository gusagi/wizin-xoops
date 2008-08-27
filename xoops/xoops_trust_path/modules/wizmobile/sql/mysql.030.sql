DROP TABLE IF EXISTS `{prefix}_{dirname}_module`;
CREATE TABLE `{prefix}_{dirname}_module` (
    `wmm_module_id` SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT ,
    `wmm_mid` MEDIUMINT UNSIGNED NOT NULL ,
    `wmm_init_datetime` datetime NOT NULL default '0000-00-00 00:00:00',
    `wmm_update_datetime` datetime NOT NULL default '0000-00-00 00:00:00',
    `wmm_delete_datetime` datetime NOT NULL default '0000-00-00 00:00:00',
    PRIMARY KEY  (`wmm_module_id`)
) Type=MyISAM;
ALTER TABLE `{prefix}_{dirname}_module` ADD INDEX `wmm_idx` ( `wmm_mid`, `wmm_delete_datetime` ) ;
