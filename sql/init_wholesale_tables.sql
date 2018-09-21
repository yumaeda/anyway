USE wholesale;

CREATE TABLE IF NOT EXISTS `business_customers` (
    `id`             INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    `email`          VARCHAR(80) NOT NULL,
    `hash`           VARCHAR(500) NOT NULL,
    `name`           VARCHAR(100) NOT NULL,
    `name_phonetic`  VARCHAR(200) NOT NULL,
    `post_code`      CHAR(8) NOT NULL, /* xxx-xxxx */
    `prefecture`     VARCHAR(10) NOT NULL,
    `address`        VARCHAR(300) NOT NULL,
    `phone`          VARCHAR(13) NOT NULL, /* xxx-xxxx-xxxx */
    `comment`        VARCHAR(2000) NOT NULL,
    `date_created`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cart_items` (
    `id`              INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_session_id` CHAR(32) NOT NULL,
    `barcode_number`  SMALLINT UNSIGNED NOT NULL,
    `quantity`        SMALLINT UNSIGNED NOT NULL,
    `date_created`    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `date_modified`   TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY(`id`),
    KEY `user_session_id` (`user_session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `business_orders` (
    `id`             INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    `filemaker_id`   CHAR(6) NOT NULL DEFAULT 'xxxxxx',
    `customer_email` VARCHAR(80) NOT NULL,
    `transaction_id` CHAR(14) NOT NULL DEFAULT 'xxxx-xxxx-xxxx',
    `date_created`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `contents`       VARCHAR(5000) NOT NULL,
    `status`         TINYINT UNSIGNED NOT NULL, /* 0: Not confirmed, 1: Confirmed, 2: Paid, 3: Issued, 4: Shipped */
    `payment_method` TINYINT UNSIGNED NOT NULL, /* 1: Credit Card, 2: Bank */
    `delivery_date`  VARCHAR(30) NOT NULL,
    `delivery_time`  VARCHAR(30) NOT NULL,
    `refrigerated`   BOOLEAN NOT NULL DEFAULT 1,
    `wine_total`     INTEGER UNSIGNED NOT NULL,
    `shipping_fee`   INTEGER UNSIGNED NOT NULL,
    `ip_address`     VARCHAR(50) NOT NULL DEFAULT '0.0.0.0',
    `user_agent`     VARCHAR(500) NOT NULL,
    PRIMARY KEY(`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
