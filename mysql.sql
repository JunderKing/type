CREATE TABLE `tp_user`(
    `id` INT UNSIGNED AUTO_INCREMENT,
    `user_name` VARCHAR(50) NOT NULL DEFAULT '',
    `passwd` VARCHAR(100) NOT NULL DEFAULT '',
    `created_at` INT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY(id)
);

CREATE TABLE `tp_phrase`(
    `id` INT UNSIGNED AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL DEFAULT 0,
    `phrase` VARCHAR(1000) NOT NULL DEFAULT '',
    `desc` VARCHAR(1000) NOT NULL DEFAULT '',
    `first_class` VARCHAR(50) NOT NULL DEFAULT '',
    `second_class` VARCHAR(50) NOT NULL DEFAULT '',
    `third_class` VARCHAR(50) NOT NULL DEFAULT '',
    `level` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `speed` INT UNSIGNED NOT NULL DEFAULT 0,
    `in_buffer` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `complete_count` INT UNSIGNED NOT NULL DEFAULT 0,
    `error_count` INT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` INT UNSIGNED NOT NULL DEFAULT 0,
    `updated_at` INT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY(id)
);
