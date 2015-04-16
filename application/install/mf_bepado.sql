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

/* Table for persisting the module configuration */
CREATE TABLE  IF NOT EXISTS `mfbepadoconfiguration` (
  `OXID` CHAR(32) NOT NULL,
  `APIKEY` VARCHAR(255) DEFAULT NULL,
  `SANDBOXMODE` BOOLEAN DEFAULT TRUE,
  `MARKETPLACEHINTARTICLE` BOOLEAN DEFAULT FALSE,
  `MARKETPLACEHINTBASKET` BOOLEAN DEFAULT FALSE,
  `PURCHASEGROUP` VARCHAR(1) NOT NULL,
  PRIMARY KEY (`OXID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/**Table to persist a unit mapping, the oxid will be the oxid unit key, which should be translated */
CREATE TABLE  IF NOT EXISTS `mfbepadounits` (
  `OXID` VARCHAR(255) NOT NULL,
  `BEPADOUNITKEY` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`OXID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE bepado_categories ADD path VARCHAR(255) NOT NULL;
ALTER TABLE bepado_categories ADD catnid VARCHAR(255) NOT NULL;

/* Special user group for remote shops */
INSERT INTO oxgroups (`OXID`, `OXACTIVE`, `OXTITLE`) VALUES ('bepadoshopgroup', '0', 'Bepado Remote Shop');

/* Insert an own payment type for bebado. */
INSERT INTO oxpayments (`OXID`, `OXACTIVE`, `OXDESC`) VALUES ('bepadopaymenttype', '1', 'Bezahlung an Bepado Handelspartner');

/* Create an bepado shipping type and rule, cause the shop will get access to prices by shipping rules */
INSERT INTO oxdeliveryset (`OXID`, `OXACTIVE`, `OXTITLE`, `OXSHOPID`) VALUES ('bepadoshipping', '1', 'Bepado Shipping', 'oxbaseshop');
INSERT INTO oxdelivery (`OXID`, `OXACTIVE`, `OXTITLE`, `OXSHOPID`) VALUES ('bepadoshippingrule', '1', 'Bepado Shipping Rule', 'oxbaseshop');

/* Article can be marked as imported article in order */
ALTER TABLE oxorderarticles ADD imported TINYINT;

