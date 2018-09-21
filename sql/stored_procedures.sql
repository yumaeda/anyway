USE shop;

-- Drop procedures first.
DROP PROCEDURE IF EXISTS clear_lucky_bag;
DROP PROCEDURE IF EXISTS add_to_lucky_bag;
DROP PROCEDURE IF EXISTS remove_from_lucky_bag;
DROP PROCEDURE IF EXISTS update_lucky_bag;
DROP PROCEDURE IF EXISTS get_lucky_bag_items;
DROP PROCEDURE IF EXISTS get_lucky_bag_item_count;
DROP PROCEDURE IF EXISTS get_lucky_bag_item_total;
DROP PROCEDURE IF EXISTS checkout_lucky_bag;
DROP PROCEDURE IF EXISTS checkin_lucky_bag;
DROP PROCEDURE IF EXISTS add_to_cart;
DROP PROCEDURE IF EXISTS add_to_cart_force;
DROP PROCEDURE IF EXISTS remove_from_cart;
DROP PROCEDURE IF EXISTS update_cart;
DROP PROCEDURE IF EXISTS get_cart_contents;
DROP PROCEDURE IF EXISTS get_cart_wine_set_total;
DROP PROCEDURE IF EXISTS get_cart_item_count;
DROP PROCEDURE IF EXISTS get_cart_wine_count;
DROP PROCEDURE IF EXISTS get_cart_set_count;
DROP PROCEDURE IF EXISTS get_cart_goods_count;
DROP PROCEDURE IF EXISTS clear_cart;
DROP PROCEDURE IF EXISTS add_shipping;
DROP PROCEDURE IF EXISTS get_shipping;
DROP PROCEDURE IF EXISTS set_customer_info;
DROP PROCEDURE IF EXISTS get_order_contents;
DROP PROCEDURE IF EXISTS set_order_status;
DROP PROCEDURE IF EXISTS set_tracking_id;
DROP PROCEDURE IF EXISTS remove_order;
DROP PROCEDURE IF EXISTS set_shipping_datetime;
DROP PROCEDURE IF EXISTS set_payment_method;
DROP PROCEDURE IF EXISTS get_wines_without_image;
DROP PROCEDURE IF EXISTS get_wine;
DROP PROCEDURE IF EXISTS checkout_wine;
DROP PROCEDURE IF EXISTS checkout_all_wines;
DROP PROCEDURE IF EXISTS checkout_wine_set;
DROP PROCEDURE IF EXISTS checkout_cart;
DROP PROCEDURE IF EXISTS checkin_cart;
DROP PROCEDURE IF EXISTS get_delivered_wines;
DROP PROCEDURE IF EXISTS get_wine_set;
DROP PROCEDURE IF EXISTS get_set_wines;
DROP PROCEDURE IF EXISTS add_wine_set;
DROP PROCEDURE IF EXISTS add_to_wine_set;
DROP PROCEDURE IF EXISTS remove_from_wine_set;
DROP PROCEDURE IF EXISTS remove_wine_set;
DROP PROCEDURE IF EXISTS add_producer;
DROP PROCEDURE IF EXISTS add_wine_image;
DROP PROCEDURE IF EXISTS get_wine_image;
DROP PROCEDURE IF EXISTS add_alt_wine_image;
DROP PROCEDURE IF EXISTS get_producer_detail;
DROP PROCEDURE IF EXISTS get_wine_details;
DROP PROCEDURE IF EXISTS get_related_wine_details;
DROP PROCEDURE IF EXISTS get_producers_by_aoc;
DROP PROCEDURE IF EXISTS get_html_page;
DROP PROCEDURE IF EXISTS add_customer;
DROP PROCEDURE IF EXISTS update_customer_password;
DROP PROCEDURE IF EXISTS get_customer;
DROP PROCEDURE IF EXISTS get_customer_count;
DROP PROCEDURE IF EXISTS get_emails_for_restock_alert;
DROP PROCEDURE IF EXISTS change_producer_name_in_favorite_list;
DROP PROCEDURE IF EXISTS get_favorite_producer;
DROP PROCEDURE IF EXISTS get_favorite_producers_by_email;
DROP PROCEDURE IF EXISTS add_producer_to_favorite_list;
DROP PROCEDURE IF EXISTS remove_producer_from_favorite_list;
DROP PROCEDURE IF EXISTS get_soldout_wines;
DROP PROCEDURE IF EXISTS get_purchased_wines_by_email;
DROP PROCEDURE IF EXISTS add_purchased_wine;


-- Set delimiter to '$$' in order to properly create stored procedures.
DELIMITER $$



/*
 * get_wines
 *
 * TODO: yumaeda Remove this stored procedure since REST API already exists.
 *
 * DB:     shop
 * Tables: wines
*/
CREATE PROCEDURE get_wines(argType VARCHAR(30), argScope VARCHAR(1000), argProducer VARCHAR(300))
BEGIN
    IF (argType != '') AND (argScope != '') AND (argProducer != '') THEN
        SELECT wines.*
        FROM wines
        WHERE (wines.type = argType) AND ((wines.village = argScope) OR (wines.district = argScope) OR (wines.region = argScope)) AND (wines.producer = argProducer COLLATE utf8_unicode_ci) AND (wines.availability = 'Online') AND (wines.apply <> 'DP');
    ELSEIF (argType = '') AND (argScope != '') AND (argProducer != '') THEN
        SELECT wines.*
        FROM wines
        WHERE ((wines.village = argScope) OR (wines.district = argScope) OR (wines.region = argScope)) AND (wines.producer = argProducer COLLATE utf8_unicode_ci) AND (wines.availability = 'Online') AND (wines.apply <> 'DP')
        ORDER BY wines.type, wines.vintage ASC;
    ELSEIF (argType = '') AND (argScope = '') AND (argProducer != '') THEN
        SELECT wines.*
        FROM wines
        WHERE (wines.producer = argProducer COLLATE utf8_unicode_ci) AND (wines.availability = 'Online') AND (wines.apply <> 'DP')
        ORDER BY wines.type, wines.vintage ASC;
    ELSEIF (argType = '') AND (argScope != '') AND (argProducer = '') THEN
        SELECT wines.*
        FROM wines
        WHERE ((wines.village = argScope) OR (wines.district = argScope) OR (wines.region = argScope)) AND (wines.availability = 'Online') AND (wines.apply <> 'DP')
        ORDER BY wines.type, wines.vintage ASC;
    ELSE
        SELECT wines.*
        FROM wines
        WHERE (wines.availability = 'Online') AND (wines.apply <> 'DP');
    END IF;
END$$

GRANT EXECUTE ON PROCEDURE shop.get_wines TO 'reader'@'localhost';
GRANT EXECUTE ON PROCEDURE shop.get_wines TO 'admin'@'localhost';



/*
 * add_to_lucky_bag
 *
 * DB:     shop
 * Tables: lucky_bag_items, wines
*/
CREATE PROCEDURE add_to_lucky_bag(argUserId CHAR(32), argProductId SMALLINT UNSIGNED, argQuantity SMALLINT UNSIGNED)
BEGIN
    DECLARE itemId INTEGER;
    DECLARE cOnline INTEGER;

    SELECT COUNT(*) INTO cOnline FROM wines WHERE (wines.barcode_number=argProductId AND wines.availability='Online');

    IF (cOnline > 0) THEN
        SELECT id INTO itemId FROM lucky_bag_items WHERE (user_session_id=argUserId AND product_id=argProductId);
        IF itemId > 0 THEN
            UPDATE lucky_bag_items SET quantity=(quantity + argQuantity), date_modified=NOW()
            WHERE id=itemId;
        ELSE
            INSERT INTO lucky_bag_items (user_session_id, product_id, quantity) VALUES (argUserId, argProductId, argQuantity);
        END IF;
    END IF;
END$$


/*
 * remove_from_lucky_bag
 *
 * DB:     shop
 * Tables: lucky_bag_items
*/
CREATE PROCEDURE remove_from_lucky_bag(argUserId CHAR(32), argProductId SMALLINT UNSIGNED)
BEGIN
    DELETE FROM lucky_bag_items WHERE user_session_id=argUserId AND product_id=argProductId;
END$$


/*
 * clear_lucky_bag
 *
 * DB:     shop
 * Tables: lucky_bag_items
*/
CREATE PROCEDURE clear_lucky_bag(argUserId CHAR(32))
BEGIN
    DELETE FROM lucky_bag_items WHERE user_session_id=argUserId;
END$$


/*
 * update_lucky_bag()
 *
 * DB:     shop
 * Tables: lucky_bag_items
*/
CREATE PROCEDURE update_lucky_bag(argUserId CHAR(32), argProductId SMALLINT UNSIGNED, argQuantity SMALLINT UNSIGNED)
BEGIN
    IF argQuantity > 0 THEN
        UPDATE lucky_bag_items SET quantity=argQuantity, date_modified=NOW()
        WHERE user_session_id=argUserId AND product_id=argProductId;
    ELSEIF argQuantity = 0 THEN
        CALL remove_from_lucky_bag(argUserId, argProductId);
    END IF;
END$$


/*
 * get_lucky_bag_items()
 *
 * DB:     shop
 * Tables: wines, lucky_bag_items
*/
CREATE PROCEDURE get_lucky_bag_items(argUserId CHAR(32))
BEGIN
    SELECT
        lucky_bag_items.product_id AS barcode_number,
        lucky_bag_items.quantity AS quantity,
        wines.vintage,
        wines.producer_jpn AS producer,
        wines.combined_name_jpn AS name,
        wines.stock,
        wines.price

        FROM lucky_bag_items
        INNER JOIN wines
        ON lucky_bag_items.product_id=wines.barcode_number
        WHERE lucky_bag_items.user_session_id=argUserId;
END$$


/*
 * get_lucky_bag_item_count()
 *
 * DB:     shop
 * Tables: lucky_bag_items
*/
CREATE PROCEDURE get_lucky_bag_item_count(argUserId CHAR(32))
BEGIN
    SELECT SUM(quantity) FROM lucky_bag_items
    WHERE (user_session_id=argUserId);
END$$


/*
 * get_lucky_bag_item_total()
 *
 * DB:     shop
 * Tables: wines, lucky_bag_items
*/
CREATE PROCEDURE get_lucky_bag_item_total(argUserId CHAR(32))
BEGIN
    DECLARE cItem INTEGER;
    DECLARE discountRate FLOAT DEFAULT 0.0;

    SELECT SUM(quantity) INTO cItem FROM lucky_bag_items
    WHERE (user_session_id=argUserId);

    IF ((cItem > 5) AND (cItem < 12)) THEN
        SET discountRate = 0.9;
    ELSEIF (cItem = 12) THEN
        SET discountRate = 0.8;
    ELSE
        SET discountRate = 1.0;
    END IF;

    SELECT
        SUM(lucky_bag_items.quantity * wines.price) AS total_price,
        SUM(lucky_bag_items.quantity * ROUND(wines.price * discountRate, 0)) AS discount_price
    FROM lucky_bag_items
    INNER JOIN wines 
    ON lucky_bag_items.product_id=wines.barcode_number
    WHERE lucky_bag_items.user_session_id=argUserId;
END$$


/*
 * checkout_lucky_bag
 *
 * DB:     shop
 * Tables: lucky_bag_items, wines
 *
 * Check out wines in the specified user's lucky bag.
*/
CREATE PROCEDURE checkout_lucky_bag(argUserId CHAR(32), OUT argSuccess INTEGER)
BEGIN
    DECLARE tmpId INTEGER UNSIGNED;
    DECLARE tmpQty INTEGER UNSIGNED;
    DECLARE done INTEGER DEFAULT 0;
    DECLARE cur CURSOR FOR SELECT product_id, quantity FROM lucky_bag_items WHERE user_session_id=argUserId;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done=1;

    START TRANSACTION;
        OPEN cur;
            REPEAT
                FETCH cur INTO tmpId, tmpQty;
                IF NOT done THEN
                    CALL checkout_wine(tmpId, tmpQty, @fSuccess);

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
    END IF;
END$$


/*
 * checkin_lucky_bag
 *
 * DB:     shop
 * Tables: lucky_bag_items, wines
 *
 * Check in wines in the specified user's lucky bag.
*/
CREATE PROCEDURE checkin_lucky_bag(argUserId CHAR(32), OUT argSuccess INTEGER)
BEGIN
    DECLARE tmpId INTEGER UNSIGNED;
    DECLARE tmpQty INTEGER UNSIGNED;
    DECLARE done INTEGER DEFAULT 0;
    DECLARE cur CURSOR FOR SELECT product_id, quantity FROM lucky_bag_items WHERE user_session_id=argUserId;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done=1;

    START TRANSACTION;
        OPEN cur;
            REPEAT
                FETCH cur INTO tmpId, tmpQty;
                IF NOT done THEN
                    CALL checkout_wine(tmpId, -tmpQty, @fSuccess);

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
    END IF;
END$$


/*
 * add_to_cart
 *
 * DB:     shop
 * Tables: carts, wines
*/
CREATE PROCEDURE add_to_cart(argUserId CHAR(32), argProductId SMALLINT UNSIGNED, argQuantity SMALLINT UNSIGNED, argMemberType SMALLINT UNSIGNED)
BEGIN
    DECLARE cartId INTEGER;
    DECLARE cOnline INTEGER;
    DECLARE cMember INTEGER;
    DECLARE cWineSet INTEGER;

    SELECT COUNT(*) INTO cOnline FROM wines WHERE (wines.barcode_number=argProductId AND wines.availability='Online' AND wines.price > 0);
    SELECT COUNT(*) INTO cMember FROM wines WHERE (wines.barcode_number=argProductId AND wines.availability='Member' AND wines.price > 0);

    IF (argProductId > 50000) THEN
        SELECT COUNT(*) INTO cWineSet FROM wine_sets WHERE (wine_sets.id = (argProductId - 50000));
    END IF;
    
    IF (cOnline > 0) OR (cWineSet > 0) OR ((cMember > 0) AND (argMemberType = 1)) THEN
        SELECT id INTO cartId FROM carts WHERE user_session_id=argUserId AND product_id=argProductId;
        IF cartId > 0 THEN
            UPDATE carts SET quantity=(quantity + argQuantity), date_modified=NOW()
            WHERE id=cartId;
        ELSE
            INSERT INTO carts (user_session_id, product_id, quantity) VALUES (argUserId, argProductId, argQuantity);
        END IF;
    END IF;
END$$


/*
 * add_to_cart_force
 *
 * DB:     shop
 * Tables: carts, wines
*/
CREATE PROCEDURE add_to_cart_force(argUserId CHAR(32), argProductId SMALLINT UNSIGNED, argQuantity SMALLINT UNSIGNED)
BEGIN
    DECLARE cartId INTEGER;
    DECLARE cItem INTEGER;

    SELECT COUNT(*) INTO cItem FROM wines WHERE (wines.barcode_number=argProductId AND wines.price > 0);

    IF (cItem > 0) THEN
        SELECT id INTO cartId FROM carts WHERE user_session_id=argUserId AND product_id=argProductId;
        IF cartId > 0 THEN
            UPDATE carts SET quantity=(quantity + argQuantity), date_modified=NOW()
            WHERE id=cartId;
        ELSE
            INSERT INTO carts (user_session_id, product_id, quantity) VALUES (argUserId, argProductId, argQuantity);
        END IF;
    END IF;
END$$


/*
 * remove_from_cart
 *
 * DB:     shop
 * Tables: carts
*/
CREATE PROCEDURE remove_from_cart(argUserId CHAR(32), argProductId SMALLINT UNSIGNED)
BEGIN
    DELETE FROM carts WHERE user_session_id=argUserId AND product_id=argProductId;
END$$


/*
 * update_cart()
 *
 * DB:     shop
 * Tables: carts
*/
CREATE PROCEDURE update_cart(argUserId CHAR(32), argProductId SMALLINT UNSIGNED, argQuantity SMALLINT UNSIGNED)
BEGIN
    IF argQuantity > 0 THEN
        UPDATE carts SET quantity=argQuantity, date_modified=NOW()
        WHERE user_session_id=argUserId AND product_id=argProductId;
    ELSEIF argQuantity = 0 THEN
        CALL remove_from_cart(argUserId, argProductId);
    END IF;
END$$


/*
 * get_cart_contents()
 *
 * DB:     shop
 * Tables: wines, carts
*/
CREATE PROCEDURE get_cart_contents(argUserId CHAR(32))
BEGIN
    SELECT
        carts.product_id AS barcode_number,
        carts.quantity AS quantity,
        wines.vintage,
        wines.type,
        wines.apply,
        wines.etc,
        wines.producer_jpn AS producer,
        wines.member_price,

        IF ((wines.stock <> ''),
            wines.stock,
            (
                SELECT MIN(wines.stock) FROM set_wines
                INNER JOIN wines
                ON set_wines.barcode_number=wines.barcode_number
                INNER JOIN wine_sets
                ON set_wines.set_id=wine_sets.id
                WHERE carts.product_id = (wine_sets.id + 50000)
            )
        ) AS stock,

        wines.price AS retail_price,

        IF ((wines.price <> ''),
            wines.price,
            wine_sets.set_price
        ) AS price,

        IF ((carts.product_id < 50000),
            wines.combined_name_jpn,
            (
                SELECT wine_sets.name FROM wine_sets
                WHERE carts.product_id = (wine_sets.id + 50000)
            )
        ) AS name

        FROM carts
        LEFT OUTER JOIN wine_sets
        ON carts.product_id=(wine_sets.id + 50000)
        LEFT OUTER JOIN wines
        ON carts.product_id=wines.barcode_number
        WHERE carts.user_session_id=argUserId;
END$$

GRANT EXECUTE ON PROCEDURE shop.get_cart_contents TO 'reader'@'localhost';


/*
 * get_cart_wine_set_total()
 *
 * DB:     shop
 * Tables: carts, wine_sets
*/
CREATE PROCEDURE get_cart_wine_set_total(argUserId CHAR(32))
BEGIN
    SELECT
        SUM(carts.quantity * wine_sets.set_price) AS total_price
    FROM carts
    INNER JOIN wine_sets
    ON carts.product_id = (wine_sets.id + 50000)
    WHERE carts.user_session_id=argUserId;
END$$

GRANT EXECUTE ON PROCEDURE shop.get_cart_wine_set_total TO 'reader'@'localhost';


/*
 * get_cart_item_count()
 *
 * DB:     shop
 * Tables: carts
*/
CREATE PROCEDURE get_cart_item_count(argUserId CHAR(32))
BEGIN
    SELECT SUM(quantity) AS total_quantity
    FROM carts
    WHERE user_session_id=argUserId;
END$$


/*
 * get_cart_wine_count()
 *
 * DB:     shop
 * Tables: carts
*/
CREATE PROCEDURE get_cart_wine_count(argUserId CHAR(32))
BEGIN
    SELECT
        SUM(CASE WHEN ((product_id > 1000) AND (product_id <= 50000))
            THEN
                quantity
            ELSE
                (quantity * (SELECT COUNT(*) FROM set_wines WHERE (set_id + 50000) = product_id))
            END
        )
    FROM carts
    WHERE (user_session_id=argUserId);
END$$


/*
 * get_cart_set_count()
 *
 * DB:     shop
 * Tables: carts
*/
CREATE PROCEDURE get_cart_set_count(argUserId CHAR(32))
BEGIN
    SELECT SUM(CASE WHEN (product_id > 50000) THEN quantity ELSE 0 END)
    FROM carts
    WHERE (user_session_id=argUserId);
END$$


/*
 * get_cart_fortune_box_count()
 *
 * DB:     shop
 * Tables: carts, wines
*/
CREATE PROCEDURE get_cart_fortune_box_count(argUserId CHAR(32))
BEGIN
    SELECT SUM(CASE WHEN ((wines.type = 'FORTUNE BOX') AND (wines.availability = 'Online')) THEN carts.quantity ELSE 0 END)
    FROM carts
    INNER JOIN wines
    ON wines.barcode_number=carts.product_id
    WHERE (user_session_id=argUserId);
END$$
GRANT EXECUTE ON PROCEDURE shop.get_cart_fortune_box_count TO 'reader'@'localhost';


/*
 * get_cart_goods_count()
 *
 * DB:     shop
 * Tables: carts
*/
CREATE PROCEDURE get_cart_goods_count(argUserId CHAR(32))
BEGIN
    SELECT SUM(CASE WHEN ((carts.product_id > 1000) AND (carts.product_id <= 50000) AND (wines.type = 'Goods') AND (wines.availability = 'Online')) THEN carts.quantity ELSE 0 END)
    FROM carts
    INNER JOIN wines
    ON wines.barcode_number=carts.product_id
    WHERE (user_session_id=argUserId);
END$$


/*
 * clear_cart
 *
 * DB:     shop
 * Tables: carts
*/
CREATE PROCEDURE clear_cart(argUserId CHAR(32))
BEGIN
    DELETE FROM carts WHERE user_session_id=argUserId;
END$$


/*
 * add_shipping
 *
 * DB:     shop
 * Tables: shippings
 *
*/
CREATE PROCEDURE add_shipping(argEmail VARCHAR(80), argName VARCHAR(100), argPhonetic VARCHAR(100), argPostCode CHAR(8), argPrefecture VARCHAR(10),
                              argAddress VARCHAR(300), argPhone VARCHAR(13), argDeliveryCompany VARCHAR(30), argDeliveryDate VARCHAR(30), argDeliveryTime VARCHAR(30),
                              argRefrigerated BOOLEAN, argComment VARCHAR(2000), argFee INTEGER UNSIGNED, OUT argId INTEGER)
BEGIN
    INSERT INTO shippings (email, name, phonetic, post_code, prefecture, address, phone, delivery_company, delivery_date, delivery_time, refrigerated, comment, fee)
    VALUES (argEmail, argName, argPhonetic, argPostCode, argPrefecture, argAddress, argPhone, argDeliveryCompany, argDeliveryDate, argDeliveryTime, argRefrigerated, argComment, argFee);

    SELECT LAST_INSERT_ID() INTO argId;
END$$


/*
 * get_shipping
 *
 * DB:     shop
 * Tables: shippings
*/
CREATE PROCEDURE get_shipping(argShippingId INTEGER UNSIGNED)
BEGIN
    SELECT email, name, post_code, prefecture, address, phone, delivery_company, delivery_date, delivery_time, refrigerated, comment, fee
    FROM shippings
    WHERE id = argShippingId;
END$$


/*
 * add_order
 *
 * DB:     shop
 * Tables: orders
*/
CREATE PROCEDURE add_order(argOrderId VARCHAR(30), argShippingId INTEGER UNSIGNED, argContents VARCHAR(1000),
                           argStatus TINYINT UNSIGNED, argPayment TINYINT UNSIGNED, argMemberDiscount TINYINT UNSIGNED, argWineTotal INTEGER UNSIGNED)
BEGIN
    INSERT INTO orders (order_id, shipping_id, contents, status, payment_method, member_discount, wine_total)
    VALUES (argOrderId, argShippingId, argContents, argStatus, argPayment, argMemberDiscount, argWineTotal);
END$$

GRANT EXECUTE ON PROCEDURE shop.add_order TO 'reader'@'localhost';


/*
 * set_customer_info
 *
 * DB:     shop
 * Tables: orders
*/
CREATE PROCEDURE set_customer_info(argOrderId VARCHAR(30), argName VARCHAR(100), argPhonetic VARCHAR(100), argEmail VARCHAR(80), argAddress VARCHAR(500), argPhone VARCHAR(13), argIPAddress1 VARCHAR(50), argIPAddress2 VARCHAR(50), argUserAgent VARCHAR(500))
BEGIN
    UPDATE orders SET customer_name=argName, customer_phonetic=argPhonetic, customer_email=argEmail, customer_address=argAddress, customer_phone=argPhone, ip_address1=argIPAddress1, ip_address2=argIPAddress2, user_agent=argUserAgent
    WHERE order_id=argOrderId;
END$$


/*
 * get_order_contents
 *
 * DB:     shop
 * Tables: orders, shippings
*/
CREATE PROCEDURE get_order_contents(argOrderId VARCHAR(30))
BEGIN
    IF (argOrderId = '00000000-0000000000') THEN
        SELECT orders.order_id, orders.transaction_id, orders.transaction_id2, orders.contents, orders.status, orders.payment_method, orders.member_discount, orders.wine_total,
               orders.customer_name, orders.customer_phonetic, orders.customer_email, orders.customer_address, orders.customer_phone,
               shippings.email, shippings.name, shippings.phonetic, shippings.post_code, shippings.prefecture, shippings.address, shippings.phone,
               shippings.delivery_company, shippings.delivery_date, shippings.delivery_time, shippings.refrigerated, shippings.comment, shippings.fee
        FROM orders
        INNER JOIN shippings
        ON orders.shipping_id=shippings.id
        WHERE (orders.status >= 0) AND (orders.status < 5);
    ELSE
        SELECT orders.order_id, orders.transaction_id, orders.transaction_id2, orders.contents, orders.status, orders.payment_method, orders.member_discount, orders.wine_total,
               orders.customer_name, orders.customer_phonetic, orders.customer_email, orders.customer_address, orders.customer_phone,
               shippings.email, shippings.name, shippings.phonetic, shippings.post_code, shippings.prefecture, shippings.address, shippings.phone,
               shippings.delivery_company, shippings.delivery_date, shippings.delivery_time, shippings.refrigerated, shippings.comment, shippings.fee
        FROM orders
        INNER JOIN shippings
        ON orders.shipping_id=shippings.id
        WHERE orders.order_id=argOrderId;
    END IF;
END$$

GRANT EXECUTE ON PROCEDURE shop.get_order_contents TO 'admin'@'localhost';


/*
 * set_order_status
 *
 * DB:     shop
 * Tables: orders
*/
CREATE PROCEDURE set_order_status(argOrderId VARCHAR(30), argStatus TINYINT UNSIGNED)
BEGIN
    IF (argStatus >= 0) AND (argStatus < 6) THEN
        UPDATE orders SET status=argStatus
        WHERE order_id=argOrderId;
    END IF;
END$$


/*
 * set_order_contents
 *
 * DB:     shop
 * Tables: orders
*/
CREATE PROCEDURE set_order_contents(argOrderId VARCHAR(30), argContents VARCHAR(1000), argWineTotal INTEGER UNSIGNED,
    argTransactionId CHAR(14), argTransactionId2 CHAR(14))
BEGIN
    UPDATE orders SET contents=argContents, wine_total=argWineTotal, transaction_id=argTransactionId, transaction_id2=argTransactionId2
    WHERE (order_id=argOrderId) AND (status < 4);
END$$

GRANT EXECUTE ON PROCEDURE shop.set_order_contents TO 'admin'@'localhost';


/*
 * upate_wine_total
 *
 * DB:     shop
 * Tables: orders
*/
CREATE PROCEDURE update_wine_total(argOrderId VARCHAR(30))
BEGIN
    UPDATE orders SET wine_total=argContents
    WHERE (order_id=argOrderId) AND (status=0);
END$$

GRANT EXECUTE ON PROCEDURE shop.update_wine_total TO 'admin'@'localhost';


/*
 * set_tracking_id
 *
 * DB:     shop
 * Tables: orders
*/
CREATE PROCEDURE set_tracking_id(argOrderId VARCHAR(30), argTrackingId CHAR(14))
BEGIN
    UPDATE orders SET transaction_id=argTrackingId
    WHERE order_id=argOrderId;
END$$


/*
 * remove_order
 *
 * DB:     shop
 * Tables: orders
*/
CREATE PROCEDURE remove_order(argOrderId VARCHAR(30))
BEGIN
    DECLARE shippingId INTEGER;
    SELECT shipping_id INTO shippingId FROM orders WHERE order_id=argOrderId;

    IF shippingId > 0 THEN
        DELETE FROM shippings WHERE id=shippingId;
        DELETE FROM orders WHERE order_id=argOrderId;
    END IF;
END$$


/*
 * set_shipping_datetime
 *
 * DB:     shop
 * Tables: shippings
*/
CREATE PROCEDURE set_shipping_datetime(argOrderId VARCHAR(30), argDeliveryDate VARCHAR(30), argDeliveryTime VARCHAR(30))
BEGIN
    DECLARE shippingId INTEGER;
    SELECT shipping_id INTO shippingId FROM orders WHERE order_id=argOrderId;

    IF shippingId > 0 THEN
        UPDATE shippings SET delivery_date=argDeliveryDate, delivery_time=argDeliveryTime
        WHERE id=shippingId;
    END IF;
END$$


/*
 * set_payment_method
 *
 * DB:     shop
 * Tables: orders
*/
CREATE PROCEDURE set_payment_method(argOrderId VARCHAR(30), argPayment TINYINT UNSIGNED)
BEGIN
    UPDATE orders SET payment_method=argPayment
    WHERE order_id=argOrderId;
END$$


/*
 * get_wine()
 *
 * DB:     shop
 * Tables: wines
 *
 * Get any wine w/ the specified barcode number.
*/
CREATE PROCEDURE get_wine(argBarcode CHAR(6))
BEGIN
    SELECT
        wines.barcode_number,
        wines.type,
        wines.country,
        wines.producer,
        wines.producer_jpn,
        wines.region,
        wines.region_jpn,
        wines.village,
        wines.village_jpn,
        wines.district,
        wines.district_jpn,
        wines.cepage,
        wines.vintage,
        wines.importer,
        wines.catch_copy,
        wines.comment,
        wines.stock,
        wines.apply,
        wines.availability,
        wines.etc,
        wines.price,
        wines.member_price,
        wines.point,
        wine_details.detail AS original_comment,
        wines.glass_price,
        wines.store_price AS restaurant_price,
        wines.capacity,
        wines.combined_name_jpn,
        wines.combined_name
    FROM wines
    LEFT OUTER JOIN wine_details
    ON wines.barcode_number = wine_details.barcode_number
    WHERE wines.barcode_number=argBarcode;
END$$

GRANT EXECUTE ON PROCEDURE shop.get_wine TO 'admin'@'localhost';
GRANT EXECUTE ON PROCEDURE shop.get_wine TO 'reader'@'localhost';
GRANT EXECUTE ON PROCEDURE shop.get_wine TO 'b_customer'@'localhost';


/*
 * checkout_wine
 *
 * DB:     shop
 * Tables: wines
 *
 * Set argQty to the negative value in order to revert the checked-out wines.
 *
 * This procedure locks the specified row if it is called within a transaction.
*/
CREATE PROCEDURE checkout_wine(argBarcode VARCHAR(6), argQty SMALLINT, OUT argSuccess INTEGER)
BEGIN
    DECLARE tmpStock INTEGER;
    SELECT stock INTO tmpStock FROM wines WHERE barcode_number=argBarcode FOR UPDATE;

    IF tmpStock < argQty THEN
        SET argSuccess = 0;
    ELSE
        UPDATE wines SET stock = (stock - argQty) WHERE barcode_number = argBarcode;
        SET argSuccess = 1;
    END IF;
END$$

GRANT EXECUTE ON PROCEDURE shop.checkout_wine TO 'reader'@'localhost';
GRANT EXECUTE ON PROCEDURE shop.checkout_wine TO 'admin'@'localhost';


/*
 * checkout_all_wines
 *
 * DB:     shop
 * Tables: wines
 *
 * Check out all the existing wines.
*/
CREATE PROCEDURE checkout_all_wines(argBarcode VARCHAR(6))
BEGIN
    UPDATE wines SET stock=0 WHERE barcode_number=argBarcode;
END$$

GRANT EXECUTE ON PROCEDURE shop.checkout_all_wines TO 'admin'@'localhost';


/*
 * checkout_wine_set
 *
 * DB:     shop
 * Tables: wines
 *
 * Check out wines in the specified wine set.
*/
CREATE PROCEDURE checkout_wine_set(argSetId INTEGER UNSIGNED, argQty SMALLINT, OUT argSuccess INTEGER)
BEGIN
    DECLARE tmpId INTEGER UNSIGNED;
    DECLARE done INTEGER DEFAULT 0;
    DECLARE cur CURSOR FOR SELECT barcode_number FROM set_wines WHERE set_wines.set_id=argSetId;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done=1;

    START TRANSACTION;
        OPEN cur;
            REPEAT
                FETCH cur INTO tmpId;
                IF NOT done THEN
                    CALL checkout_wine(tmpId, argQty, @fSuccess);
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
    END IF;
END$$


/*
 * checkout_cart
 *
 * DB:     shop
 * Tables: carts, wines, wine_sets, set_wines
 *
 * Check out wines in the specified user's cart.
*/
CREATE PROCEDURE checkout_cart(argUserId CHAR(32), OUT argSuccess INTEGER)
BEGIN
    DECLARE tmpId INTEGER UNSIGNED;
    DECLARE tmpQty INTEGER UNSIGNED;
    DECLARE done INTEGER DEFAULT 0;
    DECLARE cur CURSOR FOR SELECT product_id, quantity FROM carts WHERE user_session_id=argUserId;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done=1;

    START TRANSACTION;
        OPEN cur;
            REPEAT
                FETCH cur INTO tmpId, tmpQty;
                IF NOT done THEN
                    IF (tmpId > 50000) THEN
                        CALL checkout_wine_set(tmpId - 50000, tmpQty, @fSuccess);
                    ELSE
                        CALL checkout_wine(tmpId, tmpQty, @fSuccess);
                    END IF;

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
    END IF;
END$$


/*
 * checkin_cart
 *
 * DB:     shop
 * Tables: carts, wines, wine_sets, set_wines
 *
 * Check in wines in the specified user's cart.
*/
CREATE PROCEDURE checkin_cart(argUserId CHAR(32), OUT argSuccess INTEGER)
BEGIN
    DECLARE tmpId INTEGER UNSIGNED;
    DECLARE tmpQty INTEGER UNSIGNED;
    DECLARE done INTEGER DEFAULT 0;
    DECLARE cur CURSOR FOR SELECT product_id, quantity FROM carts WHERE user_session_id=argUserId;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done=1;

    START TRANSACTION;
        OPEN cur;
            REPEAT
                FETCH cur INTO tmpId, tmpQty;
                IF NOT done THEN
                    IF (tmpId > 50000) THEN
                        CALL checkout_wine_set(tmpId, -tmpQty, @fSuccess);
                    ELSE
                        CALL checkout_wine(tmpId, -tmpQty, @fSuccess);
                    END IF;

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
    END IF;
END$$


/*
 * get_delivered_wines
 *
 * DB:     shop
 * Tables: goods_issues
 *
*/
CREATE PROCEDURE get_delivered_wines(argYear CHAR(4), argMonth CHAR(2), argDate CHAR(2))
BEGIN
    SELECT barcode_number FROM goods_issues
    WHERE date_delivered
    BETWEEN 
    CONCAT(argYear, '-', LPAD(argMonth, 2, '0'), '-', LPAD(argDate, 2, '0'), ' 00:00:00') AND 
    CONCAT(argYear, '-', LPAD(argMonth, 2, '0'), '-', LPAD(argDate, 2, '0'), ' 20:00:00')
    ORDER BY date_delivered ASC;
END$$


/*
 * get_purchased_wines_by_email
 *
 * DB:     shop
 * Tables: purchased_items, wines
 *
*/
CREATE PROCEDURE get_purchased_wines_by_email(argEmail VARCHAR(80))
BEGIN
    SELECT
	purchased_items.barcode_number,
	purchased_items.quantity,
	wines.vintage,
	wines.combined_name_jpn AS full_name_jpn,
	wines.producer_jpn,
	wines.country,
	wines.type
    FROM purchased_items
    INNER JOIN wines
    ON purchased_items.barcode_number=wines.barcode_number
    WHERE purchased_items.customer_id = argEmail
    ORDER BY purchased_items.purchased, purchased_items.barcode_number;
END$$



/*
 * add_purchased_wine
 *
 * DB:     shop
 * Tables: purchased_items
 *
*/
CREATE PROCEDURE add_purchased_wine(argOrderId VARCHAR(30), argCustomerId VARCHAR(80), argBarcode CHAR(4), argQuantity SMALLINT UNSIGNED)
BEGIN
    INSERT INTO purchased_items (order_id, customer_id, barcode_number, quantity)
    VALUES (argOrderId, argCustomerId, argBarcode, argQuantity);
END$$


/*
 * get_set_wines [OK]
 *
 * DB:     shop
 * Tables: set_wines
 *
 * TODO: yumaeda Remove this stored procedure since REST API already exists.
*/
CREATE PROCEDURE get_set_wines(argSetId INTEGER UNSIGNED)
BEGIN
    SELECT barcode_number, comment FROM set_wines WHERE set_id = argSetId;
END$$


/*
 * get_wine_set
 *
 * DB:     shop
 * Tables: set_wines, wines
 *
*/
CREATE PROCEDURE get_wine_set(argSetId INTEGER UNSIGNED)
BEGIN
    SELECT
        wine_sets.id, 
        wine_sets.name,
        wine_sets.comment,
        wine_sets.type,
        wine_sets.set_price,
        SUM(wines.price) AS price,
        MIN(wines.stock) AS stock 
    FROM set_wines 
    INNER JOIN wines     ON set_wines.barcode_number=wines.barcode_number
    INNER JOIN wine_sets ON set_wines.set_id=wine_sets.id
    WHERE set_wines.set_id=argSetId;
END$$

GRANT EXECUTE ON PROCEDURE shop.get_wine_set TO 'reader'@'localhost';
GRANT EXECUTE ON PROCEDURE shop.get_wine_set TO 'admin'@'localhost';


/*
 * add_wine_set
 *
 * DB:     shop
 * Tables: wine_sets
 *
*/
CREATE PROCEDURE add_wine_set(argName VARCHAR(500), argType TINYINT UNSIGNED, argComment VARCHAR(5000), argSetPrice INTEGER UNSIGNED, OUT argId INTEGER)
BEGIN
    INSERT INTO wine_sets (name, comment, type, set_price)
    VALUES (argName, argComment, argType, argSetPrice);

    SELECT LAST_INSERT_ID() INTO argId;
END$$

GRANT EXECUTE ON PROCEDURE shop.add_wine_set TO 'admin'@'localhost';


/*
 * update_wine_set
 *
 * DB:     shop
 * Tables: wine_sets
 *
*/
CREATE PROCEDURE update_wine_set(argSetId INTEGER UNSIGNED, argName VARCHAR(500), argType TINYINT UNSIGNED, argComment VARCHAR(5000), argSetPrice INTEGER UNSIGNED)
BEGIN
    UPDATE wine_sets SET name=argName, type=argType, comment=argComment, set_price=argSetPrice
    WHERE wine_sets.id = argSetId;
END$$

GRANT EXECUTE ON PROCEDURE shop.update_wine_set TO 'admin'@'localhost';


/*
 * add_to_wine_set
 *
 * DB:     shop
 * Tables: set_wines
 *
*/
CREATE PROCEDURE add_to_wine_set(argSetId INTEGER UNSIGNED, argBarcode CHAR(4))
BEGIN
    INSERT INTO set_wines (set_id, barcode_number) VALUES (argSetId, argBarcode);
END$$


/*
 * remove_from_wine_set
 *
 * DB:     shop
 * Tables: set_wines
 *
*/
CREATE PROCEDURE remove_from_wine_set(argSetId INTEGER UNSIGNED, argBarcode VARCHAR(6))
BEGIN
    IF (argBarcode = '000000') THEN
        DELETE FROM set_wines
        WHERE (set_id=argSetId);
    ELSE
        DELETE FROM set_wines
        WHERE (set_id=argSetId AND barcode_number=argBarcode);
    END IF;
END$$

GRANT EXECUTE ON PROCEDURE shop.remove_from_wine_set TO 'admin'@'localhost';


/*
 * remove_wine_set
 *
 * DB:     shop
 * Tables: wine_sets
 *
*/
CREATE PROCEDURE remove_wine_set(argSetId INTEGER UNSIGNED)
BEGIN
    DELETE FROM wine_sets WHERE id=argSetId;
END$$


/*
 * remove_producer
 *
 * DB:     shop
 * Tables: producers
*/
CREATE PROCEDURE remove_producer(argName VARCHAR(300))
BEGIN
    DELETE FROM producers
    WHERE name=argName;
END$$


/*
 * add_producer
 *
 * DB:     shop
 * Tables: producers
*/
CREATE PROCEDURE add_producer(argName VARCHAR(300), argJpnName VARCHAR(900), argShortName VARCHAR(300), argShortJpnName VARCHAR(900), argCountry VARCHAR(30),
                              argRegion VARCHAR(250), argJpnRegion VARCHAR(250), argDistrict VARCHAR(250), argJpnDistrict VARCHAR(750), argVillage VARCHAR(250),
                              argJpnVillage VARCHAR(750), argHomePage VARCHAR(1000), argFoundedYear VARCHAR(200), argHeadquarter VARCHAR(250),
                              argJpnHeadquarter VARCHAR(750), argFamilyHead VARCHAR(300), argJpnFamilyHead VARCHAR(1000), argFieldArea VARCHAR(200),
                              argImporter VARCHAR(300), argHistoryDetail VARCHAR(50000), argFieldDetail VARCHAR(50000), argFermentationDetail VARCHAR(50000),
                              argOriginalContents VARCHAR(50000), argIsOriginal BOOLEAN, argIsMulti BOOLEAN)
BEGIN
    DECLARE cProducer INTEGER;

    SELECT COUNT(*) INTO cProducer FROM producers WHERE (producers.name = argName COLLATE utf8_unicode_ci);
    IF (cProducer = 0) THEN
        INSERT INTO producers (name, name_jpn, short_name, short_name_jpn, country, region, region_jpn, district, district_jpn, village, village_jpn, home_page, founded_year, headquarter, headquarter_jpn, family_head, family_head_jpn, field_area, importer, history_detail, field_detail, fermentation_detail, original_contents, is_original, is_multi) VALUES (argName, argJpnName, argShortName, argShortJpnName, argCountry, argRegion, argJpnRegion, argDistrict, argJpnDistrict, argVillage, argJpnVillage, argHomePage, argFoundedYear, argHeadquarter, argJpnHeadquarter, argFamilyHead, argJpnFamilyHead, argFieldArea, argImporter, argHistoryDetail, argFieldDetail, argFermentationDetail, argOriginalContents, argIsOriginal, argIsMulti);
    ELSE
        UPDATE producers SET
            name_jpn=argJpnName, 
            short_name=argShortName, 
            short_name_jpn=argShortJpnName, 
            country=argCountry, 
            region=argRegion, 
            region_jpn=argJpnRegion, 
            district=argDistrict, 
            district_jpn=argJpnDistrict, 
            village=argVillage, 
            village_jpn=argJpnVillage, 
            home_page=argHomePage, 
            founded_year=argFoundedYear, 
            headquarter=argHeadquarter, 
            headquarter_jpn=argJpnHeadquarter, 
            family_head=argFamilyHead, 
            family_head_jpn=argJpnFamilyHead, 
            field_area=argFieldArea, 
            importer=argImporter, 
            history_detail=argHistoryDetail,
            field_detail=argFieldDetail,
            fermentation_detail=argFermentationDetail,
            original_contents=argOriginalContents,
            is_original=argIsOriginal,
            is_multi=argIsMulti
        WHERE (name = argName COLLATE utf8_unicode_ci);
    END IF;
END$$


/*
 * add_wine_detail
 *
 * DB:     shop
 * Tables: wine_details 
 *
 * If the specified barcode number, except for '0000', already exist, no record will be updated.
*/
CREATE PROCEDURE add_wine_detail(argProducer VARCHAR(300), argBarcode VARCHAR(6), argDetail mediumtext)
BEGIN
    DECLARE cBarcode INTEGER;

    SELECT COUNT(*) INTO cBarcode FROM wine_details WHERE (barcode_number <> '0000') AND (barcode_number = argBarcode);
    IF (cBarcode = 0) THEN
        INSERT INTO wine_details(producer, barcode_number, detail) VALUES (argProducer, argBarcode, argDetail);
    ELSE
        UPDATE wine_details SET
            producer=argProducer,
            detail=argDetail
        WHERE barcode_number=argBarcode;
    END IF;
END$$

GRANT EXECUTE ON PROCEDURE shop.add_wine_detail TO 'admin'@'localhost';


/*
 * get_producer_detail
 *
 * TODO: yumaeda Remove this stored procedure since REST API already exists.
 *
 * DB:     shop
 * Tables: producers
*/
CREATE PROCEDURE get_producer_detail(argName VARCHAR(300))
BEGIN
    IF (argName = '') THEN
        SELECT * FROM producers
        ORDER BY producers.name;
    ELSE
        SELECT * FROM producers
        WHERE (name = argName COLLATE utf8_unicode_ci);
    END IF;
END$$


/*
 * get_related_wine_details
 *
 * DB:     shop
 * Tables: producers
*/
CREATE PROCEDURE get_related_wine_details(argProducer VARCHAR(300))
BEGIN
    SELECT
        wines.producer,
        wines.barcode_number,
        wines.stock,
        wines.availability,
        wines.etc,
        wines.apply,
        wines.combined_name,
        wines.combined_name_jpn,
        wines.type,
        wines.cepage,
        wines.capacity,
        wines.member_price,
        wines.price,
        wines.vintage,
        wines.importer,
        wines.point,
        wines.country,
        wines.region,
        wine_details.detail
    FROM wines
    INNER JOIN related_producers
    ON (related_producers.name = wines.producer COLLATE utf8_unicode_ci)
    LEFT OUTER JOIN wine_details
    ON wines.barcode_number = wine_details.barcode_number
    WHERE (related_producers.parent = argProducer COLLATE utf8_unicode_ci) AND (wines.apply <> 'DP')
    ORDER BY wines.type ASC, wines.price ASC, wines.village ASC, wines.rating ASC, wines.name ASC, wines.vintage ASC;
END$$


/*
 * get_wine_details
 *
 * TODO: yumaeda Remove this stored procedure since REST API already exists.
 *
 * DB:     shop
 * Tables: producers
*/
CREATE PROCEDURE get_wine_details(argProducer VARCHAR(300))
BEGIN
    SELECT
        wines.producer,
        wines.barcode_number,
        wines.stock,
        wines.availability,
        wines.etc,
        wines.apply,
        wines.combined_name,
        wines.combined_name_jpn,
        wines.type,
        wines.cepage,
        wines.capacity,
        wines.member_price,
        wines.price,
        wines.vintage,
        wines.importer,
        wines.point,
        wines.country,
        wines.region,
        wine_details.detail
        FROM wines
    LEFT OUTER JOIN wine_details
    ON wines.barcode_number = wine_details.barcode_number
    WHERE (wines.producer = argProducer COLLATE utf8_unicode_ci) AND (wines.apply <> 'DP') AND (wines.stock > 0)
    ORDER BY wines.type ASC, wines.price ASC, wines.village ASC, wines.rating ASC, wines.name ASC, wines.vintage ASC;
END$$


/*
 * get_producers_by_aoc
 *
 * DB:     shop
 * Tables: producers
*/
CREATE PROCEDURE get_producers_by_aoc(argRegion VARCHAR(250), argDistrict VARCHAR(250), argVillage VARCHAR(250))
BEGIN
    SELECT short_name, name, short_name_jpn, name_jpn FROM producers
    WHERE (region = argRegion) AND (district = argDistrict) AND (village = argVillage)
    ORDER BY short_name;
END$$


/*
 * get_html_page
 *
 * DB:     shop
 * Tables: html_pages
*/
CREATE PROCEDURE get_html_page(argCountry VARCHAR(30), argRegion VARCHAR(250), argDistrict VARCHAR(250), argVillage VARCHAR(250))
BEGIN
    SELECT contents FROM html_pages
    WHERE (country = argCountry) AND (region = argRegion) AND (district = argDistrict) AND (village = argVillage);
END$$


/*
* add_customer
*
* DB:     shop
* Tables: customers
*/
CREATE PROCEDURE add_customer(argEmail VARCHAR(80), argHash VARCHAR(500), argLastName VARCHAR(50),
                              argFirstName VARCHAR(50), argLastNamePhonetic VARCHAR(50), argFirstNamePhonetic VARCHAR(50), argDateOfBirth VARCHAR(50),
                              argPostCode CHAR(8), argPrefecture VARCHAR(10), argAddress VARCHAR(300), argPhone VARCHAR(13))
BEGIN
    INSERT INTO customers(email, hash, last_name, first_name, last_name_phonetic, first_name_phonetic, date_of_birth, post_code, prefecture, address, phone)
    VALUES (argEmail, argHash, argLastName, argFirstName, argLastNamePhonetic, argFirstNamePhonetic, argDateOfBirth, argPostCode, argPrefecture, argAddress, argPhone);
END$$


/*
* update_customer_password
*
* DB:     shop
* Tables: customers
*/
CREATE PROCEDURE update_customer_password(argEmail VARCHAR(80), argHash VARCHAR(500))
BEGIN
    UPDATE customers SET hash=argHash
    WHERE email=argEmail LIMIT 1;
END$$


/*
 * update_member_info
 *
 * DB:     shop
 * Tables: customers
*/
CREATE PROCEDURE update_member_info(argEmail VARCHAR(80), argPostCode CHAR(8), argPrefecture VARCHAR(10), argAddress VARCHAR(300), argPhone VARCHAR(13))
BEGIN
    UPDATE customers SET post_code=argPostCode, prefecture=argPrefecture, address=argAddress, phone=argPhone
    WHERE email=argEmail LIMIT 1;
END$$


/*
* get_customer
*
* DB:     shop
* Tables: customers
*/
CREATE PROCEDURE get_customer(argEmail VARCHAR(80))
BEGIN
    SELECT * FROM customers WHERE email=argEmail;
END$$


/*
* get_customer_count
*
* DB:     shop
* Tables: customers
*/
CREATE PROCEDURE get_customer_count(argEmail VARCHAR(80))
BEGIN
    SELECT COUNT(*) FROM customers WHERE email=argEmail;
END$$


/*
* refresh_access_token
*
* DB:     shop
* Tables: access_tokens
*/
CREATE PROCEDURE refresh_access_token(argEmail VARCHAR(80), argToken CHAR(64))
BEGIN
    REPLACE INTO access_tokens (email, token, date_expires)
    VALUES (argEmail, argToken, DATE_ADD(NOW(), INTERVAL 15 MINUTE));
END$$


/*
* remove_access_token
*
* DB:     shop
* Tables: access_tokens
*/
CREATE PROCEDURE remove_access_token(argToken CHAR(64))
BEGIN
    DELETE FROM access_tokens
    WHERE token=argToken;
END$$


/*
* get_user_id_by_token
*
* DB:     shop
* Tables: access_tokens
*/
CREATE PROCEDURE get_user_id_by_token(argToken CHAR(64))
BEGIN
    SELECT email FROM access_tokens
    WHERE token=argToken AND date_expires > NOW();
END$$


/*
* get_soldout_wines
*
* DB:     shop
* Tables: stock_records, wines
*/
CREATE PROCEDURE get_soldout_wines()
BEGIN
    SELECT DISTINCT stock_records.barcode_number,
        stock_records.stock_date,
        wines.barcode_number,
        wines.type,
        wines.vintage,
        wines.producer,
        wines.producer_jpn,
        wines.combined_name AS name,
        wines.combined_name_jpn AS name_jpn
    FROM stock_records
    INNER JOIN wines
    ON wines.barcode_number = stock_records.barcode_number
    WHERE
        (stock_records.type = '出庫') AND (stock_records.stock_date > DATE_SUB(NOW(), INTERVAL 1 WEEK)) AND (stock_records.stock = 0) AND
	(wines.type <> 'Food') AND (wines.type <> ''))
    ORDER BY stock_records.id;
END$$


/*
* get_restocked_wines
*
* DB:     shop
* Tables: stock_records, wines
*/
CREATE PROCEDURE get_restocked_wines()
BEGIN
    SELECT DISTINCT stock_records.barcode_number,
        wines.vintage,
        wines.producer,
        wines.producer_jpn,
        wines.combined_name AS name,
        wines.combined_name_jpn AS name_jpn
    FROM stock_records
    INNER JOIN wines
    ON wines.barcode_number = stock_records.barcode_number
    WHERE
        (stock_records.type = '入庫') AND (stock_date = SUBDATE(CURDATE(), 1)) AND
        (wines.availability = 'Online') AND (wines.type <> 'Food') AND (wines.apply <> 'DP') AND (wines.stock > 0)
    ORDER BY wines.producer, wines.combined_name, wines.vintage;
END$$


/*
* get_emails_for_restock_alert
*
* DB:     shop
* Tables: favorite_producers
*/
CREATE PROCEDURE get_emails_for_restock_alert()
BEGIN
    SELECT DISTINCT email FROM favorite_producers;
END$$


/*
* get_favorite_producers_by_email
*
* DB:     shop
* Tables: favorite_producers
*/
CREATE PROCEDURE get_favorite_producers_by_email(argEmail VARCHAR(80))
BEGIN
    SELECT DISTINCT producer FROM favorite_producers WHERE email = argEmail
    ORDER BY producer ASC;
END$$


/*
* get_favorite_producer
*
* DB:     shop
* Tables: favorite_producers
*/
CREATE PROCEDURE get_favorite_producer(argEmail VARCHAR(80), argProducer VARCHAR(300))
BEGIN
    SELECT * FROM favorite_producers
    WHERE (email = argEmail) AND (producer = argProducer COLLATE utf8_unicode_ci)
    LIMIT 1;
END$$


/*
* add_producer_to_favorite_list
*
* DB:     shop
* Tables: favorite_producers
*/
CREATE PROCEDURE add_producer_to_favorite_list(argEmail VARCHAR(80), argProducer VARCHAR(300))
BEGIN
    DECLARE cMatch INTEGER;

    SELECT COUNT(*) INTO cMatch FROM favorite_producers WHERE (email = argEmail) AND (producer = argProducer COLLATE utf8_unicode_ci);

    IF (cMatch = 0) THEN
        INSERT INTO favorite_producers (email, producer) VALUES (argEmail, argProducer);
    END IF;
END$$


/*
* remove_producer_from_favorite_list
*
* DB:     shop
* Tables: favorite_producers
*/
CREATE PROCEDURE remove_producer_from_favorite_list(argEmail VARCHAR(80), argProducer VARCHAR(300))
BEGIN
    DELETE FROM favorite_producers WHERE (email = argEmail) AND (producer = argProducer COLLATE utf8_unicode_ci);
END$$


/*
* change_producer_name_in_favorite_list
*
* DB:     shop
* Tables: favorite_producers
*/
CREATE PROCEDURE change_producer_name_in_favorite_list(argOldProducer VARCHAR(300), argNewProducer VARCHAR(300))
BEGIN
    UPDATE favorite_producers SET producer=argNewProducer
    WHERE (producer = argOldProducer COLLATE utf8_unicode_ci);
END$$


/*
 * add_wine_image
 *
 * DB:     shop
 * Tables: wine_images
*/
CREATE PROCEDURE add_wine_image(argProducer VARCHAR(300), argType VARCHAR(30), argName VARCHAR(500), argFileName VARCHAR(100))
BEGIN
    DECLARE cFile INTEGER;

    SELECT COUNT(*) INTO cFile FROM wine_images WHERE (file_name = argFileName);
    IF (cFile = 0) THEN
        INSERT INTO wine_images(producer, type, name, file_name) VALUES (argProducer, argType, argName, argFileName);
    ELSE
        UPDATE wine_images SET producer=argProducer, type=argType, name=argName
        WHERE (file_name=argFileName);
    END IF;
END$$


/*
 * get_wine_image
 *
 * DB:     shop
 * Tables: wine_images
*/
CREATE PROCEDURE get_wine_image(argProducer VARCHAR(300), argType VARCHAR(30), argName VARCHAR(500))
BEGIN
    SELECT file_name FROM wine_images
    WHERE (producer = argProducer COLLATE utf8_unicode_ci) AND (type = argType) AND (name = argName)
    ORDER BY file_name DESC LIMIT 1;
END$$


-- Set delimiter back to ';'.
DELIMITER ;

-- Grant privillages to 'reader'@'localhost
GRANT EXECUTE ON PROCEDURE shop.clear_lucky_bag TO 'reader'@'localhost';
GRANT EXECUTE ON PROCEDURE shop.add_to_lucky_bag TO 'reader'@'localhost';
GRANT EXECUTE ON PROCEDURE shop.remove_from_lucky_bag TO 'reader'@'localhost';
GRANT EXECUTE ON PROCEDURE shop.update_lucky_bag TO 'reader'@'localhost';
GRANT EXECUTE ON PROCEDURE shop.get_lucky_bag_items TO 'reader'@'localhost';
GRANT EXECUTE ON PROCEDURE shop.get_lucky_bag_item_count TO 'reader'@'localhost';
GRANT EXECUTE ON PROCEDURE shop.get_lucky_bag_item_total TO 'reader'@'localhost';
GRANT EXECUTE ON PROCEDURE shop.checkout_lucky_bag TO 'reader'@'localhost';
GRANT EXECUTE ON PROCEDURE shop.checkin_lucky_bag TO 'reader'@'localhost';
GRANT EXECUTE ON PROCEDURE shop.add_to_cart TO 'reader'@'localhost';
GRANT EXECUTE ON PROCEDURE shop.add_to_cart_force TO 'reader'@'localhost';
GRANT EXECUTE ON PROCEDURE shop.remove_from_cart TO 'reader'@'localhost';
GRANT EXECUTE ON PROCEDURE shop.update_cart TO 'reader'@'localhost';
GRANT EXECUTE ON PROCEDURE shop.get_cart_item_count TO 'reader'@'localhost';
GRANT EXECUTE ON PROCEDURE shop.get_cart_wine_count TO 'reader'@'localhost';
GRANT EXECUTE ON PROCEDURE shop.get_cart_set_count TO 'reader'@'localhost';
GRANT EXECUTE ON PROCEDURE shop.get_cart_goods_count TO 'reader'@'localhost';
GRANT EXECUTE ON PROCEDURE shop.clear_cart TO 'reader'@'localhost';
GRANT EXECUTE ON PROCEDURE shop.checkout_wine_set TO 'reader'@'localhost';
GRANT EXECUTE ON PROCEDURE shop.checkout_cart TO 'reader'@'localhost';
GRANT EXECUTE ON PROCEDURE shop.checkin_cart TO 'reader'@'localhost';
GRANT EXECUTE ON PROCEDURE shop.add_shipping TO 'reader'@'localhost';
GRANT EXECUTE ON PROCEDURE shop.set_customer_info TO 'reader'@'localhost';
GRANT EXECUTE ON PROCEDURE shop.get_set_wines TO 'reader'@'localhost';
GRANT EXECUTE ON PROCEDURE shop.get_producer_detail TO 'reader'@'localhost';
GRANT EXECUTE ON PROCEDURE shop.get_wine_details TO 'reader'@'localhost';
GRANT EXECUTE ON PROCEDURE shop.get_related_wine_details TO 'reader'@'localhost';
GRANT EXECUTE ON PROCEDURE shop.get_producers_by_aoc TO 'reader'@'localhost';
GRANT EXECUTE ON PROCEDURE shop.get_html_page TO 'reader'@'localhost';
GRANT EXECUTE ON PROCEDURE shop.get_customer TO 'reader'@'localhost';
GRANT EXECUTE ON PROCEDURE shop.get_customer_count TO 'reader'@'localhost';
GRANT EXECUTE ON PROCEDURE shop.add_customer TO 'reader'@'localhost';
GRANT EXECUTE ON PROCEDURE shop.update_member_info TO 'reader'@'localhost';
GRANT EXECUTE ON PROCEDURE shop.update_customer_password TO 'reader'@'localhost';
GRANT EXECUTE ON PROCEDURE shop.update_member_info TO 'reader'@'localhost';
GRANT EXECUTE ON PROCEDURE shop.refresh_access_token TO 'reader'@'localhost';
GRANT EXECUTE ON PROCEDURE shop.remove_access_token TO 'reader'@'localhost';
GRANT EXECUTE ON PROCEDURE shop.get_user_id_by_token TO 'reader'@'localhost';
GRANT EXECUTE ON PROCEDURE shop.get_purchased_wines_by_email TO 'reader'@'localhost';
GRANT EXECUTE ON PROCEDURE shop.get_favorite_producer TO 'reader'@'localhost';
GRANT EXECUTE ON PROCEDURE shop.get_favorite_producers_by_email TO 'reader'@'localhost';
GRANT EXECUTE ON PROCEDURE shop.add_producer_to_favorite_list TO 'reader'@'localhost';
GRANT EXECUTE ON PROCEDURE shop.remove_producer_from_favorite_list TO 'reader'@'localhost';

-- Grant privillages to 'admin'@'localhost
GRANT EXECUTE ON PROCEDURE shop.set_order_status TO 'admin'@'localhost';
GRANT EXECUTE ON PROCEDURE shop.set_tracking_id TO 'admin'@'localhost';
GRANT EXECUTE ON PROCEDURE shop.remove_order TO 'admin'@'localhost';
GRANT EXECUTE ON PROCEDURE shop.set_shipping_datetime TO 'admin'@'localhost';
GRANT EXECUTE ON PROCEDURE shop.set_payment_method TO 'admin'@'localhost';
GRANT EXECUTE ON PROCEDURE shop.checkout_wine_set TO 'admin'@'localhost';
GRANT EXECUTE ON PROCEDURE shop.get_delivered_wines TO 'admin'@'localhost';
GRANT EXECUTE ON PROCEDURE shop.remove_producer TO 'admin'@'localhost';
GRANT EXECUTE ON PROCEDURE shop.add_producer TO 'admin'@'localhost';
GRANT EXECUTE ON PROCEDURE shop.get_producer_detail TO 'admin'@'localhost';
GRANT EXECUTE ON PROCEDURE shop.get_wine_details TO 'admin'@'localhost';
GRANT EXECUTE ON PROCEDURE shop.get_related_wine_details TO 'admin'@'localhost';
GRANT EXECUTE ON PROCEDURE shop.add_wine_image TO 'admin'@'localhost';
GRANT EXECUTE ON PROCEDURE shop.get_wine_image TO 'admin'@'localhost';
GRANT EXECUTE ON PROCEDURE shop.get_restocked_wines TO 'admin'@'localhost';
GRANT EXECUTE ON PROCEDURE shop.get_emails_for_restock_alert TO 'admin'@'localhost';
GRANT EXECUTE ON PROCEDURE shop.get_favorite_producers_by_email TO 'admin'@'localhost';
GRANT EXECUTE ON PROCEDURE shop.change_producer_name_in_favorite_list TO 'admin'@'localhost';
GRANT EXECUTE ON PROCEDURE shop.add_to_wine_set TO 'admin'@'localhost';
GRANT EXECUTE ON PROCEDURE shop.remove_wine_set TO 'admin'@'localhost';
GRANT EXECUTE ON PROCEDURE shop.add_purchased_wine TO 'admin'@'localhost';

-- Grant privillages to 'mitsugetsu'@'localhost
GRANT EXECUTE ON PROCEDURE shop.get_soldout_wines TO 'mitsugetsu'@'localhost';
GRANT EXECUTE ON PROCEDURE shop.get_wines_by_keyword TO 'reader'@'localhost';
