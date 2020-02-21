-- phpMyAdmin SQL Dump
-- version 4.9.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 20-Fev-2020 às 01:07
-- Versão do servidor: 10.4.10-MariaDB
-- versão do PHP: 7.3.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `db_ecommerce`
--

DELIMITER $$
--
-- Procedimentos
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_addresses_save` (`pidaddress` INT(11), `pidperson` INT(11), `pdesaddress` VARCHAR(128), `pdesnumber` VARCHAR(16), `pdescomplement` VARCHAR(32), `pdesdistrict` VARCHAR(32), `pdescity` VARCHAR(32), `pdesstate` VARCHAR(32), `pdescountry` VARCHAR(32), `pdeszipcode` CHAR(8))  BEGIN

	IF pidaddress > 0 THEN
		
		UPDATE tb_addresses
        SET
			idperson = pidperson,
            desaddress = pdesaddress,
            desnumber = pdesnumber,
            descomplement = pdescomplement,
			desdistrict = pdesdistrict,
            descity = pdescity,
            desstate = pdesstate,
            descountry = pdescountry,
            deszipcode = pdeszipcode
		WHERE idaddress = pidaddress;
        
    ELSE
		
		INSERT INTO tb_addresses (idperson, desaddress, desnumber, descomplement, desdistrict, descity, desstate, descountry, deszipcode)
        VALUES(pidperson, pdesaddress, pdesnumber, pdescomplement, pdesdistrict, pdescity, pdesstate, pdescountry, pdeszipcode);
        
        SET pidaddress = LAST_INSERT_ID();
        
    END IF;
    
    SELECT * FROM tb_addresses WHERE idaddress = pidaddress;

END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_carts_save` (`pidcart` INT, `pdessessionid` VARCHAR(64), `piduser` INT, `pdeszipcode` CHAR(8), `pvlfreight` DECIMAL(10,2), `pnrdays` INT)  BEGIN

    IF pidcart > 0 THEN
        
        UPDATE tb_carts
        SET
            dessessionid = pdessessionid,
            iduser = piduser,
            deszipcode = pdeszipcode,
            vlfreight = pvlfreight,
            nrdays = pnrdays
        WHERE idcart = pidcart;
        
    ELSE
        
        INSERT INTO tb_carts (dessessionid, iduser, deszipcode, vlfreight, nrdays)
        VALUES(pdessessionid, piduser, pdeszipcode, pvlfreight, pnrdays);
        
        SET pidcart = LAST_INSERT_ID();
        
    END IF;
    
    SELECT * FROM tb_carts WHERE idcart = pidcart;

END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_categories_save` (`pidcategory` INT, `pdescategory` VARCHAR(64))  BEGIN
	
	IF pidcategory > 0 THEN
		
		UPDATE tb_categories
        SET descategory = pdescategory
        WHERE idcategory = pidcategory;
        
    ELSE
		
		INSERT INTO tb_categories (descategory) VALUES(pdescategory);
        
        SET pidcategory = LAST_INSERT_ID();
        
    END IF;
    
    SELECT * FROM tb_categories WHERE idcategory = pidcategory;
    
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_orders_save` (`pidorder` INT, `pidcart` INT(11), `piduser` INT(11), `pidstatus` INT(11), `pidaddress` INT(11), `pvltotal` DECIMAL(10,2))  BEGIN
	
	IF pidorder > 0 THEN
		
		UPDATE tb_orders
        SET
			idcart = pidcart,
            iduser = piduser,
            idstatus = pidstatus,
            idaddress = pidaddress,
            vltotal = pvltotal
		WHERE idorder = pidorder;
        
    ELSE
    
		INSERT INTO tb_orders (idcart, iduser, idstatus, idaddress, vltotal)
        VALUES(pidcart, piduser, pidstatus, pidaddress, pvltotal);
		
		SET pidorder = LAST_INSERT_ID();
        
    END IF;
    
    SELECT * 
    FROM tb_orders a
    INNER JOIN tb_ordersstatus b USING(idstatus)
    INNER JOIN tb_carts c USING(idcart)
    INNER JOIN tb_users d ON d.iduser = a.iduser
    INNER JOIN tb_addresses e USING(idaddress)
    WHERE idorder = pidorder;
    
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_products_save` (`pidproduct` INT(11), `pdesproduct` VARCHAR(64), `pvlprice` DECIMAL(10,2), `pvlwidth` DECIMAL(10,2), `pvlheight` DECIMAL(10,2), `pvllength` DECIMAL(10,2), `pvlweight` DECIMAL(10,2), `pdesurl` VARCHAR(128))  BEGIN
	
	IF pidproduct > 0 THEN
		
		UPDATE tb_products
        SET 
			desproduct = pdesproduct,
            vlprice = pvlprice,
            vlwidth = pvlwidth,
            vlheight = pvlheight,
            vllength = pvllength,
            vlweight = pvlweight,
            desurl = pdesurl
        WHERE idproduct = pidproduct;
        
    ELSE
		
		INSERT INTO tb_products (desproduct, vlprice, vlwidth, vlheight, vllength, vlweight, desurl) 
        VALUES(pdesproduct, pvlprice, pvlwidth, pvlheight, pvllength, pvlweight, pdesurl);
        
        SET pidproduct = LAST_INSERT_ID();
        
    END IF;
    
    SELECT * FROM tb_products WHERE idproduct = pidproduct;
    
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_userspasswordsrecoveries_create` (`piduser` INT, `pdesip` VARCHAR(45))  BEGIN
  
  INSERT INTO tb_userspasswordsrecoveries (iduser, desip)
    VALUES(piduser, pdesip);
    
    SELECT * FROM tb_userspasswordsrecoveries
    WHERE idrecovery = LAST_INSERT_ID();
    
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_users_create` (`pdesperson` VARCHAR(64), `pdeslogin` VARCHAR(64), `pdespassword` VARCHAR(256), `pdesemail` VARCHAR(128), `pnrphone` BIGINT, `pinadmin` TINYINT)  BEGIN
  
    DECLARE vidperson INT;
    
  INSERT INTO tb_persons (desperson, desemail, nrphone)
    VALUES(pdesperson, pdesemail, pnrphone);
    
    SET vidperson = LAST_INSERT_ID();
    
    INSERT INTO tb_users (idperson, deslogin, despassword, inadmin)
    VALUES(vidperson, pdeslogin, pdespassword, pinadmin);
    
    SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = LAST_INSERT_ID();
    
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_users_delete` (`piduser` INT)  BEGIN
  
    DECLARE vidperson INT;
    
  SELECT idperson INTO vidperson
    FROM tb_users
    WHERE iduser = piduser;
    
    DELETE FROM tb_users WHERE iduser = piduser;
    DELETE FROM tb_persons WHERE idperson = vidperson;
    
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_users_update` (`piduser` INT, `pdesperson` VARCHAR(64), `pdeslogin` VARCHAR(64), `pdespassword` VARCHAR(256), `pdesemail` VARCHAR(128), `pnrphone` BIGINT, `pinadmin` TINYINT)  BEGIN
  
    DECLARE vidperson INT;
    
  SELECT idperson INTO vidperson
    FROM tb_users
    WHERE iduser = piduser;
    
    UPDATE tb_persons
    SET 
    desperson = pdesperson,
        desemail = pdesemail,
        nrphone = pnrphone
  WHERE idperson = vidperson;
    
    UPDATE tb_users
    SET
    deslogin = pdeslogin,
        despassword = pdespassword,
        inadmin = pinadmin
  WHERE iduser = piduser;
    
    SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = piduser;
    
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tb_addresses`
--

CREATE TABLE `tb_addresses` (
  `idaddress` int(11) NOT NULL,
  `idperson` int(11) NOT NULL,
  `desaddress` varchar(128) NOT NULL,
  `desnumber` varchar(16) NOT NULL,
  `descomplement` varchar(32) DEFAULT NULL,
  `desdistrict` varchar(32) NOT NULL,
  `descity` varchar(32) NOT NULL,
  `desstate` varchar(32) NOT NULL,
  `descountry` varchar(32) NOT NULL,
  `deszipcode` char(8) NOT NULL,
  `dtregister` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Extraindo dados da tabela `tb_addresses`
--

INSERT INTO `tb_addresses` (`idaddress`, `idperson`, `desaddress`, `desnumber`, `descomplement`, `desdistrict`, `descity`, `desstate`, `descountry`, `deszipcode`, `dtregister`) VALUES
(5, 12, 'Rua Junqueira', '12', '', 'Tucumanzal', 'Porto Velho', 'RO', 'Brasil', '76804520', '2020-01-22 12:53:59'),
(6, 1, 'Rua Junqueira', '12', '', 'Tucumanzal', 'Porto Velho', 'RO', 'Brasil', '76804520', '2020-01-22 18:43:51'),
(7, 1, 'Rua Junqueira', '12', '', 'Tucumanzal', 'Porto Velho', 'RO', 'Brasil', '76804520', '2020-01-22 18:44:16'),
(11, 12, 'Rua Junqueira', '500', '', 'Tucumanzal', 'Porto Velho', 'RO', 'Brasil', '76804520', '2020-01-28 20:35:21'),
(12, 12, 'Rua Junqueira', '500', '', 'Tucumanzal', 'Porto Velho', 'RO', 'Brasil', '76804520', '2020-01-28 21:56:01'),
(13, 12, 'Rua Junqueira', '500', '', 'Tucumanzal', 'Porto Velho', 'RO', 'Brasil', '76804520', '2020-01-28 21:57:26'),
(14, 12, 'Rua Junqueira', '500', '', 'Tucumanzal', 'Porto Velho', 'RO', 'Brasil', '76804520', '2020-01-28 22:45:00'),
(15, 12, 'Rua Junqueira', '500', '', 'Tucumanzal', 'Porto Velho', 'RO', 'Brasil', '76804520', '2020-01-28 22:45:12'),
(16, 12, 'Rua Junqueira', '500', '', 'Tucumanzal', 'Porto Velho', 'RO', 'Brasil', '76804520', '2020-01-28 22:46:24'),
(17, 12, 'Rua Junqueira', '500', '', 'Tucumanzal', 'Porto Velho', 'RO', 'Brasil', '76804520', '2020-01-28 22:46:37'),
(18, 12, 'Rua Junqueira', '500', '', 'Tucumanzal', 'Porto Velho', 'RO', 'Brasil', '76804520', '2020-01-28 22:47:38');

-- --------------------------------------------------------

--
-- Estrutura da tabela `tb_carts`
--

CREATE TABLE `tb_carts` (
  `idcart` int(11) NOT NULL,
  `dessessionid` varchar(64) NOT NULL,
  `iduser` int(11) DEFAULT NULL,
  `deszipcode` char(8) DEFAULT NULL,
  `vlfreight` decimal(10,2) DEFAULT NULL,
  `nrdays` int(11) DEFAULT NULL,
  `dtregister` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Extraindo dados da tabela `tb_carts`
--

INSERT INTO `tb_carts` (`idcart`, `dessessionid`, `iduser`, `deszipcode`, `vlfreight`, `nrdays`, `dtregister`) VALUES
(6, 'uccvt2t6g8thjuajmpct2sb7lj', 12, '76804520', '189.13', 6, '2020-01-21 17:14:56'),
(7, 'ss04d30i2uf8agm22e8gabnqc2', NULL, NULL, NULL, NULL, '2020-01-23 11:44:12'),
(8, '0ok6kqho8kgjjoddhnfr2r0n2j', NULL, NULL, NULL, NULL, '2020-01-23 13:57:16'),
(9, 'csjoggq51n5av53sfc3iio9cir', NULL, NULL, NULL, NULL, '2020-01-23 14:04:16'),
(10, 'kjdboka6okd9k30brs1o3bce42', NULL, NULL, NULL, NULL, '2020-01-23 15:23:29'),
(11, 'n3ssppg639e8iri8iad59amste', NULL, NULL, NULL, NULL, '2020-01-23 23:36:30'),
(12, 'piere9k6dkc4ddurvedndtjmee', NULL, NULL, NULL, NULL, '2020-01-24 11:54:11'),
(13, 'd2rs7m15vhq9n3237uhgelj39l', NULL, NULL, NULL, NULL, '2020-01-25 11:54:11'),
(14, 'jg1fefm8gmhrjvljs06b0i9cpm', NULL, NULL, NULL, NULL, '2020-01-27 12:28:43'),
(15, '8uca33h7ng22hb5ebhgp8cqfdi', NULL, NULL, NULL, NULL, '2020-01-29 12:42:36'),
(16, 'nmem22eutuhd6npmg6q49gms43', NULL, NULL, NULL, NULL, '2020-01-29 18:03:40'),
(17, 'u638jvc8uckrr3h8lpnn3flrdh', NULL, NULL, NULL, NULL, '2020-01-29 19:09:29'),
(18, 'sse8ulth57qs13honsl4b7f367', NULL, NULL, NULL, NULL, '2020-01-30 12:42:47'),
(19, 'labeoel0pk1tro9ijrhm42hm5a', NULL, NULL, NULL, NULL, '2020-01-31 12:42:49'),
(20, '8vd5eiopl7fvfc0q6o426qqg26', NULL, NULL, NULL, NULL, '2020-02-03 10:35:12'),
(21, '6v57u2prp5v5u7gbrh5u9qjkn3', NULL, NULL, NULL, NULL, '2020-02-03 11:15:12'),
(22, 'eevm1l3sssktn8ha1jvencfj4k', NULL, NULL, NULL, NULL, '2020-02-04 11:15:17'),
(23, 'psm1octjm3piisk5bes7lqvj09', NULL, NULL, NULL, NULL, '2020-02-05 11:25:17'),
(24, '269h2vuho31h9q6m63i6f3eb6h', NULL, NULL, NULL, NULL, '2020-02-06 11:35:17'),
(25, 'qfjje7is3mt2ed756vppkf2hus', NULL, NULL, NULL, NULL, '2020-02-07 11:35:18'),
(26, '6j9an60ln5tmddnc92h5uq1pf3', NULL, NULL, NULL, NULL, '2020-02-08 11:45:18'),
(27, '4p5nmepq0tnea2e1c5kguiipul', NULL, NULL, NULL, NULL, '2020-02-09 11:55:18'),
(28, 'v1t503chrgubhnphk422300o2d', NULL, NULL, NULL, NULL, '2020-02-10 12:05:18'),
(29, 'pvnprkq61l88dfhucdifr0n0d1', NULL, NULL, NULL, NULL, '2020-02-11 12:15:18'),
(30, '8icu3eg3hk8g62g0uidegj6imt', NULL, NULL, NULL, NULL, '2020-02-12 12:15:19'),
(31, '581po9fmqg9gn23fmdtmk087uv', NULL, NULL, NULL, NULL, '2020-02-12 20:37:21'),
(32, '4ajav3h4o9baldscrp3oi8v8pr', NULL, NULL, NULL, NULL, '2020-02-13 12:25:19'),
(33, '5t0k1vhd8q3djpk70f3bkg3n75', NULL, NULL, NULL, NULL, '2020-02-14 12:35:18'),
(34, 'gbf988d6tsc8aqje8ar9vhdulk', NULL, NULL, NULL, NULL, '2020-02-14 21:48:04'),
(35, 'tcr5tg96gshr4mtsnluo8imbcr', NULL, NULL, NULL, NULL, '2020-02-15 00:14:10'),
(36, 'cvsv7693jl4u64gvp00mcfl0ho', NULL, NULL, NULL, NULL, '2020-02-15 00:15:11'),
(37, 'allfoa0rgaglu15uhvnrkk72rb', NULL, NULL, NULL, NULL, '2020-02-15 12:37:33'),
(38, '02jb21ukdnuphv9utp85oinurk', NULL, NULL, NULL, NULL, '2020-02-16 12:37:33'),
(39, '8fr08dpr5ges90q24dsrgps2fo', NULL, NULL, NULL, NULL, '2020-02-17 11:20:08'),
(40, 'aalmjrjbkmdneo32f3tf4es9s9', NULL, NULL, NULL, NULL, '2020-02-19 00:55:08');

-- --------------------------------------------------------

--
-- Estrutura da tabela `tb_cartsproducts`
--

CREATE TABLE `tb_cartsproducts` (
  `idcartproduct` int(11) NOT NULL,
  `idcart` int(11) NOT NULL,
  `idproduct` int(11) NOT NULL,
  `dtremoved` datetime DEFAULT NULL,
  `dtregister` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Extraindo dados da tabela `tb_cartsproducts`
--

INSERT INTO `tb_cartsproducts` (`idcartproduct`, `idcart`, `idproduct`, `dtremoved`, `dtregister`) VALUES
(73, 6, 7, NULL, '2020-01-21 18:31:34'),
(74, 6, 5, NULL, '2020-01-21 18:45:46'),
(75, 6, 5, NULL, '2020-01-22 18:43:37'),
(76, 6, 7, NULL, '2020-01-22 18:43:58');

-- --------------------------------------------------------

--
-- Estrutura da tabela `tb_categories`
--

CREATE TABLE `tb_categories` (
  `idcategory` int(11) NOT NULL,
  `descategory` varchar(32) NOT NULL,
  `dtregister` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Extraindo dados da tabela `tb_categories`
--

INSERT INTO `tb_categories` (`idcategory`, `descategory`, `dtregister`) VALUES
(2, 'Android', '2020-01-07 14:50:27'),
(3, 'Apple', '2020-01-08 11:17:24'),
(4, 'Motorola', '2020-01-08 11:17:31'),
(5, 'Samsung', '2020-01-08 11:17:37');

-- --------------------------------------------------------

--
-- Estrutura da tabela `tb_categoriesproducts`
--

CREATE TABLE `tb_categoriesproducts` (
  `idcategory` int(11) NOT NULL,
  `idproduct` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Extraindo dados da tabela `tb_categoriesproducts`
--

INSERT INTO `tb_categoriesproducts` (`idcategory`, `idproduct`) VALUES
(2, 1),
(2, 5),
(2, 6),
(2, 7),
(2, 8),
(2, 9),
(2, 11),
(4, 5),
(4, 6),
(5, 7),
(5, 8),
(5, 9);

-- --------------------------------------------------------

--
-- Estrutura da tabela `tb_orders`
--

CREATE TABLE `tb_orders` (
  `idorder` int(11) NOT NULL,
  `idcart` int(11) NOT NULL,
  `iduser` int(11) NOT NULL,
  `idstatus` int(11) NOT NULL,
  `idaddress` int(11) NOT NULL,
  `vltotal` decimal(10,2) NOT NULL,
  `dtregister` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Extraindo dados da tabela `tb_orders`
--

INSERT INTO `tb_orders` (`idorder`, `idcart`, `iduser`, `idstatus`, `idaddress`, `vltotal`, `dtregister`) VALUES
(1, 6, 12, 2, 5, '2574.67', '2020-01-22 13:27:12'),
(2, 6, 1, 1, 6, '3732.61', '2020-01-22 18:43:51'),
(4, 6, 12, 1, 11, '5057.59', '2020-01-28 20:35:21'),
(5, 6, 12, 1, 12, '5057.59', '2020-01-28 21:56:01'),
(6, 6, 12, 1, 13, '5057.59', '2020-01-28 21:57:26'),
(7, 6, 12, 1, 14, '5057.59', '2020-01-28 22:45:00'),
(8, 6, 12, 1, 15, '5057.59', '2020-01-28 22:45:12'),
(9, 6, 12, 1, 16, '5057.59', '2020-01-28 22:46:24'),
(10, 6, 12, 1, 17, '5057.59', '2020-01-28 22:46:37'),
(11, 6, 12, 1, 18, '5057.59', '2020-01-28 22:47:38');

-- --------------------------------------------------------

--
-- Estrutura da tabela `tb_ordersstatus`
--

CREATE TABLE `tb_ordersstatus` (
  `idstatus` int(11) NOT NULL,
  `desstatus` varchar(32) NOT NULL,
  `dtregister` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Extraindo dados da tabela `tb_ordersstatus`
--

INSERT INTO `tb_ordersstatus` (`idstatus`, `desstatus`, `dtregister`) VALUES
(1, 'Em Aberto', '2017-03-13 06:00:00'),
(2, 'Aguardando Pagamento', '2017-03-13 06:00:00'),
(3, 'Pago', '2017-03-13 06:00:00'),
(4, 'Entregue', '2017-03-13 06:00:00');

-- --------------------------------------------------------

--
-- Estrutura da tabela `tb_persons`
--

CREATE TABLE `tb_persons` (
  `idperson` int(11) NOT NULL,
  `desperson` varchar(64) NOT NULL,
  `desemail` varchar(128) DEFAULT NULL,
  `nrphone` bigint(20) DEFAULT NULL,
  `dtregister` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Extraindo dados da tabela `tb_persons`
--

INSERT INTO `tb_persons` (`idperson`, `desperson`, `desemail`, `nrphone`, `dtregister`) VALUES
(1, 'Administrador', 'admin@hcode.com.br', 2147483647, '2017-03-01 06:00:10'),
(7, 'Suporte', 'alexbotelho2@hotmail.com', 1112345678, '2017-03-15 19:10:27'),
(12, 'Alex Visitante 2', 'alexbotelho1@hotmail.com', 45984136611, '2020-01-21 12:58:33'),
(15, 'ALEX 2', 'alexbotelho1@hotmail.com', 45984136611, '2020-01-28 16:34:19'),
(17, 'admin', 'alexbotelho1@hotmail.com', 45984136611, '2020-01-28 16:34:50'),
(18, 'ALEX5 ', 'alexbotelho1@hotmail.com', 45984136611, '2020-01-28 16:34:56'),
(19, 'Visitante ', 'alexbotelho1@hotmail.com', 45984136611, '2020-01-28 16:35:03');

-- --------------------------------------------------------

--
-- Estrutura da tabela `tb_products`
--

CREATE TABLE `tb_products` (
  `idproduct` int(11) NOT NULL,
  `desproduct` varchar(64) NOT NULL,
  `vlprice` decimal(10,2) NOT NULL,
  `vlwidth` decimal(10,2) NOT NULL,
  `vlheight` decimal(10,2) NOT NULL,
  `vllength` decimal(10,2) NOT NULL,
  `vlweight` decimal(10,2) NOT NULL,
  `desurl` varchar(128) NOT NULL,
  `dtregister` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Extraindo dados da tabela `tb_products`
--

INSERT INTO `tb_products` (`idproduct`, `desproduct`, `vlprice`, `vlwidth`, `vlheight`, `vllength`, `vlweight`, `desurl`, `dtregister`) VALUES
(1, 'Smartphone Android 7.0', '999.95', '75.00', '151.00', '80.00', '167.00', 'smartphone-android-7.0', '2017-03-13 06:00:00'),
(2, 'SmartTV LED 4K', '3925.99', '917.00', '596.00', '288.00', '8600.00', 'smarttv-led-4k', '2017-03-13 06:00:00'),
(3, 'Notebook 14\" 4GB 1TB', '1949.00', '345.00', '23.00', '30.00', '2000.00', 'notebook-14-4gb-1tb', '2017-03-13 06:00:00'),
(5, 'Smartphone Motorola Moto G5 Plus', '1135.23', '15.20', '7.40', '0.70', '0.16', 'smartphone-motorola-moto-g5-plus', '2020-01-08 17:40:16'),
(6, 'Smartphone Moto Z Play', '1887.78', '14.10', '0.90', '1.16', '0.13', 'smartphone-moto-z-play', '2020-01-08 17:40:16'),
(7, 'Smartphone Samsung Galaxy J5 Pro', '1299.00', '14.60', '7.10', '0.80', '0.16', 'smartphone-samsung-galaxy-j5', '2020-01-08 17:40:16'),
(8, 'Smartphone Samsung Galaxy J7 Prime', '1149.00', '15.10', '7.50', '0.80', '0.16', 'smartphone-samsung-galaxy-j7', '2020-01-08 17:40:16'),
(9, 'Smartphone Samsung Galaxy J3 Dual', '679.90', '14.20', '7.10', '0.70', '0.14', 'smartphone-samsung-galaxy-j3', '2020-01-08 17:40:16'),
(11, 'Teste 1', '100.00', '200.00', '300.00', '400.00', '500.00', 'teste-1', '2020-01-08 18:46:28');

-- --------------------------------------------------------

--
-- Estrutura da tabela `tb_users`
--

CREATE TABLE `tb_users` (
  `iduser` int(11) NOT NULL,
  `idperson` int(11) NOT NULL,
  `deslogin` varchar(64) NOT NULL,
  `despassword` varchar(256) NOT NULL,
  `inadmin` tinyint(4) NOT NULL DEFAULT 0,
  `dtregister` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Extraindo dados da tabela `tb_users`
--

INSERT INTO `tb_users` (`iduser`, `idperson`, `deslogin`, `despassword`, `inadmin`, `dtregister`) VALUES
(1, 1, 'admin', '$2y$12$8NCaa..dn7GMSlOOOTQLwukUdHXdyb6w6eZAuPLiBVRshCx0Qad.u', 1, '2017-03-13 06:00:00'),
(7, 7, 'suporte', '$2y$12$8NCaa..dn7GMSlOOOTQLwukUdHXdyb6w6eZAuPLiBVRshCx0Qad.u', 1, '2017-03-15 19:10:27'),
(12, 12, 'alexbotelho1@hotmail.com', '$2y$12$8NCaa..dn7GMSlOOOTQLwukUdHXdyb6w6eZAuPLiBVRshCx0Qad.u', 0, '2020-01-21 12:58:33'),
(15, 15, 'alex', '$2y$12$pjd5rNPYPXZxdoaBnockqu3TyKrMJDc4qGKI99Avg1shjJKMuahVm', 0, '2020-01-28 16:34:19'),
(17, 17, 'admin', '$2y$12$mA7zv65xc5Vj7Z3AkDQjNuNpY1PFXdD1hj9oviWVODRLvfqerBViO', 0, '2020-01-28 16:34:50'),
(18, 18, 'Suporte', '$2y$12$xn0ymQ9Yryr/WEjjrB5dXOpcioxZqKoOdEQe7AgX48KFPQe4pxXjm', 0, '2020-01-28 16:34:57'),
(19, 19, 'Visitante ', '$2y$12$VwyIOiKS25xmS4sh/3JtkudhhSCkci/hkIqLZy8GigygBnA.yjmk2', 0, '2020-01-28 16:35:03');

-- --------------------------------------------------------

--
-- Estrutura da tabela `tb_userslogs`
--

CREATE TABLE `tb_userslogs` (
  `idlog` int(11) NOT NULL,
  `iduser` int(11) NOT NULL,
  `deslog` varchar(128) NOT NULL,
  `desip` varchar(45) NOT NULL,
  `desuseragent` varchar(128) NOT NULL,
  `dessessionid` varchar(64) NOT NULL,
  `desurl` varchar(128) NOT NULL,
  `dtregister` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tb_userspasswordsrecoveries`
--

CREATE TABLE `tb_userspasswordsrecoveries` (
  `idrecovery` int(11) NOT NULL,
  `iduser` int(11) NOT NULL,
  `desip` varchar(45) NOT NULL,
  `dtrecovery` datetime DEFAULT NULL,
  `dtregister` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Extraindo dados da tabela `tb_userspasswordsrecoveries`
--

INSERT INTO `tb_userspasswordsrecoveries` (`idrecovery`, `iduser`, `desip`, `dtrecovery`, `dtregister`) VALUES
(1, 7, '127.0.0.1', NULL, '2017-03-15 19:10:59'),
(2, 7, '127.0.0.1', '2017-03-15 13:33:45', '2017-03-15 19:11:18'),
(3, 7, '127.0.0.1', '2017-03-15 13:37:35', '2017-03-15 19:37:12'),
(4, 7, '127.0.0.1', NULL, '2020-01-06 15:59:48'),
(5, 7, '127.0.0.1', NULL, '2020-01-06 16:03:48'),
(6, 7, '127.0.0.1', NULL, '2020-01-06 16:10:54'),
(7, 7, '127.0.0.1', NULL, '2020-01-06 16:13:50'),
(8, 7, '127.0.0.1', NULL, '2020-01-06 16:17:11'),
(9, 7, '127.0.0.1', '2020-01-06 14:34:20', '2020-01-06 16:39:38'),
(10, 7, '127.0.0.1', NULL, '2020-01-06 16:41:50'),
(11, 7, '127.0.0.1', NULL, '2020-01-06 17:35:12'),
(12, 7, '127.0.0.1', '2020-01-06 14:49:39', '2020-01-06 17:47:11'),
(13, 7, '127.0.0.1', '2020-01-06 15:00:04', '2020-01-06 17:58:35'),
(14, 7, '127.0.0.1', NULL, '2020-01-21 13:39:07'),
(15, 7, '127.0.0.1', '2020-01-21 10:43:20', '2020-01-21 13:43:01'),
(16, 7, '127.0.0.1', '2020-01-21 11:04:20', '2020-01-21 14:04:06'),
(17, 12, '127.0.0.1', '2020-01-21 13:00:35', '2020-01-21 16:00:10');

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `tb_addresses`
--
ALTER TABLE `tb_addresses`
  ADD PRIMARY KEY (`idaddress`),
  ADD KEY `fk_addresses_persons_idx` (`idperson`);

--
-- Índices para tabela `tb_carts`
--
ALTER TABLE `tb_carts`
  ADD PRIMARY KEY (`idcart`),
  ADD KEY `FK_carts_users_idx` (`iduser`);

--
-- Índices para tabela `tb_cartsproducts`
--
ALTER TABLE `tb_cartsproducts`
  ADD PRIMARY KEY (`idcartproduct`),
  ADD KEY `FK_cartsproducts_products_idx` (`idproduct`);

--
-- Índices para tabela `tb_categories`
--
ALTER TABLE `tb_categories`
  ADD PRIMARY KEY (`idcategory`);

--
-- Índices para tabela `tb_categoriesproducts`
--
ALTER TABLE `tb_categoriesproducts`
  ADD PRIMARY KEY (`idcategory`,`idproduct`);

--
-- Índices para tabela `tb_orders`
--
ALTER TABLE `tb_orders`
  ADD PRIMARY KEY (`idorder`),
  ADD KEY `FK_orders_users_idx` (`iduser`),
  ADD KEY `fk_orders_ordersstatus_idx` (`idstatus`),
  ADD KEY `fk_orders_carts_idx` (`idcart`),
  ADD KEY `fk_orders_addresses_idx` (`idaddress`);

--
-- Índices para tabela `tb_ordersstatus`
--
ALTER TABLE `tb_ordersstatus`
  ADD PRIMARY KEY (`idstatus`);

--
-- Índices para tabela `tb_persons`
--
ALTER TABLE `tb_persons`
  ADD PRIMARY KEY (`idperson`);

--
-- Índices para tabela `tb_products`
--
ALTER TABLE `tb_products`
  ADD PRIMARY KEY (`idproduct`);

--
-- Índices para tabela `tb_users`
--
ALTER TABLE `tb_users`
  ADD PRIMARY KEY (`iduser`),
  ADD KEY `FK_users_persons_idx` (`idperson`);

--
-- Índices para tabela `tb_userslogs`
--
ALTER TABLE `tb_userslogs`
  ADD PRIMARY KEY (`idlog`),
  ADD KEY `fk_userslogs_users_idx` (`iduser`);

--
-- Índices para tabela `tb_userspasswordsrecoveries`
--
ALTER TABLE `tb_userspasswordsrecoveries`
  ADD PRIMARY KEY (`idrecovery`),
  ADD KEY `fk_userspasswordsrecoveries_users_idx` (`iduser`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `tb_addresses`
--
ALTER TABLE `tb_addresses`
  MODIFY `idaddress` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de tabela `tb_carts`
--
ALTER TABLE `tb_carts`
  MODIFY `idcart` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT de tabela `tb_cartsproducts`
--
ALTER TABLE `tb_cartsproducts`
  MODIFY `idcartproduct` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- AUTO_INCREMENT de tabela `tb_categories`
--
ALTER TABLE `tb_categories`
  MODIFY `idcategory` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `tb_orders`
--
ALTER TABLE `tb_orders`
  MODIFY `idorder` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de tabela `tb_ordersstatus`
--
ALTER TABLE `tb_ordersstatus`
  MODIFY `idstatus` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `tb_persons`
--
ALTER TABLE `tb_persons`
  MODIFY `idperson` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de tabela `tb_products`
--
ALTER TABLE `tb_products`
  MODIFY `idproduct` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de tabela `tb_users`
--
ALTER TABLE `tb_users`
  MODIFY `iduser` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de tabela `tb_userslogs`
--
ALTER TABLE `tb_userslogs`
  MODIFY `idlog` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `tb_userspasswordsrecoveries`
--
ALTER TABLE `tb_userspasswordsrecoveries`
  MODIFY `idrecovery` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `tb_addresses`
--
ALTER TABLE `tb_addresses`
  ADD CONSTRAINT `fk_addresses_persons` FOREIGN KEY (`idperson`) REFERENCES `tb_persons` (`idperson`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Limitadores para a tabela `tb_carts`
--
ALTER TABLE `tb_carts`
  ADD CONSTRAINT `fk_carts_users` FOREIGN KEY (`iduser`) REFERENCES `tb_users` (`iduser`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Limitadores para a tabela `tb_cartsproducts`
--
ALTER TABLE `tb_cartsproducts`
  ADD CONSTRAINT `fk_cartsproducts_products` FOREIGN KEY (`idproduct`) REFERENCES `tb_products` (`idproduct`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Limitadores para a tabela `tb_orders`
--
ALTER TABLE `tb_orders`
  ADD CONSTRAINT `fk_orders_addresses` FOREIGN KEY (`idaddress`) REFERENCES `tb_addresses` (`idaddress`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_orders_carts` FOREIGN KEY (`idcart`) REFERENCES `tb_carts` (`idcart`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_orders_ordersstatus` FOREIGN KEY (`idstatus`) REFERENCES `tb_ordersstatus` (`idstatus`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_orders_users` FOREIGN KEY (`iduser`) REFERENCES `tb_users` (`iduser`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Limitadores para a tabela `tb_users`
--
ALTER TABLE `tb_users`
  ADD CONSTRAINT `fk_users_persons` FOREIGN KEY (`idperson`) REFERENCES `tb_persons` (`idperson`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Limitadores para a tabela `tb_userslogs`
--
ALTER TABLE `tb_userslogs`
  ADD CONSTRAINT `fk_userslogs_users` FOREIGN KEY (`iduser`) REFERENCES `tb_users` (`iduser`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Limitadores para a tabela `tb_userspasswordsrecoveries`
--
ALTER TABLE `tb_userspasswordsrecoveries`
  ADD CONSTRAINT `fk_userspasswordsrecoveries_users` FOREIGN KEY (`iduser`) REFERENCES `tb_users` (`iduser`) ON DELETE NO ACTION ON UPDATE NO ACTION;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
