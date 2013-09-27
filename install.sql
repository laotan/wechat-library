-- phpMyAdmin SQL Dump
-- version 3.3.8.1

--
-- 表的结构 `book_list`
--

CREATE TABLE IF NOT EXISTS `book_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `douban_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `image` varchar(255) NOT NULL,
  `summary` text NOT NULL,
  `book_from` varchar(255) DEFAULT NULL,
  `state` varchar(255) DEFAULT NULL,
  `update_time` timestamp NULL DEFAULT NULL,
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `douban_id` (`douban_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=35 ;

-- --------------------------------------------------------

--
-- 表的结构 `book_user`
--

CREATE TABLE IF NOT EXISTS `book_user` (
  `openid` varchar(255) NOT NULL COMMENT '微信唯一openid',
  `name` varchar(255) NOT NULL COMMENT '绑定的自定义名称',
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`openid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
