
# This is a fix for InnoDB in MySQL >= 4.1.x
# It "suspends judgement" for fkey relationships until are tables are set.
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- product_upsell
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `product_upsell`;

CREATE TABLE `product_upsell`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `product_id` INTEGER NOT NULL,
    `minimumCart` DECIMAL(12,2) NOT NULL,
    `created_at` DATETIME,
    `updated_at` DATETIME,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `product_upsell_u_7d267a` (`product_id`),
    INDEX `product_upsell_i_7d267a` (`product_id`),
    CONSTRAINT `product_upsell_fk_0f5ed8`
        FOREIGN KEY (`product_id`)
        REFERENCES `product` (`id`)
        ON UPDATE RESTRICT
        ON DELETE CASCADE
) ENGINE=InnoDB;

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
