SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

CREATE SCHEMA IF NOT EXISTS `combine` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ;
USE `combine` ;

-- -----------------------------------------------------
-- Table `mydb`.`question`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `combine`.`question` ;

CREATE TABLE IF NOT EXISTS `combine`.`question` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `question` VARCHAR(45) NULL,
  `active` VARCHAR(45) NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`user`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `combine`.`user` ;

CREATE TABLE IF NOT EXISTS `combine`.`user` (
  `username` VARCHAR(45) NULL,
  `name` VARCHAR(45) NULL,
  `department` VARCHAR(45) NULL,
  PRIMARY KEY (`username`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`entry`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `combine`.`entry` ;

CREATE TABLE IF NOT EXISTS `combine`.`entry` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `created` DATETIME NULL,
  `response` TEXT NULL,
  `question_id` INT NOT NULL,
  `username` varchar(45) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_entry_question_idx` (`question_id` ASC),
  INDEX `fk_entry_user_idx` (`username` ASC),
  INDEX `created` (`created` ASC),
  CONSTRAINT `fk_entry_question`
    FOREIGN KEY (`question_id`)
    REFERENCES `combine`.`question` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_entry_user`
    FOREIGN KEY (`username`)
    REFERENCES `combine`.`user` (`username`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
