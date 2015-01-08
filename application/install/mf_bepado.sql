CREATE TABLE IF NOT EXISTS `bepado_change` (
  `c_source_id` VARCHAR(64) NOT NULL,
  `c_operation` CHAR(8) NOT NULL,
  `c_revision` DECIMAL(20, 10) NOT NULL,
  `changed` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX (`c_source_id`),
  UNIQUE (`c_revision`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `bepado_product` (
  `p_source_id` VARCHAR(64) NOT NULL,
  `p_hash` VARCHAR(64) NOT NULL,
  `changed` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`p_source_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


ALTER TABLE `bepado_change`
    ADD `c_product` BLOB NULL AFTER `c_revision`;

CREATE TABLE IF NOT EXISTS `bepado_data` (
  `d_key` VARCHAR(32) NOT NULL,
  `d_value` VARCHAR(256) NOT NULL,
  `changed` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`d_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `bepado_change` MODIFY `c_product` LONGBLOB NULL;

CREATE TABLE IF NOT EXISTS `bepado_shop_config` (
  `s_shop` VARCHAR(32) NOT NULL,
  `s_config` BLOB NOT NULL,
  `changed` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`s_shop`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `bepado_reservations` (
  `r_id` VARCHAR(32) NOT NULL,
  `r_state` VARCHAR(12) NOT NULL,
  `r_order` LONGBLOB NOT NULL,
  `changed` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`r_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `bepado_shop_config` CHANGE `s_config` `s_config` LONGBLOB NOT NULL;

CREATE TABLE IF NOT EXISTS `bepado_shipping_costs` (
  `sc_shop` VARCHAR(32) NOT NULL,
  `sc_revision` VARCHAR(32) NOT NULL,
  `sc_shipping_costs` LONGBLOB NOT NULL,
  `changed` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`sc_shop`),
  INDEX (`sc_revision`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `bepado_shipping_costs`
    CHANGE COLUMN `sc_shop` `sc_from_shop` VARCHAR(32) NOT NULL,
    ADD COLUMN `sc_to_shop` VARCHAR(32) NOT NULL  AFTER `sc_from_shop`,
    DROP PRIMARY KEY,
    ADD PRIMARY KEY (`sc_from_shop`, `sc_to_shop`);

CREATE TABLE IF NOT EXISTS `bepado_product_state` (
  `OXID` VARCHAR(32),
  `p_source_id` VARCHAR(64) NOT NULL,
  `shop_id` VARCHAR(64) NOT NULL,
  `state` TINYINT NOT NULL,
  CONSTRAINT pk_p_state PRIMARY KEY (p_source_id, shop_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `bepado_categories` (
  `oxid` CHAR(32) NOT NULL,
  `title` VARCHAR(255),
  PRIMARY KEY (`oxid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/* Adds column for the payment type mapping */
ALTER TABLE oxpayments ADD bepadopaymenttype VARCHAR(100);

/* Special user group for remote shops */
INSERT INTO oxgroups (`OXID`, `OXACTIVE`, `OXTITLE`) VALUES ('bepadoshopgroup', '0', 'Bepado Remote Shop');

/* Chance to map an user to a bepado shop. All shops will get its own user */
ALTER TABLE oxuser ADD bepadoshopid VARCHAR(100);

/* Create an bepado shipping type and rule, cause the shop will get access to prices by shipping rules */
INSERT INTO oxdeliveryset (`OXID`, `OXACTIVE`, `OXTITLE`, `OXSHOPID`) VALUES ('bepadoshipping', '1', 'Bepado Shipping', 'oxbaseshop');
INSERT INTO oxdelivery (`OXID`, `OXACTIVE`, `OXTITLE`, `OXSHOPID`) VALUES ('bepadoshippingrule', '1', 'Bepado Shipping Rule', 'oxbaseshop');

/* Article can be marked as imported article in order */
ALTER TABLE oxorderarticles ADD imported TINYINT;

/* Field to persist the bepado order state to not send an state again */
ALTER TABLE oxorder ADD mf_bepado_state VARCHAR(50);

