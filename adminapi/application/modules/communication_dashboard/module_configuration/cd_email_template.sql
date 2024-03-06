-- phpMyAdmin SQL Dump
-- version 4.6.6deb5
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 14, 2019 at 10:31 AM
-- Server version: 8.0.13
-- PHP Version: 7.2.19-0ubuntu0.18.04.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `490_vfantasy_user`
--

-- --------------------------------------------------------

--
-- Table structure for table `vi_cd_email_template`
--

CREATE TABLE `vi_cd_email_template` (
  `cd_email_template_id` int(11) NOT NULL,
  `template_name` varchar(100) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `notification_type` int(4) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0 deactive 1 active',
  `type` int(8) DEFAULT '0' COMMENT '0=> normal,2=> fixture promotion',
  `email_body` blob,
  `message_body` text,
  `display_label` varchar(200) DEFAULT NULL,
  `date_added` datetime DEFAULT NULL,
  `modified_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;

--
-- Dumping data for table `vi_cd_email_template`
--

INSERT INTO `vi_cd_email_template` (`cd_email_template_id`, `template_name`, `subject`, `notification_type`, `status`, `type`, `email_body`, `message_body`, `display_label`, `date_added`, `modified_date`) VALUES
(1, 'promotion-for-deposit', 'Promotion for Deposit', 120, 1, 1, 0x203c21444f43545950452068746d6c3e0a3c68746d6c3e0a3c686561643e0a3c7374796c653e0a2e456d61696c34207b77696474683a33383770783b206865696768743a36313170783b7d0a0a2e6865792d7468657265207b646973706c61793a666c65783b206a7573746966792d636f6e74656e743a63656e7465723b206261636b67726f756e642d636f6c6f723a233631373737443b77696474683a33383770783b7d0a092e456d61696c34206836207b6865696768743a20333170783b77696474683a2031363470783b636f6c6f723a20234646464646463b3b666f6e742d73697a653a20333070783b20666f6e742d66616d696c793a2073616e732d73657269663b666f6e742d7765696768743a20626f6c643b6c696e652d6865696768743a20333170783b746578742d616c69676e3a2063656e7465723b206d617267696e2d746f703a3272656d3b6d617267696e2d626f74746f6d3a203072656d3b7d0a0a2e646f776e2d776974682d637269636b65742d6665207b746578742d616c69676e3a2063656e7465723b646973706c61793a666c65783b6a7573746966792d636f6e74656e743a63656e7465723b6261636b67726f756e642d636f6c6f723a233631373737443b2077696474683a33383770783b6865696768743a323870783b7d0a092e456d61696c342074657874207b6d617267696e2d746f703a3470783b6865696768743a20323470783b77696474683a2031393270783b636f6c6f723a20234646464646463b3b666f6e742d73697a653a20313670783b666f6e742d66616d696c793a2073616e732d73657269663b6c696e652d6865696768743a20323470783b7d0a0a2e696d616765646976207b6261636b67726f756e642d636f6c6f723a233631373737447d0a0a2e456d61696c34202e73616d706c656d6f6e6579207b77696474683a33383770783b206865696768743a3332312e393970783b7d0a0a2e6f6e6c79646976207b646973706c61793a677269643b6a7573746966792d636f6e74656e743a63656e7465723b6261636b67726f756e642d636f6c6f723a233631373737443b2077696474683a2033383770783b7d0a092e456d61696c34206835207b6865696768743a20353670783b77696474683a2033323470783b636f6c6f723a20234646464646463b666f6e742d73697a653a20313670783b20666f6e742d66616d696c793a2073616e732d73657269663b666f6e742d7765696768743a20626f6c643b6c696e652d6865696768743a20323870783b746578742d616c69676e3a63656e7465723b206d617267696e2d626f74746f6d3a203070783b7d0a0a0a0a2e746f2d706572736f6e616c697a652d796f75727b646973706c61793a677269643b6a7573746966792d636f6e74656e743a63656e7465723b6261636b67726f756e642d636f6c6f723a233631373737443b77696474683a2033383770783b7d0a092e456d61696c34206834207b666f6e742d66616d696c793a2748656c766574696361204e657565273b6d617267696e2d746f703a333070783b6d617267696e2d626f74746f6d3a203172656d3b6865696768743a20323070783b77696474683a2032303270783b636f6c6f723a20234646464646463b666f6e742d73697a653a20313270783b20666f6e742d66616d696c793a2073616e732d73657269663b6c696e652d6865696768743a20323070783b746578742d616c69676e3a2063656e7465723b7d0a0a0a0a2e62746e2d72656374616e676c65207b746578742d616c69676e3a2063656e7465723b646973706c61793a666c65783b6a7573746966792d636f6e74656e743a63656e7465723b6261636b67726f756e642d636f6c6f723a233631373737443b77696474683a2033383770783b207d0a092e62746e2d73756363657373097b6261636b67726f756e643a206c696e6561722d6772616469656e74283133356465672c2072676261283234382c36372c3131302c302e3737292030252c2072676261283234392c38332c38312c302e3832292031303025293b626f782d736861646f773a203020327078203470782030207267626128302c302c302c302e32293b636f6c6f723a20234646464646463b666f6e742d7765696768743a20626f6c643b746578742d616c69676e3a2063656e7465723b666f6e742d73697a653a20323070783b20666f6e742d66616d696c793a2073616e732d73657269663b6865696768743a20333070783b77696474683a2031373670783b626f726465722d7261646975733a203470783b6d617267696e2d626f74746f6d3a353770783b7d0a2e456d61696c34206832207b6865696768743a20313670783b0977696474683a2031323070783b09636f6c6f723a20234646464646463b09666f6e742d66616d696c793a2073616e732d73657269663b09666f6e742d73697a653a20313370783b09666f6e742d7765696768743a203830303b096c696e652d6865696768743a20313670783b09746578742d616c69676e3a2063656e7465723b2070616464696e672d6c6566743a20353770783b706f736974696f6e3a2072656c61746976653b20202020746f703a203470783b7d0a0a090a40666f6e742d66616365207b0a20666f6e742d66616d696c793a2073616e732d73657269663b7d0a0a0a3c2f7374796c653e0a3c2f686561643e0a3c626f64793e0a0a3c64697620636c6173733d22456d61696c34223e0a0a2020202020202020202020202020203c64697620636c6173733d226865792d7468657265223e0a20202020202020202020202020202020202020203c68363e48657920546865726520213c2f68363e0a202020202020202020202020202020203c2f6469763e0a0a202020202020202020202020202020203c6469762020636c6173733d22646f776e2d776974682d637269636b65742d6665223e0a20202020202020202020202020202020202020203c746578743e446f776e207769746820437269636b65742046657665723f3c2f746578743e0a202020202020202020202020202020203c2f6469763e0a0a09093c64697620636c6173733d22696d61676564697622203e0a20202020202020202020202020202020202020203c696d6720636c6173733d202273616d706c656d6f6e657922207372633d227b7b4255434b45545f55524c7d7d726561637461646d696e2f6173736574732f696d672f53414d504c45204d4f4e45592e706e672220202f3e0a202020202020202020202020202020203c2f6469763e0a0a0a202020202020202020202020202020203c64697620636c6173733d226f6e6c79646976223e0a20202020202020202020202020202020202020203c68352020636c6173733d226765742d7878782d65787472612d6f6e2d796f223e4765742078787825206578747261206f6e20796f7572204465706f7369742e2055736520636f6465200a4445504f5349544e4f5721204578636c75736976656c7920666f7220796f75213c2f68353e0a202020202020202020202020202020203c2f6469763e0a0a202020202020202020202020202020203c6469762020636c617373203d22746f2d706572736f6e616c697a652d796f757222203e0a2020202020202020202020202020202020202020203c6834203e58585825206578747261206f6e20796f7572206e657874206465706f7369743c2f68343e3c2f6469763e0a0a09093c64697620636c6173733d2262746e2d72656374616e676c65223e0a2020202020202020202020202020202020202020203c64697620636c6173733d2262746e2d73756363657373223e3c68323e506c6179204e6f773c2f68323e3c2f6469763e0a202020202020202020202020202020203c2f6469763e0a0a0a2020202020202020202020203c2f6469763e0a0a3c2f626f64793e0a3c2f68746d6c3e0a0a, 'Dear {{username}},\nYou are entitled to receive bonus cash on your next deposit. \nUse this promocode: BT3901\nFuntasy11 Team', 'Promotion for Deposit', NULL, NULL),
(2, 'promotion-for-contest', 'promotion-for-contest', 121, 1, 1, 0x203c21444f43545950452068746d6c3e0a3c68746d6c3e0a3c6865616465723e0a3c7374796c653e0a2e456d61696c31207b77696474683a313030253b206865696768743a3530253b7d0a0a2e456d61696c31202e73616d706c656d6f6e6579207b77696474683a3835253b206865696768743a313030253b7d0a0a2e6469642d796f752d6b6e6f77207b646973706c61793a666c65783b206a7573746966792d636f6e74656e743a63656e7465723b7d0a092e456d61696c31206836207b6865696768743a20333670783b77696474683a2032363770783b636f6c6f723a20233030303030303b666f6e742d73697a653a20333270783b20666f6e742d66616d696c793a2073616e732d73657269663b666f6e742d7765696768743a20626f6c643b6c696e652d6865696768743a20333670783b746578742d616c69676e3a2063656e7465723b206d617267696e2d746f703a3272656d3b6d617267696e2d626f74746f6d3a203072656d3b7d0a0a2e677265656e646976207b646973706c61793a666c65783b6a7573746966792d636f6e74656e743a63656e7465727d0a0a2e677265656e72656374616e676c65207b77696474683a383070783b6865696768743a3470783b6261636b67726f756e642d636f6c6f723a20233030413035373b626f726465722d7261646975733a20332e3570783b6d617267696e2d746f703a3172656d3b7d0a0a2e69662d796f752d6a6f696e2d6e616d65207b746578742d616c69676e3a2063656e7465723b646973706c61793a666c65783b6a7573746966792d636f6e74656e743a63656e7465723b7d0a092e456d61696c312074657874207b6d617267696e2d746f703a3172656d3b6865696768743a20363470783b77696474683a2036343270783b636f6c6f723a20233636363636363b666f6e742d73697a653a20323070783b666f6e742d66616d696c793a2073616e732d73657269663b6c696e652d6865696768743a20333270783b7d0a092e456d61696c312062207b636f6c6f723a626c61636b3b666f6e742d73697a653a323670783b666f6e742d66616d696c793a2073616e732d73657269663b7d0a0a2e656e7472792d666565207b646973706c61793a677269643b6a7573746966792d636f6e74656e743a63656e7465723b7d0a092e456d61696c31206835207b206d617267696e2d746f703a3272656d3b6d617267696e2d626f74746f6d3a203072656d3b6865696768743a20323470783b77696474683a20383070783b636f6c6f723a20233636363636363b666f6e742d73697a653a20313670783b20666f6e742d66616d696c793a2073616e732d73657269663b6c696e652d6865696768743a20323470783b746578742d616c69676e3a2063656e7465723b7d200a0a2e6f6e6c79646976207b646973706c61793a677269643b6a7573746966792d636f6e74656e743a63656e7465723b7d0a0a2e6f6e6c79207b6d617267696e2d746f703a302e3572656d3b6d617267696e2d626f74746f6d3a203072656d3b206865696768743a20323670783b77696474683a2031333470783b636f6c6f723a20233030303030303b666f6e742d73697a653a20323870783b20666f6e742d66616d696c793a2073616e732d73657269663b666f6e742d7765696768743a20626f6c643b6c696e652d6865696768743a20323670783b7d0a0a2e62746e2d72656374616e676c65207b746578742d616c69676e3a2063656e7465723b646973706c61793a666c65783b6a7573746966792d636f6e74656e743a63656e7465723b6d617267696e2d746f703a3372656d3b7d0a092e456d61696c3120427574746f6e097b6261636b67726f756e642d636f6c6f723a20233030413035373b636f6c6f723a20234646464646463b666f6e742d7765696768743a20626f6c643b746578742d616c69676e3a2063656e7465723b666f6e742d73697a653a20323070783b20666f6e742d66616d696c793a2073616e732d73657269663b6865696768743a20353670783b77696474683a2034303070783b626f726465722d7261646975733a203470783b626f782d736861646f773a203020327078203470782030207267626128302c302c302c302e32293b7d0a0a2e746f2d706572736f6e616c697a652d796f75727b646973706c61793a677269643b6a7573746966792d636f6e74656e743a63656e7465723b7d0a092e456d61696c31206834207b666f6e742d66616d696c793a2748656c766574696361204e657565273b6d617267696e2d746f703a3372656d3b6d617267696e2d626f74746f6d3a203072656d3b6865696768743a20323270783b77696474683a2035323070783b636f6c6f723a20233636363636363b666f6e742d73697a653a20313470783b20666f6e742d66616d696c793a2073616e732d73657269663b6c696e652d6865696768743a20323270783b746578742d616c69676e3a2063656e7465723b7d0a0a2e636f707972696768742d323031372d66616e207b646973706c61793a677269643b6a7573746966792d636f6e74656e743a63656e7465723b7d0a092e456d61696c31206833207b6865696768743a20313970783b77696474683a2033353270783b636f6c6f723a20233634363436343b666f6e742d73697a653a20313470783b666f6e742d66616d696c793a2073616e732d73657269663b6c696e652d6865696768743a20313970783b746578742d616c69676e3a2063656e7465723b206d617267696e2d626f74746f6d3a203072656d3b0a202020206d617267696e2d746f703a203072656d3b7d0a0a2e736f6369616c2d6d656469612d6c696e6b73207b646973706c61793a666c65783b6a7573746966792d636f6e74656e743a63656e7465723b206d617267696e2d746f703a3272656d3b7d0a092e456d61696c3120696d67207b6d617267696e2d6c6566743a3172656d3b2077696474683a20323470783b206865696768743a20323470783b7d0a090a40666f6e742d66616365207b0a20666f6e742d66616d696c793a2073616e732d73657269663b7d0a0a0a3c2f7374796c653e0a3c2f6865616465723e0a3c626f64793e0a0a3c64697620636c6173733d22456d61696c31223e0a0a202020202020202020202020202020203c6469763e0a20202020202020202020202020202020202020203c696d6720636c6173733d202273616d706c656d6f6e657922207372633d227b7b4255434b45545f55524c7d7d726561637461646d696e2f6173736574732f696d672f53414d504c45204d4f4e45592e706e6722202f3e0a202020202020202020202020202020203c2f6469763e0a0a202020202020202020202020202020203c64697620636c6173733d226469642d796f752d6b6e6f77223e0a20202020202020202020202020202020202020203c68363e44494420594f55204b4e4f573f3c2f68363e0a202020202020202020202020202020203c2f6469763e0a0a202020202020202020202020202020203c64697620636c6173733d22677265656e646976223e0a2020202020202020202020202020202020202020203c64697620636c6173733d22677265656e72656374616e676c65223e3c2f6469763e0a202020202020202020202020202020203c2f6469763e0a0a202020202020202020202020202020203c6469762020636c6173733d2269662d796f752d6a6f696e2d6e616d65223e0a20202020202020202020202020202020202020203c746578743e496620796f75206a6f696e20276e616d652720636f6e746573742c2074686572652077696c6c20626520612020200a20202020202020202020202020202020202020203c623e203333253c2f623e206368616e6365207468617420796f7527642077696e2069742e55736520796f757220637269636b65742065787065727469736520616e6420737461727420746f20706c617920616e642077696e2e203c2f746578743e0a202020202020202020202020202020203c2f6469763e0a0a202020202020202020202020202020203c64697620636c6173733d22656e7472792d666565223e0a20202020202020202020202020202020202020203c68353e456e747279204665653a3c2f68353e0a202020202020202020202020202020203c2f6469763e0a202020202020202020202020202020203c64697620636c6173733d226f6e6c79646976223e0a20202020202020202020202020202020202020203c68362020636c6173733d226f6e6c79223ee282b9323030206f6e6c793c2f68363e0a202020202020202020202020202020203c2f6469763e0a0a202020202020202020202020202020203c64697620636c6173733d2262746e2d72656374616e676c65223e0a2020202020202020202020202020202020202020203c427574746f6e20747970653d22627574746f6e2220636c6173733d2262746e2062746e2d73756363657373223e5669657720436f6e746573743c2f427574746f6e3e0a202020202020202020202020202020203c2f6469763e0a0a202020202020202020202020202020203c6469762020636c617373203d22746f2d706572736f6e616c697a652d796f757222203e0a2020202020202020202020202020202020202020203c6834203e546f20706572736f6e616c697a6520796f757220656d61696c206e6f74696669636174696f6e2073657474696e67732066726f6d2046616e746173792053706f72742c20436c69636b20686572652e3c2f68343e3c2f6469763e0a2020202020202020202020202020202020202020203c64697620636c6173733d22636f707972696768742d323031372d66616e223e0a2020202020202020202020202020202020202020203c68333e436f7079726967687420c2a92032303139207b7b534954455f5449544c457d7d2c20416c6c207269676874732072657365727665642e3c2f68333e0a202020202020202020202020202020203c2f6469763e0a0a202020202020202020202020202020203c6469762020636c6173733d22736f6369616c2d6d656469612d6c696e6b73223e0a2020202020202020202020202020202020202020203c696d67207372633d227b7b4255434b45545f55524c7d7d726561637461646d696e2f6173736574732f696d672f66616365626f6f6b2e706e6722202f3e0a2020202020202020202020202020202020202020203c696d67207372633d227b7b4255434b45545f55524c7d7d726561637461646d696e2f6173736574732f696d672f747769747465722e706e6722202f3e0a2020202020202020202020202020202020202020203c696d67207372633d227b7b4255434b45545f55524c7d7d726561637461646d696e2f6173736574732f696d672f696e7374612e706e6722202f3e0a202020202020202020202020202020203c2f6469763e0a0a2020202020202020202020203c2f6469763e0a0a3c2f626f64793e0a3c2f68746d6c3e0a0a, 'Dear {{username}},\nIf you join \'name\' contest, there will be a  \n33% chance that you\'d win it.Use your cricket expertise and start to play and win.\nFramework Team', 'Promotion for Contest', NULL, NULL),
(3, 'admin-refer-a-friend', '', 123, 1, 1, 0x203c21444f43545950452068746d6c3e0a3c68746d6c3e0a3c6865616465723e0a3c7374796c653e0a2e456d61696c33207b77696474683a313030253b206865696768743a3530253b7d0a0a2e436f6666696573686f706d656574696e67207b77696474683a3835253b206865696768743a313030253b7d0a0a2e72656665722d612d667269656e64207b646973706c61793a666c65783b206a7573746966792d636f6e74656e743a63656e7465723b7d0a2020202020202020202020202e456d61696c33206836207b6865696768743a20333670783b77696474683a2032363570783b636f6c6f723a20233030303030303b666f6e742d73697a653a20333270783b666f6e742d66616d696c793a2073616e732d73657269663b20666f6e742d7765696768743a20626f6c643b6c696e652d6865696768743a20333670783b746578742d616c69676e3a2063656e7465723b206d617267696e2d746f703a3272656d3b206d617267696e2d626f74746f6d3a3070783b7d0a0a2e677265656e646976207b646973706c61793a666c65783b6a7573746966792d636f6e74656e743a63656e7465727d0a0a2e677265656e72656374616e676c65207b77696474683a383070783b6865696768743a3470783b6261636b67726f756e642d636f6c6f723a20233030413035373b626f726465722d7261646975733a20332e3570783b6d617267696e2d746f703a3172656d3b7d0a0a2e6561726e2d706c6179696e672d626f6e7573207b746578742d616c69676e3a2063656e7465723b646973706c61793a666c65783b6a7573746966792d636f6e74656e743a63656e7465723b7d0a092e456d61696c332074657874207b6d617267696e2d746f703a3172656d3b6865696768743a20363470783b77696474683a2036303970783b636f6c6f723a20233636363636363b666f6e742d7765696768743a20626f6c643b666f6e742d73697a653a20323070783b666f6e742d66616d696c793a2073616e732d73657269663b206c696e652d6865696768743a20333270783b7d0a0a2e72656665722d6d6f72652d746f2d6561726e207b746578742d616c69676e3a2063656e7465723b646973706c61793a666c65783b6a7573746966792d636f6e74656e743a63656e7465723b7d0a092e456d61696c33206835207b6d617267696e2d746f703a3172656d3b6865696768743a20333670783b77696474683a2036303970783b636f6c6f723a20233030303030303b666f6e742d7765696768743a20626f6c643b6d617267696e2d626f74746f6d3a3272656d3b666f6e742d73697a653a20323070783b20666f6e742d66616d696c793a2073616e732d73657269663b6c696e652d6865696768743a20333270783b7d0a0a2e62746e2d72656374616e676c65207b746578742d616c69676e3a2063656e7465723b646973706c61793a666c65783b6a7573746966792d636f6e74656e743a63656e7465723b6d617267696e2d746f703a3372656d3b7d0a092e456d61696c3320427574746f6e207b6261636b67726f756e642d636f6c6f723a20233030413035373b636f6c6f723a20234646464646463b666f6e742d7765696768743a20626f6c643b746578742d616c69676e3a2063656e7465723b666f6e742d73697a653a20323070783b20666f6e742d66616d696c793a2073616e732d73657269663b206865696768743a20353670783b77696474683a2034303070783b626f726465722d7261646975733a203470783b626f782d736861646f773a203020327078203470782030207267626128302c302c302c302e32293b7d0a0a0a2e746f2d706572736f6e616c697a652d796f7572207b646973706c61793a677269643b6a7573746966792d636f6e74656e743a63656e7465723b7d0a092e456d61696c33206834207b6d617267696e2d746f703a3372656d3b6865696768743a20323270783b77696474683a2035323070783b636f6c6f723a20233636363636363b666f6e742d73697a653a20313470783b2020666f6e742d66616d696c793a2073616e732d73657269663b6c696e652d6865696768743a20323270783b746578742d616c69676e3a2063656e7465723b206d617267696e2d626f74746f6d3a203070783b7d0a0a2e636f707972696768742d323031372d66616e207b646973706c61793a677269643b6a7573746966792d636f6e74656e743a63656e7465723b7d0a092e456d61696c33206833207b6865696768743a20313970783b77696474683a2033353270783b636f6c6f723a20233634363436343b666f6e742d73697a653a20313470783b2020666f6e742d66616d696c793a2073616e732d73657269663b6c696e652d6865696768743a20313970783b746578742d616c69676e3a2063656e7465723b206d617267696e2d626f74746f6d3a203072656d3b206d617267696e2d746f703a203072656d3b7d0a0a2e736f6369616c2d6d656469612d6c696e6b73207b646973706c61793a666c65783b6a7573746966792d636f6e74656e743a63656e7465723b206d617267696e2d746f703a3272656d3b7d0a092e456d61696c3320696d67207b6d617267696e2d6c6566743a3172656d3b77696474683a20323470783b206865696768743a20323470783b7d0a0a40666f6e742d66616365207b0a20666f6e742d66616d696c793a2073616e732d73657269663b7d0a0a0a0a3c2f7374796c653e0a3c2f6865616465723e0a3c626f64793e0a0a3c64697620636c6173733d22456d61696c33223e0a0a202020202020202020202020202020203c6469763e0a20202020202020202020202020202020202020203c696d6720636c6173733d22436f6666696573686f706d656574696e6722207372633d222f686f6d652f686172736869746a2f4465736b746f702f434f464645455f53484f505f4d454554494e472e706e6722202f3e0a202020202020202020202020202020203c2f6469763e0a0a202020202020202020202020202020203c64697620636c6173733d2272656665722d612d667269656e64223e0a0909093c68363e5265666572206120467269656e643c2f68363e0a202020202020202020202020202020203c2f6469763e0a0a202020202020202020202020202020203c6469762020636c6173733d22677265656e64697622203e0a2020202020202020202020202020202020202020203c64697620636c6173733d22677265656e72656374616e676c65223e3c2f6469763e0a202020202020202020202020202020203c2f6469763e0a0a202020202020202020202020202020203c6469763e0a0a20202020202020202020202020202020202020203c64697620636c6173733d226561726e2d706c6179696e672d626f6e7573223e0a2020202020202020202020202020202020202020202020203c746578743e4561726e20706c6179696e6720626f6e7573206f662052732e20353020666f72206561636820667269656e6420796f752072656665722027617070206e616d65272e20416e6420796f752063616e207265666572206173206d616e7920667269656e647320796f752077616e742e3c2f746578743e0a20202020202020202020202020202020202020203c2f6469763e0a0a20202020202020202020202020202020202020203c64697620636c6173733d2272656665722d6d6f72652d746f2d6561726e223e0a2020202020202020202020202020202020202020202020203c68353e5265666572206d6f726520746f206561726e206d6f726521203c62722f3e48757272792c20746869732069732061206c696d6974656420706572696f64206f666665722120576861742061726520796f752077616974696e6720666f723f3c2f68353e0a20202020202020202020202020202020202020203c2f6469763e2020200a202020202020202020202020202020203c2f6469763e0a0a0a202020202020202020202020202020203c6469762020636c6173733d2262746e2d72656374616e676c65223e0a2020202020202020202020202020202020202020203c427574746f6e20747970653d22627574746f6e2220636c6173733d2262746e2062746e2d73756363657373223e5265666572204e6f773c2f427574746f6e3e0a202020202020202020202020202020203c2f6469763e0a0a202020202020202020202020202020203c64697620636c617373203d22746f2d706572736f6e616c697a652d796f7572223e0a2020202020202020202020202020202020202020203c68343e546f20706572736f6e616c697a6520796f757220656d61696c206e6f74696669636174696f6e2073657474696e67732066726f6d2046616e746173792053706f72742c20436c69636b20686572652e3c2f68343e3c2f6469763e0a2020202020202020202020202020202020202020203c64697620636c6173733d22636f707972696768742d323031372d66616e223e0a2020202020202020202020202020202020202020203c68333e436f7079726967687420c2a920323031392046616e7461737953706f7274732c20416c6c207269676874732072657365727665642e3c2f68333e0a202020202020202020202020202020203c2f6469763e0a0a20202020202020202020202020202020203c6469762020636c6173733d22736f6369616c2d6d656469612d6c696e6b73223e0a2020202020202020202020202020202020202020203c696d67207372633d222f686f6d652f686172736869746a2f4465736b746f702f66616365626f6f6b2e706e6722202f3e0a2020202020202020202020202020202020202020203c696d67207372633d222f686f6d652f686172736869746a2f4465736b746f702f747769747465722e706e6722202f3e0a2020202020202020202020202020202020202020203c696d67207372633d222f686f6d652f686172736869746a2f4465736b746f702f696e7374612e706e6722202f3e0a202020202020202020202020202020203c2f6469763e0a0a2020202020202020202020203c2f6469763e0a3c2f626f64793e0a3c2f68746d6c3e0a, NULL, 'Refer a friend', NULL, NULL),
(4, 'promotion-for-fixture', 'Fixture', 124, 1, 0, 0x636f6e74656e74, NULL, 'Promocode for Fixture', NULL, NULL),
(5, 'cd-email-buy-notify', 'Email Buy Notification', 127, 1, 0, 0x7b7b736974655f7469746c657d7d2068617320626f75676874207b7b616d6f756e747d7d20656d61696c7320746f6461792e, NULL, 'Email buy Notification', NULL, NULL),
(6, 'cd-sms-buy-notify', 'SMS Buy Notification', 128, 1, 0, 0x7b7b736974655f7469746c657d7d2068617320626f75676874207b7b616d6f756e747d7d20534d5320746f6461792e, NULL, 'SMS buy Notification', NULL, NULL),
(7, 'cd-notification-buy-notify', 'Notification Buy Notification', 129, 1, 0, 0x7b7b736974655f7469746c657d7d2068617320626f75676874207b7b616d6f756e747d7d204e6f74696669636174696f6e20746f6461792e, NULL, 'Notification buy Notification', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `vi_cd_email_template`
--
ALTER TABLE `vi_cd_email_template`
  ADD PRIMARY KEY (`cd_email_template_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `vi_cd_email_template`
--
ALTER TABLE `vi_cd_email_template`
  MODIFY `cd_email_template_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;