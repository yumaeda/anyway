USE seiya_anyway;

-- Drop procedures first.
DROP PROCEDURE IF EXISTS init_printed_flag;
DROP PROCEDURE IF EXISTS set_printed_flag;
DROP PROCEDURE IF EXISTS get_untagged_wine_codes;

-- Set delimiter to '$$' in order to properly create stored procedures.
DELIMITER $$

/*
 * init_printed_flag
 *
 * DB:     seiya_anyway
 * Tables: anyway_price_tags
*/
CREATE PROCEDURE init_printed_flag(argBarcodeNumber VARCHAR(5))
BEGIN
    INSERT IGNORE INTO anyway_price_tags (barcode_number) VALUES (argBarcodeNumber);
END$$

/*
 * set_printed_flag
 *
 * DB:     seiya_anyway
 * Tables: anyway_price_tags
*/
CREATE PROCEDURE set_printed_flag(argBarcodeNumber VARCHAR(5))
BEGIN
    UPDATE anyway_price_tags SET is_printed=1
    WHERE barcode_number=argBarcodeNumber;
END$$

/*
 * get_untagged_wine_codes
 *
 * DB:     seiya_anyway
 * Tables: anyway_price_tags
*/
CREATE PROCEDURE get_untagged_wine_codes()
BEGIN
    SELECT barcode_number FROM anyway_price_tags
    WHERE is_printed=0;
END$$

-- Set delimiter back to ';'.
DELIMITER ;

-- Grant privillages to 'admin'@'localhost
GRANT EXECUTE ON PROCEDURE seiya_anyway.init_printed_flag TO 'seiya_admin3'@'localhost';
GRANT EXECUTE ON PROCEDURE seiya_anyway.set_printed_flag TO 'seiya_admin3'@'localhost';
GRANT EXECUTE ON PROCEDURE seiya_anyway.get_untagged_wine_code TO 'seiya_admin3'@'localhost';
