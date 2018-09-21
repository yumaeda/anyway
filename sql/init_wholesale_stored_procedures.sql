USE wholesale;

/* Drop procedures first. */
DROP PROCEDURE IF EXISTS add_business_customer;
DROP PROCEDURE IF EXISTS update_business_customer_password;
DROP PROCEDURE IF EXISTS update_business_customer_info;
DROP PROCEDURE IF EXISTS get_business_customer_names;
DROP PROCEDURE IF EXISTS get_business_customer;
DROP PROCEDURE IF EXISTS get_business_customer_count;
DROP PROCEDURE IF EXISTS get_business_customer_wines;
DROP PROCEDURE IF EXISTS get_business_customer_wines_by_keyword;
DROP PROCEDURE IF EXISTS get_business_customer_wine;
DROP PROCEDURE IF EXISTS add_to_cart;
DROP PROCEDURE IF EXISTS remove_cart_item;
DROP PROCEDURE IF EXISTS set_cart_item_quantity;
DROP PROCEDURE IF EXISTS clear_cart_items;
DROP PROCEDURE IF EXISTS get_cart_items;
DROP PROCEDURE IF EXISTS get_cart_item_total;
DROP PROCEDURE IF EXISTS checkout_cart_items;
DROP PROCEDURE IF EXISTS add_business_order;
DROP PROCEDURE IF EXISTS get_business_orders;
DROP PROCEDURE IF EXISTS remove_business_order;
DROP PROCEDURE IF EXISTS set_business_order_status;
DROP PROCEDURE IF EXISTS set_business_order_tracking_id;

/* Set delimiter to '$$' in order to properly create stored procedures. */
DELIMITER $$

/*
* add_business_customer
*
* DB:     wholesale
* Tables: business_customers
*/
CREATE PROCEDURE add_business_customer(argEmail VARCHAR(80), argHash VARCHAR(500), argName VARCHAR(100),
                              argNamePhonetic VARCHAR(200), argPostCode CHAR(8), argPrefecture VARCHAR(10), argAddress VARCHAR(300), argPhone VARCHAR(13), argComment VARCHAR(2000))
BEGIN
    INSERT INTO business_customers(email, hash, name, name_phonetic, post_code, prefecture, address, phone, comment)
    VALUES (argEmail, argHash, argName, argNamePhonetic, argPostCode, argPrefecture, argAddress, argPhone, argComment);
END$$

/*
* update_business_customer_password
*
* DB:     wholesale
* Tables: business_customers
*/
CREATE PROCEDURE update_business_customer_password(argEmail VARCHAR(80), argHash VARCHAR(500))
BEGIN
    UPDATE business_customers SET hash=argHash
    WHERE email=argEmail LIMIT 1;
END$$

/*
 * update_business_customer_info
 *
 * DB:     wholesale
 * Tables: business_customers
*/
CREATE PROCEDURE update_business_customer_info(argEmail VARCHAR(80), argPostCode CHAR(8), argPrefecture VARCHAR(10), argAddress VARCHAR(300), argPhone VARCHAR(13))
BEGIN
    UPDATE business_customers SET post_code=argPostCode, prefecture=argPrefecture, address=argAddress, phone=argPhone
    WHERE email=argEmail LIMIT 1;
END$$

/*
* get_business_customer_names
*
* DB:     wholesale
* Tables: business_customers
*/
CREATE PROCEDURE get_business_customer_names()
BEGIN
    SELECT id, name FROM business_customers;
END$$

/*
* get_business_customer
*
* DB:     wholesale
* Tables: business_customers
*/
CREATE PROCEDURE get_business_customer(argEmail VARCHAR(80))
BEGIN
    SELECT * FROM business_customers WHERE email=argEmail;
END$$

/*
* get_business_customer_count
*
* DB:     wholesale
* Tables: business_customers
*/
CREATE PROCEDURE get_business_customer_count(argEmail VARCHAR(80))
BEGIN
    SELECT COUNT(*) FROM business_customers WHERE email=argEmail;
END$$

/*
* get_business_customer_wines
*
* DB:     wholesale, shop
* Tables: wholesale_wines, wines
*/
CREATE PROCEDURE get_business_customer_wines(argType VARCHAR(30), argCountry VARCHAR(30), argRegion VARCHAR(500), argProducer VARCHAR(300))
BEGIN
    SELECT
        lhs.barcode_number  AS barcode_number,
        lhs.wholesale_price AS price,
        rhs.cepage,
        rhs.cultivation_method,
        rhs.stock,
        rhs.importer,
        rhs.type,
        rhs.country,
        rhs.region,
        rhs.region_jpn,
        rhs.district,
        rhs.district_jpn,
        rhs.village,
        rhs.village_jpn,
        rhs.producer,
        rhs.producer_jpn,
        rhs.vintage,
        rhs.comment,
        rhs.point,
        rhs.etc,
        rhs.catch_copy,
        rhs.combined_name,
        rhs.combined_name_jpn
    FROM wholesale.wholesale_wines lhs
    INNER JOIN shop.wines rhs
    ON lhs.barcode_number = rhs.barcode_number
    WHERE (rhs.availability = 'Online') AND (rhs.apply <> 'DP') AND (rhs.stock > 0) AND (lhs.wholesale_price > 0) AND (rhs.type <> 'Goods') AND (rhs.type <> 'Food') AND
    (argType = '' OR rhs.type = argType) AND
    (argCountry = '' OR rhs.country = argCountry) AND
    (argRegion = '' OR rhs.region = argRegion) AND
    (argProducer = '' OR (rhs.producer = argProducer COLLATE utf8_unicode_ci))
    ORDER BY lhs.wholesale_price;
END$$

/*
* get_business_customer_wines_by_keyword
*
* DB:     wholesale, shop
* Tables: wholesale_wines, wines
*/
CREATE PROCEDURE get_business_customer_wines_by_keyword(argKeyword VARCHAR(500))
BEGIN
    SELECT
        lhs.barcode_number  AS barcode_number,
        lhs.wholesale_price AS price,
        rhs.cepage,
        rhs.cultivation_method,
        rhs.stock,
        rhs.importer,
        rhs.type,
        rhs.country,
        rhs.region,
        rhs.region_jpn,
        rhs.district,
        rhs.district_jpn,
        rhs.village,
        rhs.village_jpn,
        rhs.producer,
        rhs.producer_jpn,
        rhs.vintage,
        rhs.comment,
        rhs.point,
        rhs.etc,
        rhs.catch_copy,
        rhs.combined_name,
        rhs.combined_name_jpn
    FROM wholesale.wholesale_wines lhs
    INNER JOIN shop.wines rhs
    ON lhs.barcode_number = rhs.barcode_number
    WHERE (rhs.availability = 'Online') AND (rhs.apply <> 'DP') AND (rhs.stock > 0) AND (lhs.wholesale_price > 0) AND (rhs.type <> 'Goods') AND (rhs.type <> 'Food') AND
    (
        (rhs.barcode_number    LIKE CONCAT('%', argKeyword, '%')) OR
        (rhs.importer          LIKE CONCAT('%', argKeyword, '%') COLLATE utf8_general_ci) OR
        (rhs.cepage            LIKE CONCAT('%', argKeyword, '%')) OR
        (rhs.producer          LIKE CONCAT('%', argKeyword, '%')) OR
        (rhs.producer_jpn      LIKE CONCAT('%', argKeyword, '%') COLLATE utf8_general_ci) OR
        (rhs.country           LIKE CONCAT('%', argKeyword, '%')) OR
        (rhs.region            LIKE CONCAT('%', argKeyword, '%')) OR
        (rhs.region_jpn        LIKE CONCAT('%', argKeyword, '%')) OR
        (rhs.district          LIKE CONCAT('%', argKeyword, '%')) OR
        (rhs.district_jpn      LIKE CONCAT('%', argKeyword, '%')) OR
        (rhs.combined_name     LIKE CONCAT('%', argKeyword, '%')) OR
        (rhs.combined_name_jpn LIKE CONCAT('%', argKeyword, '%'))
    )
    ORDER BY lhs.wholesale_price;
END$$

/*
* get_business_customer_wine
*
* DB:     wholesale, shop
* Tables: wholesale_wines, wines
*/
CREATE PROCEDURE get_business_customer_wine(argBarcode CHAR(4))
BEGIN
    SELECT
        lhs.barcode_number  AS barcode_number,
        lhs.wholesale_price AS price,
        rhs.cepage,
        rhs.cultivation_method,
        rhs.stock,
        rhs.importer,
        rhs.type,
        rhs.country,
        rhs.region,
        rhs.region_jpn,
        rhs.district,
        rhs.district_jpn,
        rhs.village,
        rhs.village_jpn,
        rhs.producer,
        rhs.producer_jpn,
        rhs.vintage,
        rhs.comment,
        rhs.point,
        rhs.etc,
        rhs.catch_copy,
        rhs.combined_name,
        rhs.combined_name_jpn
    FROM wholesale.wholesale_wines lhs
    INNER JOIN shop.wines rhs
    ON lhs.barcode_number = rhs.barcode_number
    WHERE (lhs.wholesale_price > 0) AND (rhs.type <> 'Goods') AND (rhs.type <> 'Food') AND (argBarcode = lhs.barcode_number);
END$$

/*
 * add_to_cart
 *
 * DB:     wholesale_wines
 * Tables: cart_items, wines
*/
CREATE PROCEDURE add_to_cart(argUserId CHAR(32), argBarcodeNumber SMALLINT UNSIGNED, argQuantity SMALLINT UNSIGNED)
BEGIN
    DECLARE cartId INTEGER;
    DECLARE cOnline INTEGER;

    SELECT COUNT(*) INTO cOnline FROM wholesale.wholesale_wines lhs
    INNER JOIN shop.wines rhs
    ON lhs.barcode_number = rhs.barcode_number
    WHERE (rhs.availability = 'Online') AND (rhs.apply <> 'DP') AND (rhs.stock > 0) AND (lhs.wholesale_price > 0) AND (rhs.type <> 'Goods') AND (rhs.type <> 'Food');

    IF (cOnline > 0) THEN
        SELECT id INTO cartId FROM cart_items WHERE user_session_id=argUserId AND barcode_number=argBarcodeNumber;
        IF cartId > 0 THEN
            UPDATE cart_items SET quantity=(quantity + argQuantity), date_modified=NOW()
            WHERE id=cartId;
        ELSE
            INSERT INTO cart_items (user_session_id, barcode_number, quantity) VALUES (argUserId, argBarcodeNumber, argQuantity);
        END IF;
    END IF;
END$$

/*
 * set_cart_item_quantity
 *
 * DB:     wholesale
 * Tables: cart_items
*/
CREATE PROCEDURE set_cart_item_quantity(argUserId CHAR(32), argBarcode CHAR(4), argQuantity SMALLINT UNSIGNED)
BEGIN
    IF argQuantity > 0 THEN
        UPDATE cart_items SET quantity=argQuantity, date_modified=NOW()
        WHERE user_session_id=argUserId AND barcode_number=argBarcode;
    ELSEIF argQuantity = 0 THEN
        CALL remove_cart_item(argUserId, argBarcode);
    END IF;
END$$

/*
 * remove_cart_item
 *
 * DB:     wholesale
 * Tables: cart_items
*/
CREATE PROCEDURE remove_cart_item(argUserId CHAR(32), argBarcode CHAR(4))
BEGIN
    DELETE FROM cart_items WHERE user_session_id=argUserId AND barcode_number=argBarcode;
END$$

/*
 * clear_cart_items
 *
 * DB:     wholesale
 * Tables: cart_items
*/
CREATE PROCEDURE clear_cart_items(argUserId CHAR(32))
BEGIN
    DELETE FROM cart_items WHERE user_session_id=argUserId;
END$$

/*
 * get_cart_items
 *
 * DB:     wholesale
 * Tables: wholesale.cart_items, wholesale_wines, shop.wines
*/
CREATE PROCEDURE get_cart_items(argUserId CHAR(32))
BEGIN
    SELECT
        lhs.barcode_number,
        lhs.quantity,
        rhs.vintage,
        rhs.type,
        rhs.stock,
        rhs.producer_jpn AS producer,
        rhs.combined_name_jpn AS name,
        mhs.wholesale_price AS price
    FROM wholesale.cart_items lhs
    INNER JOIN shop.wines rhs
    ON lhs.barcode_number = rhs.barcode_number
    INNER JOIN wholesale.wholesale_wines mhs
    ON lhs.barcode_number = mhs.barcode_number
    WHERE
        (rhs.availability = 'Online') AND (rhs.apply <> 'DP') AND
        (rhs.stock > 0) AND (mhs.wholesale_price > 0) AND (argUserId = lhs.user_session_id);
END$$

/*
 * get_cart_item_total
 *
 * DB:     wholesale
 * Tables: wholesale.cart_items, wholesale_wines
*/
CREATE PROCEDURE get_cart_item_total(argUserId CHAR(32))
BEGIN
    SELECT SUM(cart_items.quantity * wholesale_wines.wholesale_price) AS total_price
    FROM cart_items
    INNER JOIN wholesale_wines
    ON cart_items.barcode_number = wholesale_wines.barcode_number
    WHERE cart_items.user_session_id=argUserId;
END$$

/*
 * checkout_cart_items
 *
 * DB:     shop, wholesale
 * Tables: wholesale.cart_items, shop.wines
 *
 * Check out wines in the specified user's cart.
*/
CREATE PROCEDURE checkout_cart_items(argUserId CHAR(32), OUT argSuccess INTEGER)
BEGIN
    DECLARE tmpId INTEGER UNSIGNED;
    DECLARE tmpQty INTEGER UNSIGNED;
    DECLARE done INTEGER DEFAULT 0;
    DECLARE cur CURSOR FOR SELECT barcode_number, quantity FROM cart_items WHERE cart_items.user_session_id=argUserId;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done=1;

    START TRANSACTION;
    OPEN cur;
	REPEAT
	    FETCH cur INTO tmpId, tmpQty;
	    IF NOT done THEN
		CALL shop.checkout_wine(tmpId, tmpQty, @fSuccess);

		SELECT @fSuccess INTO argSuccess;
		IF argSuccess = 0 THEN
		    SET done=1;
		END IF;
	    END IF;
	UNTIL done END REPEAT;
    CLOSE cur;

    IF argSuccess = 0 THEN
        ROLLBACK;
    ELSE
        COMMIT;
        CALL wholesale.clear_cart_items(argUserId);
    END IF;
END$$

/*
 * add_business_order
 *
 * DB:     wholesale
 * Tables: wholesale.business_orders
*/
CREATE PROCEDURE add_business_order(argEmail VARCHAR(80), argContents VARCHAR(5000), argPayment TINYINT UNSIGNED, argDeliveryDate VARCHAR(30), argDeliveryTime VARCHAR(30), argRefrigerated BOOLEAN, argWineTotal INTEGER UNSIGNED, argShippingFee INTEGER UNSIGNED, argIPAddress VARCHAR(50), argUserAgent VARCHAR(500))
BEGIN
    INSERT INTO business_orders (customer_email, contents, payment_method, delivery_date, delivery_time, refrigerated, wine_total, shipping_fee, ip_address, user_agent)
    VALUES (argEmail, argContents, argPayment, argDeliveryDate, argDeliveryTime, argRefrigerated, argWineTotal, argShippingFee, argIPAddress, argUserAgent);

    SELECT LAST_INSERT_ID() AS order_id;
END$$

/*
 * get_business_orders
 *
 * DB:     wholesale 
 * Tables: wholesale.business_orders
*/
CREATE PROCEDURE get_business_orders()
BEGIN
    SELECT business_orders.id,
           business_orders.customer_email,
           business_orders.contents,
           business_orders.status,
           business_orders.payment_method,
           business_orders.wine_total,
           business_orders.shipping_fee,
           business_orders.delivery_date,
           business_orders.delivery_time,
           business_orders.refrigerated,
           business_customers.name,
           business_customers.name_phonetic,
           business_customers.post_code,
           business_customers.prefecture,
           business_customers.address,
           business_customers.phone,
           business_customers.comment
    FROM business_orders
    INNER JOIN business_customers
    ON business_orders.customer_email=business_customers.email
    WHERE (business_orders.status >= 0) AND (business_orders.status < 4);
END$$

/*
 * get_business_orders_by_customer
 *
 * DB:     wholesale 
 * Tables: wholesale.business_orders
*/
CREATE PROCEDURE get_business_orders_by_customer(argEmail VARCHAR(80), argStartIndex INTEGER UNSIGNED, argItemCount INTEGER UNSIGNED)
BEGIN
    SELECT business_orders.id,
           business_orders.filemaker_id,
           business_orders.customer_email,
           business_orders.transaction_id,
           business_orders.date_created,
           business_orders.contents,
           business_orders.status,
           business_orders.payment_method,
           business_orders.wine_total,
           business_orders.shipping_fee,
           business_orders.delivery_date,
           business_orders.delivery_time,
           business_orders.refrigerated,
           business_customers.name,
           business_customers.name_phonetic,
           business_customers.post_code,
           business_customers.prefecture,
           business_customers.address,
           business_customers.phone,
           business_customers.comment
    FROM business_orders
    INNER JOIN business_customers
    ON (business_orders.customer_email=argEmail) AND (business_customers.email=argEmail)
    ORDER BY business_orders.date_created DESC LIMIT argStartIndex,argItemCount;
END$$

/*
 * remove_business_order
 *
 * DB:     wholesale
 * Tables: wholesale.business_orders
*/
CREATE PROCEDURE remove_business_order(argId INTEGER UNSIGNED)
BEGIN
    DELETE FROM business_orders WHERE id=argId;
END$$

/*
 * set_business_order_status
 *
 * DB:     wholesale
 * Tables: wholesale.business_orders
*/
CREATE PROCEDURE set_business_order_status(argId INTEGER UNSIGNED, argStatus TINYINT UNSIGNED)
BEGIN
    IF (argStatus >= 0) AND (argStatus < 5) THEN
        UPDATE business_orders SET status=argStatus
        WHERE id=argId;
    END IF;
END$$

/*
 * set_business_order_tracking_id
 *
 * DB:     wholesale
 * Tables: wholesale.business_orders
*/
CREATE PROCEDURE set_business_order_tracking_id(argId INTEGER UNSIGNED, argTrackingId CHAR(14))
BEGIN
    UPDATE business_orders SET transaction_id=argTrackingId
    WHERE id=argId;
END$$

DELIMITER ;

/* Grant stored procedure permissions to admin */
GRANT EXECUTE ON PROCEDURE wholesale.add_business_customer TO 'admin'@'localhost';
GRANT EXECUTE ON PROCEDURE wholesale.update_business_customer_password TO 'admin'@'localhost';
GRANT EXECUTE ON PROCEDURE wholesale.get_business_customer_names TO 'admin'@'localhost';
GRANT EXECUTE ON PROCEDURE wholesale.get_business_customer_count TO 'admin'@'localhost';
GRANT EXECUTE ON PROCEDURE wholesale.get_business_orders TO 'admin'@'localhost';
GRANT EXECUTE ON PROCEDURE wholesale.remove_business_order TO 'admin'@'localhost';
GRANT EXECUTE ON PROCEDURE wholesale.set_business_order_status TO 'admin'@'localhost';
GRANT EXECUTE ON PROCEDURE wholesale.get_business_customer_wine TO 'admin'@'localhost';
GRANT EXECUTE ON PROCEDURE wholesale.set_business_order_tracking_id TO 'admin'@'localhost';

/* Grant stored procedure permissions to b_customer */
GRANT EXECUTE ON PROCEDURE wholesale.get_business_customer TO 'b_customer'@'localhost';
GRANT EXECUTE ON PROCEDURE wholesale.get_business_customer_wines TO 'b_customer'@'localhost';
GRANT EXECUTE ON PROCEDURE wholesale.get_business_customer_wines_by_keyword TO 'b_customer'@'localhost';
GRANT EXECUTE ON PROCEDURE wholesale.get_business_customer_wine TO 'b_customer'@'localhost';
GRANT EXECUTE ON PROCEDURE wholesale.add_to_cart TO 'b_customer'@'localhost';
GRANT EXECUTE ON PROCEDURE wholesale.set_cart_item_quantity TO 'b_customer'@'localhost';
GRANT EXECUTE ON PROCEDURE wholesale.remove_cart_item TO 'b_customer'@'localhost';
GRANT EXECUTE ON PROCEDURE wholesale.clear_cart_items TO 'b_customer'@'localhost';
GRANT EXECUTE ON PROCEDURE wholesale.get_cart_items TO 'b_customer'@'localhost';
GRANT EXECUTE ON PROCEDURE wholesale.get_cart_item_total TO 'b_customer'@'localhost';
GRANT EXECUTE ON PROCEDURE wholesale.checkout_cart_items TO 'b_customer'@'localhost';
GRANT EXECUTE ON PROCEDURE wholesale.add_business_order TO 'b_customer'@'localhost';
GRANT EXECUTE ON PROCEDURE wholesale.get_business_orders_by_customer TO 'b_customer'@'localhost';
GRANT EXECUTE ON PROCEDURE wholesale.update_business_customer_info TO 'b_customer'@'localhost';

GRANT EXECUTE ON PROCEDURE shop.checkout_wine TO 'b_customer'@'localhost';
