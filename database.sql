-- =============================================================================
-- HXH-C10: Hunter x Hunter CTF Challenge - Master MySQL Schema & Seed Data
-- =============================================================================

CREATE DATABASE IF NOT EXISTS `hxh_c10_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `hxh_c10_db`;
SET NAMES utf8mb4;

-- Users / Game Players Table
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(64) NOT NULL UNIQUE,
    `full_name` VARCHAR(128) NOT NULL,
    `title` VARCHAR(128) NOT NULL,
    `nen_type` VARCHAR(64) NOT NULL,
    `license_number` VARCHAR(64) NOT NULL,
    `balance` INT NOT NULL DEFAULT 0,
    `bio` TEXT NOT NULL,
    `avatar_badge` VARCHAR(16) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- In-Game P2P Trades / Money Transfers Table
CREATE TABLE IF NOT EXISTS `game_trades` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `from_user` VARCHAR(64) NOT NULL,
    `to_user` VARCHAR(64) NOT NULL,
    `amount` INT NOT NULL,
    `memo` VARCHAR(255) DEFAULT 'P2P In-Game Jenny Trade',
    `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- In-Game Purchased Clues & Items Table
CREATE TABLE IF NOT EXISTS `purchased_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(64) NOT NULL,
    `item_id` VARCHAR(64) NOT NULL,
    `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Challenge Flags Table (Dynamic DB Retrieval)
CREATE TABLE IF NOT EXISTS `challenge_flags` (
    `flag_key` VARCHAR(64) PRIMARY KEY,
    `flag_value` VARCHAR(128) NOT NULL,
    `description` VARCHAR(255) DEFAULT '',
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Data: 15 Hunter In-Game Players (Only milluki_zoldyck holds 10,000 Jenny; others have 0)
INSERT INTO `users` (`username`, `full_name`, `title`, `nen_type`, `license_number`, `balance`, `bio`, `avatar_badge`) VALUES
('leorio_paradinight', 'Leorio Paradinight', 'Medical Student & Zodiac ("Boar")', 'Emission (Warp Punch)', '#287-0403-9912', 0, 'Aspiring physician working tirelessly to pay medical school tuition fees. Wallet currently empty.', '🩺'),
('gon_freecss', 'Gon Freecss', 'Rookie Hunter / Enhancer', 'Enhancement (Jajanken)', '#287-0405-1104', 0, 'Traveled from Whale Island in search of his father Ging. Spent all his in-game Jenny on fishing bait.', '🎣'),
('killua_zoldyck', 'Killua Zoldyck', 'Ex-Assassin Heir / Transmuter', 'Transmutation (Godspeed Lightning)', '#288-0001-7723', 0, 'Blew his entire game balance at the confectionery shop purchasing limited edition ChocoRobo-kun snacks.', '⚡'),
('kurapika_kurta', 'Kurapika', 'Blacklist Hunter & Nostrade Boss', 'Conjuration / Specialization (Emperor Time)', '#287-0404-5510', 0, 'Last survivor of the Kurta Clan. Allocated all trade pouch assets into Southernpiece auction intelligence.', '⛓️'),
('milluki_zoldyck', 'Milluki Zoldyck', 'Zoldyck Estate Hacker & Otaku Collector', 'Manipulation (Remote Drone Bot)', '#ZOLDYCK-TECH-902', 10000, 'Second son of the Zoldyck family. Hoards an emergency reserve of 10,000 Ⓙ in his in-game player pouch to purchase ultra-rare anime figurines and Greed Island memory cards.', '💻'),
('hisoka_morow', 'Hisoka Morow', 'Grim Reaper Magician', 'Transmutation (Bungee Gum)', '#287-0044-8832', 0, 'Zero interest in in-game currency. Only seeks unripe fruit to duel to the death.', '🃏'),
('tonpa_crusher', 'Tonpa', 'Rookie Crusher Veteran', 'Unknown (Lethargy)', 'UNLICENSED-EXAM-35X', 0, 'Participated in 35 consecutive Hunter exams. Bankrupted his game inventory purchasing industrial laxatives.', '🥫'),
('hanzo_ninja', 'Hanzo', 'Cloud Hidden Shinobi', 'Transmutation (Ninjutsu Arts)', '#287-0294-3321', 0, 'Elite ninja pursuing the Secret Scroll of the Hermit. Carries only survival shurikens and rations.', '🥷'),
('pokkle_archer', 'Pokkle', 'Beast Hunter / Archer', 'Emission (Rainbow Arrows)', '#287-0053-4412', 0, 'Specializes in undiscovered creature classification. Expended all capital on NGL exploration equipment.', '🏹'),
('ponzu_bees', 'Ponzu', 'Chemical & Bee Specialist', 'Manipulation (Bee Signals)', 'APPLICANT-#246', 0, 'Carries neurotoxin bees inside her hat. Zero Jenny remaining after apiary laboratory upgrades.', '🐝'),
('illumi_zoldyck', 'Illumi Zoldyck', 'Assassin Operative / Needle Master', 'Manipulation (Needle Control)', '#287-0301-9988', 0, 'Transfers all contract assassination bounties directly to the Kukuroo Mountain family vault.', '🪡'),
('chrollo_lucilfer', 'Chrollo Lucilfer', 'Phantom Troupe Head (Spider #0)', 'Specialization (Skill Hunter)', '#METEOR-CITY-0000', 0, 'We steal everything we want. Currency holds no value to the Spider, only rare artifacts and stolen Nen.', '📖'),
('feitan_portor', 'Feitan Portor', 'Phantom Troupe Interrogator (#2)', 'Transmutation (Rising Sun / Pain Packer)', '#SPIDER-0002-XX', 0, 'Master of torture and interrogation. Possesses no in-game money in the civilized world.', '☂️'),
('machi_komacine', 'Machi Komacine', 'Nen Stitch Specialist (#3)', 'Transmutation (Nen Threads)', '#SPIDER-0003-XX', 0, 'Charges exorbitant rates for re-attaching severed limbs (e.g. Hisoka), but stored all funds in Meteor City caches.', '🧵'),
('zepile_appraiser', 'Zepile', 'Yorknew Antique & Treasure Appraiser', 'Natural Genius / Wooden Box Artisan', '#YORKNEW-APPRAISER', 0, 'Fell into severe debt pawning his Hunter License to finance bidding wars at the Southernpiece Auction.', '🏺')
ON DUPLICATE KEY UPDATE `username`=`username`;

-- Seed Flags Data
INSERT INTO `challenge_flags` (`flag_key`, `flag_value`, `description`) VALUES
('stage2', 'SSP{MILLUKI_BANK_IDOR_DRAINED_98A41}', 'Stage 2 P2P Bank IDOR Exploit Flag'),
('stage3', 'SSP{WEB_HUNTER_LICENSE_DA91FC72}', 'Stage 3 Final Web Hunter License Master Flag')
ON DUPLICATE KEY UPDATE `flag_value`=VALUES(`flag_value`);
