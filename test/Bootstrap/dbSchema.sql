DROP TABLE IF EXISTS `User`;

CREATE TABLE `User` (
  `userId` bigint(20) unsigned NOT NULL,
  `userName` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  PRIMARY KEY (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `Item`;

CREATE TABLE `Item` (
  `itemId` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `userId` bigint(20) unsigned NOT NULL,
  `itemDesignId` bigint(20) NOT NULL,
  `itemName` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`itemId`),
  KEY `index` (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;