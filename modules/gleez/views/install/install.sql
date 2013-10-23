
DROP TABLE IF EXISTS {posts};
CREATE TABLE {posts} (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  version bigint(20) unsigned NOT NULL DEFAULT '0',
  author bigint(20) unsigned NOT NULL DEFAULT '0',
  title text NOT NULL,
  body longtext NOT NULL,
  teaser text,
  status varchar(20) NOT NULL DEFAULT 'draft',
  promote tinyint(1) NOT NULL DEFAULT '0',
  moderate tinyint(1) NOT NULL DEFAULT '0',
  sticky tinyint(1) NOT NULL DEFAULT '0',
  type varchar(20) NOT NULL DEFAULT 'post',
  format tinyint(4) NOT NULL DEFAULT '1',
  created int(11) NOT NULL DEFAULT '0',
  updated int(11) NOT NULL DEFAULT '0',
  pubdate int(11) NOT NULL DEFAULT '0',
  password varchar(20) DEFAULT '',
  comment tinyint(4) NOT NULL DEFAULT '0',
  lang varchar(12) NOT NULL DEFAULT 'en',
  layout varchar(255) NOT NULL DEFAULT '',
  image varchar(255) DEFAULT NULL,
  PRIMARY KEY (id),
  KEY `post_type` (`type`),
  KEY `post_type_id` (`type`,`id`),
  KEY `post_type_moderate` (`type`,`moderate`),
  KEY `type_status_date` (`type`,`status`,`created`,`id`),
  KEY `post_frontpage` (`promote`,`status`,`sticky`,`created`),
  KEY `post_author` (`author`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO {posts} (`id`, `version`, `author`, `title`, `body`, `teaser`, `status`, `promote`, `moderate`, `sticky`, `type`, `format`, `created`, `updated`, `pubdate`, `password`, `comment`, `lang`) VALUES
(1, 0, 2, 'Welcome to Gleez - Content Management System!', 'What Is Gleez CMS?\r\n\r\nGleez CMS is a user-friendly website content management system. With Gleez CMS you can easily build dynamic websites within a matter of minutes with just the click of your mouse! Maintain your web content, navigation and even limit what groups or specific users can access, from anywhere in the world with just a web browser! \r\n\r\nWith an emphasis on security and functionality, Gleez CMS is a professional and robust system suitable for any business or organization website. Built on the PHP programming language and the MySQL database, Gleez CMS delivers superb performance on any size website.\r\n\r\nDownload:\r\nwww.gleezcms.org', 'What Is Gleez CMS?\r\n\r\nGleez CMS is a user-friendly website content management system. With Gleez CMS you can easily build dynamic websites within a matter of minutes with just the click of your mouse! Maintain your web content, navigation and even limit what groups or specific users can access, from anywhere in the world with just a web browser!', 'publish', 0, 0, 0, 'page', 1, 1304978011, 1305488194, 1304978011, '', 0, 'en');

DROP TABLE IF EXISTS {tags};
CREATE TABLE {tags} (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  name varchar(64) NOT NULL,
  type varchar(64) NOT NULL DEFAULT 'post',
  count int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (id),
  KEY type (type),
  UNIQUE KEY name_type (name, type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS {terms};
CREATE TABLE {terms} (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  name varchar(64) NOT NULL,
  description varchar(255) DEFAULT NULL,
  image varchar(255) DEFAULT NULL,
  type varchar(64) NOT NULL DEFAULT 'post',
  pid int(11) unsigned NOT NULL DEFAULT '0',
  lft int(10) unsigned DEFAULT NULL,
  rgt int(10) unsigned DEFAULT NULL,
  lvl int(10) unsigned DEFAULT NULL,
  scp int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO {terms} (`id`, `name`, `description`, `image`, `type`, `pid`, `lft`, `rgt`, `lvl`, `scp`) VALUES
(1, 'Pages', 'Use to group pages on similar topics into categories.', NULL, 'page', 0, 1, 2, 1, 1),
(2, 'Blogs', 'Use to group articles on similar topics into categories.', NULL, 'blog', 0, 3, 4, 1, 1);

DROP TABLE IF EXISTS {comments};
CREATE TABLE {comments} (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  post_id bigint(20) unsigned NOT NULL DEFAULT '0',
  author bigint(20) unsigned NOT NULL DEFAULT '1',
  pid bigint(20) unsigned NOT NULL DEFAULT '0',
  title varchar(128) DEFAULT NULL,
  body longtext NOT NULL,
  hostname  varchar(255) DEFAULT NULL,
  created int(11) NOT NULL DEFAULT '0',
  updated int(11) NOT NULL DEFAULT '0',
  status varchar(20) NOT NULL DEFAULT 'draft',
  format tinyint(4) NOT NULL DEFAULT '1',
  thread varchar(255) DEFAULT NULL,
  type varchar(20) NOT NULL DEFAULT 'post',
  guest_name varchar(128) DEFAULT NULL,
  guest_email varchar(128) DEFAULT NULL,
  guest_url varchar(255) DEFAULT NULL,
  karma int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (id),
  KEY comment_status_pid (`status`,pid),
  KEY comment_num_new (post_id, `status`, created, id, thread),
  KEY comment_author (author),
  KEY comment_post_type (post_id, `type`),
  KEY comment_type (`type`),
  KEY comment_post_id  (`post_id`),
  CONSTRAINT {comments_ibfk_1} FOREIGN KEY (`post_id`) REFERENCES {posts} (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS {users};
CREATE TABLE {users} (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  name varchar(60) NOT NULL DEFAULT '',
  pass varchar(128) NOT NULL DEFAULT '',
  mail varchar(254) NOT NULL DEFAULT '',
  homepage varchar(255) DEFAULT NULL,
  bio varchar(800) DEFAULT NULL,
  nick varchar(255) DEFAULT NULL,
  gender tinyint(4) DEFAULT NULL,
  dob int(11) NOT NULL DEFAULT '0',
  theme varchar(255) DEFAULT NULL,
  signature varchar(255) DEFAULT NULL,
  signature_format int(10) unsigned DEFAULT '1',
  logins int(10) unsigned NOT NULL DEFAULT '0',
  created int(11) NOT NULL DEFAULT '0',
  updated int(11) NOT NULL DEFAULT '0',
  access int(11) NOT NULL DEFAULT '0',
  login int(11) NOT NULL DEFAULT '0',
  status tinyint(4) NOT NULL DEFAULT '0',
  timezone varchar(32) DEFAULT 'UTC',
  language varchar(12) DEFAULT 'en_US',
  picture varchar(255) DEFAULT NULL,
  init varchar(254) DEFAULT NULL,
  `hash` char(32) DEFAULT NULL,
  `data` longblob,
  PRIMARY KEY (id),
  UNIQUE KEY mail (mail),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `hash` (`hash`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

INSERT INTO {users} (`id`, `name`, `pass`, `mail`, `nick`, `gender`, `dob`, `theme`, `signature`, `signature_format`, `logins`, `created`, `updated`, `login`, `status`, `timezone`, `language`, `picture`, `init`, `hash`, `data`) VALUES
(1, 'guest', '', 'guest@example.com', 'Guest', NULL, 0, '', '', NULL, 0, 0, 0, 0, 1, 'UTC', 'en_US', '', '', NULL, NULL),
(2, 'admin', 'f06b94fb0479f5596399aa962d9d9f8904d3e09a', 'webmaster@gleez.com', 'Gleez Administrator', NULL, 0, '', '', NULL, 12, 1304109999, 1305386005, 1305386005, 1, 'UTC', 'en_US', '', 'webmaster@gleez.com', NULL, NULL);

DROP TABLE IF EXISTS {config};
CREATE TABLE {config} (
  `group_name` varchar(128) NOT NULL,
  `config_key` varchar(128) NOT NULL,
  `config_value` text NOT NULL,
  PRIMARY KEY (`group_name`,`config_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO {config} (`group_name`, `config_key`, `config_value`) VALUES
('site', 'admin_theme', 's:5:"fluid";'),
('site', 'date_first_day', 's:1:"1";'),
('site', 'date_format', 's:9:"l, F j, Y";'),
('site', 'date_time_format', 's:15:"l, F j, Y - H:i";'),
('site', 'front_page', 's:7:"welcome";'),
('site', 'maintenance_mode', 's:1:"0";'),
('site', 'mission', 's:0:"";'),
('site', 'offline_message', 's:0:"";'),
('site', 'seo_url', 's:1:"1";'),
('site', 'site_email', 's:19:"unknown@unknown.com";'),
('site', 'site_favicon', 's:18:"/media/favicon.ico";'),
('site', 'site_logo', 's:15:"/media/logo.png";'),
('site', 'site_name', 's:9:"Gleez CMS";'),
('site', 'site_slogan', 's:49:"Light, Simple, Flexible Content Management System";'),
('site', 'theme', 's:5:"fluid";'),
('site', 'timezone', 's:12:"Asia/Kolkata";'),
('site', 'time_format', 's:5:"H:i:s";'),
('site', 'gleez_private_key', 's:72:"d6b7050911d1fa78e8f8eb648feacbb61a03805fa62126cbc303cab12dba77067655674c";');

DROP TABLE IF EXISTS {menus};
CREATE TABLE {menus} (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  title varchar(128) NOT NULL,
  name varchar(128) NOT NULL,
  descp varchar(255) DEFAULT NULL,
  image varchar(255) DEFAULT NULL,
  url varchar(255) DEFAULT NULL,
  params text,
  active tinyint(3) NOT NULL DEFAULT '1',
  pid int(11) unsigned NOT NULL DEFAULT '0',
  lft int(10) unsigned DEFAULT NULL,
  rgt int(10) unsigned DEFAULT NULL,
  lvl int(10) unsigned DEFAULT NULL,
  scp int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

INSERT INTO {menus} (`id`, `title`, `name`, `descp`, `image`, `url`, `params`, `active`, `pid`, `lft`, `rgt`, `lvl`, `scp`) VALUES
(1, 'Main Menu', 'main-menu', 'The Main menu is used on many sites to show the major sections of the site, often in a top navigation bar.', NULL, NULL, '', 1, 0, 1, 18, 1, 1),
(2, 'Management', 'management', 'The Management menu contains links for administrative tasks.', NULL, NULL, '', 1, 0, 1, 30, 1, 2),
(3, 'Navigation', 'navigation', 'The Navigation menu contains links intended for site visitors. Links are added to the Navigation menu automatically by some modules.', NULL, NULL, '', 1, 0, 1, 2, 1, 3),
(4, 'User Menu', 'user-menu', 'The User menu contains links related to the user''s account, as well as the ''Log out'' link.', NULL, NULL, '', 1, 0, 1, 4, 1, 4),
(8, 'Home', 'home', '', 'icon-home', '', NULL, 1, 1, 2, 3, 2, 1),
(10, 'Pages', 'pages', '', 'icon-book', 'page', NULL, 1, 1, 4, 9, 2, 1),
(11, 'Add Page', 'add-page', '', NULL, 'page/add', NULL, 1, 10, 7, 8, 3, 1),
(12, 'Contact', 'contact', '', 'icon-envelope', 'contact', NULL, 1, 1, 18, 19, 2, 1),
(13, 'Administer', 'administer', '', 'icon-cog', 'admin', NULL, 1, 2, 2, 3, 2, 2),
(14, 'Menus', 'menus', '', 'icon-bookmark', 'admin/menus', NULL, 1, 2, 6, 7, 2, 2),
(15, 'Blogs', 'blogs', '', 'icon-beer', 'admin/blogs', NULL, 1, 2, 10, 11, 2, 2),
(16, 'Input Formats', 'input-formats', '', 'icon-magnet', 'admin/formats', NULL, 1, 2, 18, 19, 2, 2),
(17, 'Settings', 'settings', '', 'icon-cogs', 'admin/settings', NULL, 1, 2, 28, 29, 2, 2),
(18, 'Path Alias', 'path-alias', '', 'icon-link', 'admin/paths', NULL, 1, 2, 20, 21, 2, 2),
(19, 'Widgets', 'widgets', '', 'icon-asterisk', 'admin/widgets', NULL, 1, 2, 26, 27, 2, 2),
(20, 'Taxonomy', 'taxonomy', '', 'icon-folder-open', 'admin/taxonomy', NULL, 1, 2, 14, 15, 2, 2),
(21, 'Tags', 'tags', '', 'icon-tags', 'admin/tags', NULL, 1, 2, 16, 17, 2, 2),
(22, 'Modules', 'modules', '', 'icon-list-alt', 'admin/modules', NULL, 1, 2, 4, 5, 2, 2),
(23, 'Users', 'users', '', 'icon-user', 'admin/users', NULL, 1, 2, 22, 23, 2, 2),
(24, 'Roles', 'roles', '', 'icon-retweet', 'admin/roles', NULL, 1, 2, 24, 25, 2, 2),
(25, 'Pages', 'admin-pages', '', 'icon-book', 'admin/pages', NULL, 1, 2, 8, 9, 2, 2),
(26, 'Comments', 'admin-comment', '', 'icon-comment', 'admin/comments', NULL, 1, 2, 12, 13, 2, 2),
(27, 'Login', 'user-login', '', NULL, '', NULL, 1, 4, 2, 3, 2, 4),
(28, 'Blogs', 'blogs-1', '', 'icon-beer', '#', NULL, 1, 1, 10, 15, 2, 1),
(29, 'Add Blog', 'add-blog', '', NULL, 'blog/add', NULL, 1, 28, 13, 14, 3, 1),
(30, 'List', 'list', '', NULL, 'page', NULL, 1, 10, 5, 6, 3, 1),
(31, 'List', 'list-1', '', NULL, 'blog', NULL, 1, 28, 11, 12, 3, 1),
(32, 'Contact', 'contact-1', '', 'icon-envelope-alt', 'contact', NULL, 1, 1, 16, 17, 2, 1);

DROP TABLE IF EXISTS {modules};
CREATE TABLE {modules} (
  id int(11) NOT NULL AUTO_INCREMENT,
  name varchar(128) NOT NULL,
  active tinyint(4) NOT NULL DEFAULT '0',
  weight int(11) NOT NULL DEFAULT '0',
  version decimal(10,2) NOT NULL DEFAULT '0',
  path varchar(255) DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

INSERT INTO {modules} (`id`, `name`, `active`, `weight`, `version`, `path`) VALUES
(1, 'user', 1, 0, '2', NULL);

DROP TABLE IF EXISTS {paths};
CREATE TABLE {paths} (
  id int(11) NOT NULL AUTO_INCREMENT,
  source varchar(255) NOT NULL,
  alias varchar(255) NOT NULL,
  lang varchar(12) NOT NULL DEFAULT 'und',
  route_name varchar(255) DEFAULT NULL,
  route_directory varchar(255) DEFAULT NULL,
  route_controller varchar(255) DEFAULT NULL,
  route_action varchar(255) DEFAULT NULL,
  route_id varchar(255) DEFAULT NULL,
  PRIMARY KEY (id),
  KEY id_lang_alias (lang,alias,id),
  KEY id_source (`source`),
  KEY id_lang_path (lang,`source`,id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

INSERT INTO {paths} (`id`, `source`, `alias`, `lang`, `route_name`, `route_directory`, `route_controller`, `route_action`, `route_id`) VALUES
(1, 'rss', 'rss.xml', 'und', 'rss', 'feeds', 'base', 'index', NULL),
(2, 'welcome', '<front>', 'und', 'default', NULL, 'welcome', 'index', NULL),
(3, 'user/login', 'login', 'und', 'user', NULL, 'user', 'login', NULL);

DROP TABLE IF EXISTS {permissions};
CREATE TABLE {permissions} (
  rid int(11) NOT NULL,
  permission varchar(64) NOT NULL,
  module varchar(255) NOT NULL,
  PRIMARY KEY (rid,permission),
  KEY permission (permission)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO {permissions} (`rid`, `permission`, `module`) VALUES
(1, 'access content', 'content'),
(1, 'access profiles', 'user'),
(1, 'sending mail', 'contact'),
(3, 'access content', 'content'),
(3, 'access profiles', 'user'),
(3, 'create page', 'content'),
(3, 'edit own comment', 'comment'),
(3, 'edit own page', 'content'),
(3, 'edit profile', 'user'),
(3, 'post comment', 'comment'),
(3, 'view own unpublished content', 'content'),
(3, 'sending mail', 'contact'),
(4, 'access comment', 'comment'),
(4, 'access content', 'content'),
(4, 'access profiles', 'user'),
(4, 'administer comment', 'comment'),
(4, 'administer content', 'content'),
(4, 'administer logs', 'site'),
(4, 'administer page', 'content'),
(4, 'administer paths', 'site'),
(4, 'administer permissions', 'user'),
(4, 'administer site', 'site'),
(4, 'administer tags', 'site'),
(4, 'administer terms', 'site'),
(4, 'administer users', 'user'),
(4, 'change own username', 'user'),
(4, 'create page', 'content'),
(4, 'delete any page', 'content'),
(4, 'delete own page', 'content'),
(4, 'edit any page', 'content'),
(4, 'edit own comment', 'comment'),
(4, 'edit own page', 'content'),
(4, 'edit profile', 'user'),
(4, 'post comment', 'comment'),
(4, 'skip comment approval', 'comment'),
(4, 'view own unpublished content', 'content'),
(4, 'sending mail', 'contact');

DROP TABLE IF EXISTS {posts_versions};
CREATE TABLE {posts_versions} (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `post_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `version` bigint(20) unsigned NOT NULL DEFAULT '0',
  `author`  bigint(20) unsigned NOT NULL DEFAULT '1',
  `title`  text NOT NULL,
  `body`   longtext NOT NULL,
  teaser text,
  status varchar(20) NOT NULL DEFAULT 'draft',
  promote tinyint(1) NOT NULL DEFAULT '0',
  moderate tinyint(1) NOT NULL DEFAULT '0',
  sticky tinyint(1) NOT NULL DEFAULT '0',
  type varchar(20) NOT NULL DEFAULT 'post',
  format tinyint(4) NOT NULL DEFAULT '1',
  created int(11) NOT NULL DEFAULT '0',
  updated int(11) NOT NULL DEFAULT '0',
  pubdate int(11) NOT NULL DEFAULT '0',
  password varchar(20) DEFAULT '',
  comment tinyint(4) NOT NULL DEFAULT '0',
  lang varchar(12) NOT NULL DEFAULT 'en',
  layout varchar(255) NOT NULL,
  image varchar(255) DEFAULT NULL,
  version_log varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `post_id` (`post_id`),
  KEY `post_author` (`author`),
  CONSTRAINT {posts_versions_ibfk_1} FOREIGN KEY (`post_id`) REFERENCES {posts} (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS {posts_tags};
CREATE TABLE {posts_tags} (
  post_id bigint(20) unsigned NOT NULL DEFAULT '0',
  tag_id bigint(20) unsigned NOT NULL DEFAULT '0',
  author bigint(20) NOT NULL DEFAULT '1',
  type varchar(20) NOT NULL DEFAULT 'post',
  created int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (post_id,tag_id),
  KEY fk_tag_id (tag_id),
  CONSTRAINT {posts_tags_ibfk_1} FOREIGN KEY (`post_id`) REFERENCES {posts} (`id`) ON DELETE CASCADE,
  CONSTRAINT {posts_tags_ibfk_2} FOREIGN KEY (`tag_id`)  REFERENCES {tags}  (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS {posts_terms};
CREATE TABLE {posts_terms} (
  post_id bigint(20) unsigned NOT NULL DEFAULT '0',
  term_id bigint(20) unsigned NOT NULL DEFAULT '0',
  type varchar(20) NOT NULL DEFAULT 'post',
  parent_id bigint(20) NOT NULL DEFAULT '0',
  term_order int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (post_id,term_id),
  KEY fk_term_id (term_id),
  KEY `type` (`type`),
  KEY posts_terms_ibfk_1 (post_id,`type`),
  CONSTRAINT {posts_terms_ibfk_1} FOREIGN KEY (`post_id`) REFERENCES {posts} (`id`) ON DELETE CASCADE,
  CONSTRAINT {posts_terms_ibfk_2} FOREIGN KEY (`term_id`) REFERENCES {terms} (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS {roles};
CREATE TABLE {roles} (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  name varchar(32) NOT NULL,
  description varchar(255) DEFAULT NULL,
  special tinyint(1) DEFAULT '0',
  PRIMARY KEY (id),
  UNIQUE KEY uniq_name (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO {roles} (`id`, `name`, `description`, `special`) VALUES
(1, 'Anonymous', 'Guests can only view content. Anyone browsing the site who is not signed in is considered to be a "Guest".', 1),
(2, 'login', 'Login privileges, pending account confirmation.', 1),
(3, 'user', 'Member privileges, granted after account confirmation.', 1),
(4, 'admin', 'Administrative user, has access to everything.', 1);

DROP TABLE IF EXISTS {roles_users};
CREATE TABLE {roles_users} (
  user_id bigint(20) unsigned NOT NULL,
  role_id int(11) unsigned NOT NULL,
  PRIMARY KEY (user_id,role_id),
  KEY fk_role_id (role_id),
  CONSTRAINT {roles_users_ibfk_1} FOREIGN KEY (`user_id`) REFERENCES {users} (`id`) ON DELETE CASCADE,
  CONSTRAINT {roles_users_ibfk_2} FOREIGN KEY (`role_id`) REFERENCES {roles} (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO {roles_users} (`user_id`, `role_id`) VALUES
(1, 1),
(2, 2),
(2, 3),
(2, 4);

DROP TABLE IF EXISTS {sessions};
CREATE TABLE {sessions} (
  session_id varchar(24) NOT NULL,
  last_active int(10) unsigned NOT NULL,
  contents longtext NOT NULL,
  hostname varchar(128) DEFAULT '',
  user_id int(11) DEFAULT '0',
  PRIMARY KEY (session_id),
  KEY last_active (last_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS {user_tokens};
CREATE TABLE {user_tokens} (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  user_id bigint(20) unsigned NOT NULL,
  user_agent varchar(40) NOT NULL,
  token varchar(40) NOT NULL,
  `type` varchar(100) DEFAULT NULL,
  created int(11) unsigned NOT NULL DEFAULT '0',
  expires int(11) unsigned NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_token (token),
  KEY fk_user_id (user_id),
  CONSTRAINT {user_tokens_ibfk_1} FOREIGN KEY (`user_id`) REFERENCES {users} (`id`) ON DELETE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS {widgets};
CREATE TABLE {widgets} (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `module` varchar(64) NOT NULL,
  `theme` varchar(64) DEFAULT NULL,
  `status` tinyint(1) DEFAULT '0',
  `region` varchar(64) DEFAULT '-1',
  `weight` int(11) NOT NULL DEFAULT '0',
  `cache` tinyint(4) NOT NULL DEFAULT '0',
  `visibility` tinyint(4) NOT NULL DEFAULT '0',
  `pages` text DEFAULT NULL,
  `roles` varchar(255) DEFAULT NULL,
  `show_title` tinyint(1) DEFAULT '1',
  `body` longtext,
  `format` tinyint(3) NOT NULL DEFAULT '1',
  `icon` varchar(255) DEFAULT 'icon-none',
  PRIMARY KEY (`id`),
  KEY `fk_name` (`name`),
  KEY `fk_module` (`module`),
  KEY `fk_status` (`status`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

INSERT INTO {widgets} (`id`, `name`, `title`, `module`, `theme`, `status`, `region`, `weight`, `cache`, `visibility`, `pages`, `roles`, `show_title`, `body`, `format`, `icon`) VALUES
(1, 'admin/donate', 'Donate', 'gleez', NULL, 1, 'right', -5, 0, 0, '', '4', 1, NULL, 1, 'icon-gift'),
(2, 'menu/main-menu', 'Main Menu', 'gleez', NULL, 1, '-1', -3, 0, 0, NULL, NULL, 1, NULL, 0, 'icon-retweet'),
(3, 'menu/management', 'Management', 'gleez', NULL, 1, 'right', -2, 0, 0, '', '4', 1, NULL, 0, 'icon-cog'),
(4, 'menu/navigation', 'Navigation', 'gleez', NULL, 0, '-1', -6, 0, 0, NULL, NULL, 1, NULL, 0, 'icon-asterisk'),
(5, 'menu/user-menu', 'User Menu', 'gleez', NULL, 0, '-1', -5, 0, 0, NULL, NULL, 1, NULL, 0, 'icon-none'),
(6, 'admin/welcome', 'Welcome', 'gleez', NULL, 1, 'dashboard', -6, 0, 0, NULL, NULL, 1, NULL, 0, 'icon-flag'),
(7, 'admin/info', 'System', 'gleez', NULL, 1, 'dashboard', -3, 0, 0, NULL, NULL, 1, NULL, 0, 'icon-pushpin'),
(8, 'user/login', 'Login', 'user', NULL, 1, 'right', -4, 0, 0, NULL, NULL, 1, NULL, 0, 'icon-lock'),
(9, 'comment/recent', 'Comments', 'gleez', NULL, 0, '-1', -4, 0, 0, NULL, NULL, 1, NULL, 0, 'icon-comment'),
(10, 'admin/shortcut', 'Quick Shortcuts', 'gleez', NULL, 1, 'dashboard', -5, 0, 0, NULL, NULL, 1, NULL, 0, 'icon-bookmark'),
(11, 'blog/recent',	'Recent Blogs',	'gleez',	NULL,	0,	'-1',	0,	0,	0,	NULL,	NULL,	1,	NULL,	1,	'icon-book'),
(12, 'blog/announce',	'Announce of Recent Blogs',	'gleez',	NULL,	0,	'-1',	0,	0,	0,	NULL,	NULL,	1,	NULL,	1,	'icon-book');

DROP TABLE IF EXISTS {identities};
CREATE TABLE {identities} (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `provider` varchar(32) NOT NULL,
  `provider_id` varchar(128) NOT NULL,
  `refresh_token` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `provider` (`provider`),
  KEY `provider_id` (`provider`, `provider_id`),
  UNIQUE KEY `user_provider_id` (`user_id`, `provider`, `provider_id`),
  CONSTRAINT {identities_ibfk_1} FOREIGN KEY (`user_id`) REFERENCES {users} (`id`) ON DELETE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS {sitemaps};
CREATE TABLE {sitemaps} (
  id bigint(20) unsigned NOT NULL DEFAULT '0',
  loc varchar(255) NOT NULL,
  lastmod int(11) unsigned NOT NULL DEFAULT '0',
  priority float NOT NULL DEFAULT '0.5',
  changefreq int(10) unsigned NOT NULL DEFAULT '0',
  status tinyint(4) NOT NULL DEFAULT '1',
  type varchar(20) NOT NULL DEFAULT 'post',
  PRIMARY KEY (`id`,`type`),
  KEY `loc` (`loc`),
  KEY `status_loc` (`status`,`loc`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS {buddies};
CREATE TABLE IF NOT EXISTS {buddies} (
    `user_id` bigint(20) UNSIGNED NOT NULL,
    `buddy_id` bigint(20) UNSIGNED NOT NULL,
    PRIMARY KEY (`user_id`,`buddy_id`),
    KEY `buddy_fk_1` (`user_id`),
    KEY `buddy_fk_2` (`buddy_id`),
    CONSTRAINT {buddy_ibfk_1} FOREIGN KEY (`user_id`) REFERENCES {users} (`id`) ON DELETE CASCADE,
    CONSTRAINT {buddy_ibfk_2} FOREIGN KEY (`buddy_id`) REFERENCES {users} (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS {buddy_requests};
CREATE TABLE IF NOT EXISTS {buddy_requests} (
    `id` INT(11) UNSIGNED NOT NULL,
    `request_from` bigint(20) UNSIGNED NOT NULL,
    `request_to` bigint(20) UNSIGNED NOT NULL,
    `accepted` INT(1) UNSIGNED NOT NULL DEFAULT '0',
    `date_requested` int(11) NOT NULL DEFAULT '0',
    `date_accepted` int(11) NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`),
    KEY `buddy_requests_fk_1` (`request_from`),
    KEY `buddy_requests_fk_2` (`request_to`),
    CONSTRAINT {buddy_requests_ibfk_1} FOREIGN KEY (`request_from`) REFERENCES {users} (`id`) ON DELETE CASCADE,
    CONSTRAINT {buddy_requests_ibfk_2} FOREIGN KEY (`request_to`) REFERENCES {users} (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
