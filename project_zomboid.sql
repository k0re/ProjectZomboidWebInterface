
CREATE TABLE `mod_id` (
                          `id` int NOT NULL,
                          `workshop_id` varchar(255) NOT NULL,
                          `mod_id` varchar(255) NOT NULL,
                          `active` tinyint(1) NOT NULL DEFAULT '0'
);

CREATE TABLE `workshop_id` (
                               `id` varchar(255) NOT NULL,
                               `title` varchar(255) NOT NULL,
                               `active` tinyint(1) NOT NULL DEFAULT '0'
);

ALTER TABLE `mod_id`
    ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_mod` (`workshop_id`,`mod_id`) USING BTREE;
ALTER TABLE `workshop_id`
    ADD UNIQUE KEY `id_unique` (`id`);
ALTER TABLE `mod_id`
    MODIFY `id` int NOT NULL AUTO_INCREMENT;
COMMIT;
