SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';

CREATE SCHEMA IF NOT EXISTS `mydb` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci ;
USE `mydb` ;

-- -----------------------------------------------------
-- Table `mydb`.`Artist`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `mydb`.`Artist` (
  `id` INT NOT NULL ,
  `name` VARCHAR(45) NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`Album`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `mydb`.`Album` (
  `id` INT NOT NULL ,
  `name` VARCHAR(45) NULL ,
  `artist` INT NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_Album_Artist` (`artist` ASC) ,
  CONSTRAINT `fk_Album_Artist`
    FOREIGN KEY (`artist` )
    REFERENCES `mydb`.`Artist` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`Song`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `mydb`.`Song` (
  `id` INT NOT NULL ,
  `name` VARCHAR(45) NULL ,
  `album` INT NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_Song_Album1` (`album` ASC) ,
  CONSTRAINT `fk_Song_Album1`
    FOREIGN KEY (`album` )
    REFERENCES `mydb`.`Album` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;



SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
