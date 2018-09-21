DELIMITER $$

/*
 * get_preorder_wine()
 *
 * DB:     shop
 * Tables: preorder_wines
 *
 * Get any wine w/ the specified barcode number.
*/
CREATE PROCEDURE get_preorder_wine(argBarcode INTEGER UNSIGNED)
BEGIN
    SELECT * FROM preorder_wines
    WHERE barcode_number=argBarcode;
END$$
GRANT EXECUTE ON PROCEDURE shop.get_preorder_wine TO 'reader'@'localhost';
GRANT EXECUTE ON PROCEDURE shop.get_preorder_wine TO 'admin'@'localhost';

/*
 * get_preorder_cart_wine_count()
 *
 * DB:     shop
 * Tables: preorder_carts
*/
CREATE PROCEDURE get_preorder_cart_wine_count(argUserId CHAR(32))
BEGIN
    SELECT SUM(quantity) FROM preorder_carts
    WHERE (user_session_id=argUserId);
END$$
GRANT EXECUTE ON PROCEDURE shop.get_preorder_cart_wine_count TO 'reader'@'localhost';

/*
 * get_preordered_wines()
 *
 * DB:     shop
 * Tables: preorder_wines
*/
CREATE PROCEDURE get_preordered_wines()
BEGIN
    SELECT
        barcode_number,
        type,
        vintage,
        combined_name,
        producer,
        capacity1,
        (initial_stock - stock) AS sold_qty
    FROM preorder_wines
    WHERE initial_stock > stock
    ORDER BY barcode_number ASC;
END$$
GRANT EXECUTE ON PROCEDURE shop.get_preordered_wines TO 'admin'@'localhost';

/*
 * add_to_preorder_cart
 *
 * DB:     shop
 * Tables: preorder_carts, preorder_wines
*/
CREATE PROCEDURE add_to_preorder_cart(argUserId CHAR(32), argProductId INTEGER UNSIGNED, argQuantity SMALLINT UNSIGNED)
BEGIN
    DECLARE cartId INTEGER;

    SELECT id INTO cartId FROM preorder_carts WHERE user_session_id=argUserId AND product_id=argProductId;
    IF cartId > 0 THEN
        UPDATE preorder_carts SET quantity=(quantity + argQuantity), date_modified=NOW()
        WHERE id=cartId;
    ELSE
        INSERT INTO preorder_carts (user_session_id, product_id, quantity) VALUES (argUserId, argProductId, argQuantity);
    END IF;
END$$
GRANT EXECUTE ON PROCEDURE shop.add_to_preorder_cart TO 'reader'@'localhost';

/*
 * remove_from_preorder_cart
 *
 * DB:     shop
 * Tables: preorder_carts
*/
CREATE PROCEDURE remove_from_preorder_cart(argUserId CHAR(32), argProductId INTEGER UNSIGNED)
BEGIN
    DELETE FROM preorder_carts WHERE user_session_id=argUserId AND product_id=argProductId;
END$$
GRANT EXECUTE ON PROCEDURE shop.remove_from_preorder_cart TO 'reader'@'localhost';

/*
 * clear_preorder_cart
 *
 * DB:     shop
 * Tables: preorder_carts
*/
CREATE PROCEDURE clear_preorder_cart(argUserId CHAR(32))
BEGIN
    DELETE FROM preorder_carts WHERE user_session_id=argUserId;
END$$
GRANT EXECUTE ON PROCEDURE shop.clear_preorder_cart TO 'reader'@'localhost';

/*
 * update_preorder_cart()
 *
 * DB:     shop
 * Tables: preorder_carts
*/
CREATE PROCEDURE update_preorder_cart(argUserId CHAR(32), argProductId INTEGER UNSIGNED, argQuantity SMALLINT UNSIGNED)
BEGIN
    IF argQuantity > 0 THEN
        UPDATE preorder_carts SET quantity=argQuantity, date_modified=NOW()
        WHERE user_session_id=argUserId AND product_id=argProductId;
    ELSEIF argQuantity = 0 THEN
        CALL remove_from_preorder_cart(argUserId, argProductId);
    END IF;
END$$
GRANT EXECUTE ON PROCEDURE shop.update_preorder_cart TO 'reader'@'localhost';

/*
 * get_preorder_cart_contents()
 *
 * DB:     shop
 * Tables: preorder_wines, preorder_carts
*/
CREATE PROCEDURE get_preorder_cart_contents(argUserId CHAR(32))
BEGIN
    SELECT
        preorder_carts.product_id AS barcode_number,
        preorder_wines.type,
        preorder_wines.vintage,
        preorder_wines.combined_name,
        preorder_wines.producer,
        preorder_carts.quantity AS quantity,
        preorder_wines.member_price,
        preorder_wines.stock,
        preorder_wines.price
    FROM preorder_carts
        LEFT OUTER JOIN preorder_wines
        ON preorder_carts.product_id=preorder_wines.barcode_number
        WHERE preorder_carts.user_session_id=argUserId;
END$$
GRANT EXECUTE ON PROCEDURE shop.get_preorder_cart_contents TO 'reader'@'localhost';

/*
 * checkout_preorder_cart
 *
 * DB:     shop
 * Tables: preorder_carts, preorder_wines
 *
 * Check out preordered wines in the specified user's cart.
*/
CREATE PROCEDURE checkout_preorder_cart(argUserId CHAR(32), OUT argSuccess INTEGER)
BEGIN
    DECLARE tmpId INTEGER UNSIGNED;
    DECLARE tmpQty INTEGER UNSIGNED;
    DECLARE done INTEGER DEFAULT 0;
    DECLARE cur CURSOR FOR SELECT product_id, quantity FROM preorder_carts WHERE user_session_id=argUserId;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done=1;

    START TRANSACTION;
        OPEN cur;
            REPEAT
                FETCH cur INTO tmpId, tmpQty;
                IF NOT done THEN
                    CALL checkout_preorder_wine(tmpId, tmpQty, @fSuccess);

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
GRANT EXECUTE ON PROCEDURE shop.checkout_preorder_cart TO 'reader'@'localhost';

/*
 * checkin_preorder_cart
 *
 * DB:     shop
 * Tables: preorder_carts, preorder_wines
 *
 * Check in wines in the specified user's cart.
*/
CREATE PROCEDURE checkin_preorder_cart(argUserId CHAR(32), OUT argSuccess INTEGER)
BEGIN
    DECLARE tmpId INTEGER UNSIGNED;
    DECLARE tmpQty INTEGER UNSIGNED;
    DECLARE done INTEGER DEFAULT 0;
    DECLARE cur CURSOR FOR SELECT product_id, quantity FROM preorder_carts WHERE user_session_id=argUserId;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done=1;

    START TRANSACTION;
        OPEN cur;
            REPEAT
                FETCH cur INTO tmpId, tmpQty;
                IF NOT done THEN
                    CALL checkout_preorder_wine(tmpId, -tmpQty, @fSuccess);

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
GRANT EXECUTE ON PROCEDURE shop.checkin_preorder_cart TO 'reader'@'localhost';

/*
 * checkout_preorder_wine
 *
 * DB:     shop
 * Tables: preorder_wines
 *
 * Set argQty to the negative value in order to revert the checked-out wines.
 *
 * This procedure locks the specified row if it is called within a transaction.
*/
CREATE PROCEDURE checkout_preorder_wine(argBarcode INTEGER UNSIGNED, argQty SMALLINT, OUT argSuccess INTEGER)
BEGIN
    DECLARE tmpStock INTEGER;
    SELECT stock INTO tmpStock FROM preorder_wines WHERE barcode_number=argBarcode FOR UPDATE;

    IF tmpStock < argQty THEN
        SET argSuccess = 0;
    ELSE
        UPDATE preorder_wines SET stock = (stock - argQty) WHERE barcode_number = argBarcode;
        SET argSuccess = 1;
    END IF;
END$$
GRANT EXECUTE ON PROCEDURE shop.checkout_preorder_wine TO 'reader'@'localhost';

