SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';


-- -----------------------------------------------------
-- Table `Artist`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `Artist` (
  `id` INT NOT NULL,
  `name` VARCHAR(45) NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `Album`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `Album` (
  `id` INT NOT NULL,
  `name` VARCHAR(45) NULL,
  `artist` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_Album_Artist_idx` (`artist` ASC),
  CONSTRAINT `fk_Album_Artist`
    FOREIGN KEY (`artist`)
    REFERENCES `Artist` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `Song`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `Song` (
  `id` INT NOT NULL,
  `name` VARCHAR(45) NULL,
  `album` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_Song_Album1_idx` (`album` ASC),
  CONSTRAINT `fk_Song_Album1`
    FOREIGN KEY (`album`)
    REFERENCES `Album` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
