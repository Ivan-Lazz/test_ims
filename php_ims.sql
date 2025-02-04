-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 02, 2025 at 11:54 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `php_ims`
--

-- --------------------------------------------------------

--
-- Table structure for table `billing_details`
--

CREATE TABLE `billing_details` (
  `id` int(11) NOT NULL,
  `bill_id` varchar(50) NOT NULL,
  `product_company` varchar(50) NOT NULL,
  `product_name` varchar(50) NOT NULL,
  `product_unit` varchar(20) NOT NULL,
  `packaging_size` varchar(30) NOT NULL,
  `price` varchar(10) NOT NULL,
  `qty` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `billing_details`
--

INSERT INTO `billing_details` (`id`, `bill_id`, `product_company`, `product_name`, `product_unit`, `packaging_size`, `price`, `qty`) VALUES
(3, '3', 'Tesla', 'Cybertruck', 'Electric Vehicle', '1', '150000', '1'),
(4, '4', 'National Bookstore', 'Stapler Wire', 'Box', '100', '750', '15'),
(5, '5', 'National Bookstore', 'Stapler Wire', 'Box', '100', '750', '20'),
(7, '6', 'National Bookstore', 'HBW Ballpoint Pen BLCK', 'Box', '150', '85', '50'),
(8, '6', 'Marby\'s Bakeshop', 'Ube Hopia', 'Kilogram', '5', '80', '15'),
(9, '6', 'National Bookstore', 'Stapler', 'Box', '20', '1500', '10'),
(10, '6', 'National Bookstore', 'Stapler Wire', 'Box', '100', '750', '15'),
(11, '7', 'National Bookstore', 'HBW Ballpoint Pen BLCK', 'Box', '150', '85', '3'),
(12, '8', 'National Bookstore', 'HBW Ballpoint Pen BLCK', 'Box', '150', '85', '15'),
(13, '8', 'National Bookstore', 'Stapler', 'Box', '20', '1500', '10'),
(14, '8', 'National Bookstore', 'Stapler Wire', 'Box', '100', '750', '10'),
(15, '9', 'National Bookstore', 'HBW Ballpoint Pen BLCK', 'Box', '150', '85', '5'),
(16, '10', 'National Bookstore', 'HBW Ballpoint Pen BLCK', 'Box', '150', '85', '10'),
(17, '11', 'National Bookstore', 'HBW Ballpoint Pen BLCK', 'Box', '150', '85', '15'),
(18, '12', 'National Bookstore', 'HBW Ballpoint Pen BLCK', 'Box', '150', '85', '10'),
(19, '13', 'National Bookstore', 'HBW Ballpoint Pen BLCK', 'Box', '150', '85', '13'),
(20, '14', 'National Bookstore', 'HBW Ballpoint Pen BLCK', 'Box', '150', '85', '10');

-- --------------------------------------------------------

--
-- Table structure for table `billing_header`
--

CREATE TABLE `billing_header` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `bill_type` varchar(50) NOT NULL,
  `date` date NOT NULL,
  `bill_no` varchar(10) NOT NULL,
  `username` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `billing_header`
--

INSERT INTO `billing_header` (`id`, `full_name`, `bill_type`, `date`, `bill_no`, `username`) VALUES
(1, 'James Ivan', 'Cash', '2025-02-01', '00001', 'admin'),
(2, 'James Ivan', 'Cash', '2025-02-01', '00002', 'admin'),
(3, 'CJ Gunas', 'Cash', '2025-02-01', '00003', 'admin'),
(4, 'Roniel Pena', 'Cash', '2025-02-01', '00004', 'admin'),
(5, 'Ma Jamina Ygrubay', 'Cash', '2025-02-01', '00005', 'admin'),
(6, 'Jake Perales', 'Cash', '2025-02-01', '00006', 'admin'),
(7, 'Fredrich Engel Boral', 'Cash', '2025-02-01', '00007', 'admin'),
(8, 'Sodium Navi', 'Cash', '2025-02-02', '00008', 'admin'),
(9, 'Java Script', 'Cash', '2025-02-02', '00009', 'admin'),
(10, 'Java Script', 'Cash', '2025-02-02', '00010', 'admin'),
(11, 'Zage', 'Cash', '2025-02-02', '00011', 'admin'),
(12, 'Ronii', 'Debit', '2025-02-02', '00012', 'admin'),
(13, 'Ronald', 'Cash', '2025-02-02', '00013', 'admin'),
(14, 'Java Script', 'Cash', '2025-02-02', '00014', 'lala');

-- --------------------------------------------------------

--
-- Table structure for table `company_name`
--

CREATE TABLE `company_name` (
  `id` int(5) NOT NULL,
  `companyname` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `company_name`
--

INSERT INTO `company_name` (`id`, `companyname`) VALUES
(1, 'Tesla'),
(2, 'Samsung'),
(3, 'Hard Copy'),
(4, 'National Bookstore'),
(5, 'Xiaomi'),
(6, 'Coca Cola'),
(7, 'Lenovo'),
(8, 'Nike'),
(9, 'World Balance'),
(10, 'Toyota'),
(11, 'Marby\'s Bakeshop'),
(12, 'Jollibee'),
(13, 'McDonalds'),
(14, 'Foton');

-- --------------------------------------------------------

--
-- Table structure for table `party_info`
--

CREATE TABLE `party_info` (
  `id` int(10) NOT NULL,
  `firstname` varchar(150) NOT NULL,
  `lastname` varchar(150) NOT NULL,
  `businessname` varchar(150) NOT NULL,
  `contact` varchar(150) NOT NULL,
  `address` varchar(500) NOT NULL,
  `city` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `party_info`
--

INSERT INTO `party_info` (`id`, `firstname`, `lastname`, `businessname`, `contact`, `address`, `city`) VALUES
(1, 'Roniel', 'Peña', 'Hamada\'s Dine and Dash Restaurant', '5689965424', ' Paradahan I ', 'Tanza'),
(2, 'CJ', 'Gunas', 'Rick\'s Computer and Auto Repair', '9653325486', 'Hugo Perez', 'Trece Martires'),
(3, 'Ma. Jamina', 'Ygrubay', 'Jammy\'s Fruit Jam and Sandwiches Factory', '9857482130', 'Tejero', 'General Trias'),
(4, 'Fredrich Engel', 'Boral', 'Freddies Pizzaria', '9964251102', 'Sahud Ulan', 'Tanza'),
(5, 'Jake', 'Perales', 'Gotta Go Travel Agency', '9789569231', 'Paliparan II', 'Dasmariñas'),
(6, 'James Ivan', 'Lazo', 'Navi Net Cafe', '9869915241', 'Pasong Kawayan II', 'General Trias');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(15) NOT NULL,
  `company_name` varchar(150) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `unit` varchar(50) NOT NULL,
  `packing_size` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `company_name`, `product_name`, `unit`, `packing_size`) VALUES
(1, 'Tesla', 'Cybertruck', 'Electric Vehicle', '1'),
(2, 'Hard Copy', 'A4 Bond Paper', 'Ream', '5'),
(3, 'National Bookstore', 'Stapler', 'Box', '20'),
(4, 'National Bookstore', 'Stapler Wire', 'Box', '100'),
(5, 'Xiaomi', 'Redmi Note 10 Pro', 'Box', '100'),
(6, 'Marby\'s Bakeshop', 'Ube Hopia', 'Kilogram', '5'),
(7, 'Toyota', 'L300', 'Van', '1'),
(8, 'National Bookstore', 'HBW Ballpoint Pen BLCK', 'Box', '150');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_master`
--

CREATE TABLE `purchase_master` (
  `id` int(15) NOT NULL,
  `company_name` varchar(150) NOT NULL,
  `product_name` varchar(150) NOT NULL,
  `unit` varchar(150) NOT NULL,
  `packing_size` varchar(150) NOT NULL,
  `quantity` varchar(150) NOT NULL,
  `price` varchar(150) NOT NULL,
  `party_name` varchar(150) NOT NULL,
  `purchase_type` varchar(150) NOT NULL,
  `expiry_date` date NOT NULL,
  `purchase_date` date DEFAULT NULL,
  `username` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchase_master`
--

INSERT INTO `purchase_master` (`id`, `company_name`, `product_name`, `unit`, `packing_size`, `quantity`, `price`, `party_name`, `purchase_type`, `expiry_date`, `purchase_date`, `username`) VALUES
(1, 'Tesla', 'Cybertruck', 'Electric Vehicle', '1', '2', '150000', 'Rick\'s Computer and Auto Repair', 'Cash', '2025-02-28', '2025-02-01', 'admin'),
(2, 'National Bookstore', 'Stapler', 'Box', '20', '150', '1500', 'Gotta Go Travel Agency', 'Cash', '2025-05-30', '2025-02-01', 'admin'),
(3, 'National Bookstore', 'Stapler Wire', 'Box', '100', '150', '750', 'Gotta Go Travel Agency', 'Cash', '2025-05-30', '2025-02-01', 'admin'),
(4, 'Marby\'s Bakeshop', 'Ube Hopia', 'Kilogram', '5', '500', '80', 'Jammy\'s Fruit Jam and Sandwiches Factory', 'Cash', '2025-09-28', '2025-02-01', 'admin'),
(5, 'Marby\'s Bakeshop', 'Ube Hopia', 'Kilogram', '5', '500', '80', 'Jammy\'s Fruit Jam and Sandwiches Factory', 'Cash', '2025-09-28', '2025-02-01', 'admin'),
(6, 'National Bookstore', 'HBW Ballpoint Pen BLCK', 'Box', '150', '500', '85', 'Navi Net Cafe', 'Cash', '2025-04-24', '2025-02-01', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `return_products`
--

CREATE TABLE `return_products` (
  `id` int(5) NOT NULL,
  `return_by` varchar(50) NOT NULL,
  `bill_no` varchar(10) NOT NULL,
  `return_date` varchar(15) NOT NULL,
  `product_company` varchar(50) NOT NULL,
  `product_name` varchar(50) NOT NULL,
  `product_unit` varchar(20) NOT NULL,
  `packing_size` varchar(20) NOT NULL,
  `product_price` varchar(10) NOT NULL,
  `product_qty` varchar(10) NOT NULL,
  `total` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `return_products`
--

INSERT INTO `return_products` (`id`, `return_by`, `bill_no`, `return_date`, `product_company`, `product_name`, `product_unit`, `packing_size`, `product_price`, `product_qty`, `total`) VALUES
(1, 'admin', '00002', '2025-02-01', 'National Bookstore', 'Stapler Wire', 'Box', '100', '750', '15', '11250'),
(2, 'admin', '00006', '2025-02-02', 'Tesla', 'Cybertruck', 'Electric Vehicle', '1', '150000', '1', '150000'),
(3, 'admin', '00003', '2025-02-02', 'Marby\'s Bakeshop', 'Ube Hopia', 'Kilogram', '5', '80', '60', '4800');

-- --------------------------------------------------------

--
-- Table structure for table `stock_master`
--

CREATE TABLE `stock_master` (
  `id` int(15) NOT NULL,
  `product_company` varchar(150) NOT NULL,
  `product_name` varchar(150) NOT NULL,
  `product_unit` varchar(150) NOT NULL,
  `packing_size` int(150) NOT NULL,
  `product_qty` varchar(15) NOT NULL,
  `product_selling_price` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stock_master`
--

INSERT INTO `stock_master` (`id`, `product_company`, `product_name`, `product_unit`, `packing_size`, `product_qty`, `product_selling_price`) VALUES
(1, 'Tesla', 'Cybertruck', 'Electric Vehicle', 1, '1', '200000'),
(2, 'National Bookstore', 'Stapler', 'Box', 20, '130', '1500'),
(3, 'National Bookstore', 'Stapler Wire', 'Box', 100, '90', '750'),
(4, 'Marby\'s Bakeshop', 'Ube Hopia', 'Kilogram', 5, '485', '80'),
(5, 'National Bookstore', 'HBW Ballpoint Pen BLCK', 'Box', 150, '369', '85');

-- --------------------------------------------------------

--
-- Table structure for table `units`
--

CREATE TABLE `units` (
  `id` int(5) NOT NULL,
  `unit` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `units`
--

INSERT INTO `units` (`id`, `unit`) VALUES
(1, 'Piece'),
(2, 'Ream'),
(3, 'Kilogram'),
(4, 'Liter'),
(5, 'Gram'),
(6, 'Box'),
(7, 'Pound'),
(8, 'Truck'),
(9, 'Motorcycle'),
(10, 'Van'),
(11, 'Electric Vehicle'),
(12, 'Car');

-- --------------------------------------------------------

--
-- Table structure for table `user_registration`
--

CREATE TABLE `user_registration` (
  `id` int(5) NOT NULL,
  `firstname` varchar(150) NOT NULL,
  `lastname` varchar(150) NOT NULL,
  `username` varchar(150) NOT NULL,
  `password` varchar(150) NOT NULL,
  `role` varchar(10) NOT NULL,
  `status` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_registration`
--

INSERT INTO `user_registration` (`id`, `firstname`, `lastname`, `username`, `password`, `role`, `status`) VALUES
(6, 'Koga', 'admin', 'admin', '$2y$10$Coo1GuBiwxC7r6ZtWoLC5uqdyDbI.Q./7NL62.52ZCKhUsYK3N7aW', 'admin', 'active'),
(7, 'Lazo', 'Navi', 'lala', 'lala', 'user', 'active'),
(8, 'Sodium', 'Na', 'navi', '$2y$10$1Mg3t18xOMhVJvTp3qtkk.5eGYqQP/bLBnq55WH0n7HRMalJ.nmAq', 'admin', 'active'),
(9, 'CJ', 'Gunas', 'Zage', '$2y$10$oVF/3hZAOvIeq0aGYjgU1.20azNZk2vTGl3iswW0wc2Sy7LEKU2ze', 'user', 'active'),
(12, 'Roni', 'Niel', 'gusi', '$2y$10$ta6CdVMtNUASDB2dQxG0Peh1tHMzNNiJLFppNU.ZJFWbYJ2l0yywq', 'user', 'active');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `billing_details`
--
ALTER TABLE `billing_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `billing_header`
--
ALTER TABLE `billing_header`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `company_name`
--
ALTER TABLE `company_name`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `party_info`
--
ALTER TABLE `party_info`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `purchase_master`
--
ALTER TABLE `purchase_master`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `return_products`
--
ALTER TABLE `return_products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `stock_master`
--
ALTER TABLE `stock_master`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `units`
--
ALTER TABLE `units`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_registration`
--
ALTER TABLE `user_registration`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `billing_details`
--
ALTER TABLE `billing_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `billing_header`
--
ALTER TABLE `billing_header`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `company_name`
--
ALTER TABLE `company_name`
  MODIFY `id` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `party_info`
--
ALTER TABLE `party_info`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(15) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `purchase_master`
--
ALTER TABLE `purchase_master`
  MODIFY `id` int(15) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `return_products`
--
ALTER TABLE `return_products`
  MODIFY `id` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `stock_master`
--
ALTER TABLE `stock_master`
  MODIFY `id` int(15) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `units`
--
ALTER TABLE `units`
  MODIFY `id` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `user_registration`
--
ALTER TABLE `user_registration`
  MODIFY `id` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
