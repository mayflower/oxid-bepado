/*
 * Copyright (C) 2015  Mayflower GmbH
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

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

ALTER TABLE bepado_categories ADD path VARCHAR(255) NOT NULL;
ALTER TABLE bepado_categories ADD catnid VARCHAR(255) NOT NULL;

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

