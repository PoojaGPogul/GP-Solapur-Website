CREATE TABLE IF NOT EXISTS `#__simplephotogallery_album` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `album_name` varchar(200) NOT NULL,
  `alias_name` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `published` tinyint(1) NOT NULL,
  `created_date` datetime NOT NULL,
  `modified_date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM ;


CREATE TABLE IF NOT EXISTS `#__simplephotogallery_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `image` varchar(200) NOT NULL,
  `alias_name` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `album_id` int(11) NOT NULL,
  `sortorder` int(3) NOT NULL,
  `is_featured` tinyint(1) NOT NULL,
  `published` tinyint(1) NOT NULL,
  `album_cover` tinyint(1) NOT NULL,
  `create_date` datetime NOT NULL,
  `modified_date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  ;

CREATE TABLE IF NOT EXISTS `#__simplephotogallery_settings` (
  `id` int(11) NOT NULL DEFAULT '1',
  `feat_cols` int(11) NOT NULL DEFAULT '4',
  `feat_rows` int(11) NOT NULL DEFAULT '4',
  `feat_photo_width` int(11) NOT NULL DEFAULT '144',
  `feat_photo_height` int(11) NOT NULL DEFAULT '144',
  `feat_vspace` int(11) NOT NULL DEFAULT '6',
  `feat_hspace` int(11) NOT NULL DEFAULT '6',
  `alb_photo_width` int(11) NOT NULL DEFAULT '166',
  `alb_photo_height` int(11) NOT NULL DEFAULT '166',
  `alb_vspace` int(11) NOT NULL DEFAULT '6',
  `alb_hspace` int(11) NOT NULL DEFAULT '6',
  `facebook_api_id` varchar(100) NOT NULL,
  `general_share_photo` int(11) NOT NULL DEFAULT '1',
  `general_download` int(2) NOT NULL DEFAULT '1',
  `general_show_alb` int(2) NOT NULL DEFAULT '1',
  `facebook_api` varchar(100) NOT NULL,
  `thumbimg_width` int(11) NOT NULL DEFAULT '150',
  `thumbimg_height` int(11) NOT NULL DEFAULT '150',
  `photo_vspace` int(1) NOT NULL DEFAULT '15',
  `photo_hspace` int(1) NOT NULL DEFAULT '7',
  `fullimg_width` int(11) NOT NULL DEFAULT '600',
  `fullimg_height` int(11) NOT NULL DEFAULT '370',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  ;



INSERT INTO `#__simplephotogallery_settings` (`id`, `feat_cols`, `feat_rows`, `feat_photo_width`, `feat_photo_height`, `feat_vspace`, `feat_hspace`, `alb_photo_width`, `alb_photo_height`, `alb_vspace`, `alb_hspace`, `general_share_photo`, `general_download`, `general_show_alb`, `facebook_api`, `thumbimg_width`, `thumbimg_height`, `photo_vspace`, `photo_hspace`, `fullimg_width`, `fullimg_height`) VALUES
(1, 4, 4, 144, 144, 6, 6, 150, 150, 6, 6, 1, 1, 1, '', 150, 190, 15, 7, 550, 370) ON DUPLICATE KEY UPDATE id=id;


