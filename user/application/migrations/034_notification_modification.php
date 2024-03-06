<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Notification_modification extends CI_Migration {

	public function up() {
		//updateding notification text Daily Check-In
        $sql = "UPDATE ".$this->db->dbprefix(NOTIFICATION_DESCRIPTION)." SET `message` = 'You have recieved {{amount}} coins for Daily Check-In Day {{day_number}}',en_message='You have recieved {{amount}} coins for Daily Check-In Day {{day_number}}' WHERE `notification_type` = 138;";
        $this->db->query($sql);
        //updateding collection name in game join notification
        $sql = "UPDATE ".$this->db->dbprefix(NOTIFICATION_DESCRIPTION)." SET 
        `message` = 'Game {{contest_name}} – {{collection_name}} joined successfully',
        en_message='Game {{contest_name}} – {{collection_name}} joined successfully',
        hi_message='आपने खेल​ {{contest_name}} - {{collection_name}} में सफलतापूर्वक प्रवेश लिया है।',
        guj_message='તમે સફળતાપૂર્વક રમત દાખલ કરેલ {{contest_name}} - {{collection_name}}.',
        ben_message='খেলা {{contest_name}} - {{collection_name}} সফলভাবে যোগদান',
        fr_message='Jeu {{contest_name}} - {{collection_name}} avec succès',
        pun_message='ਗੇਮ {{contest_name}} - {{collection_name}} ਸਫਲਤਾਪੂਰਵਕ ਸ਼ਾਮਲ ਹੋਵੋ'
        WHERE `notification_type` = 1;";
        $this->db->query($sql);
        //updateing email templated name from promocode to promotion
        $sql = "UPDATE ".$this->db->dbprefix(CD_EMAIL_TEMPLATE)." SET 
		template_name='promotion-for-fixture'
		WHERE cd_email_template_id = 4";
        $this->db->query($sql);
        
        $sql =  "INSERT INTO ".$this->db->dbprefix(CD_EMAIL_TEMPLATE)." (`cd_email_template_id`, `category_id`, `template_name`, `subject`, `notification_type`, `status`, `type`, `email_body`, `message_body`, `message_url`, `redirect_to`, `message_type`, `display_label`, `date_added`, `modified_date`) VALUES
        (NULL, 2, 'promotion-for-contest', 'Are you up for some crazy contests', 121, 1, 0, 0x0a3c21444f43545950452068746d6c205055424c494320222d2f2f5733432f2f445444205848544d4c20312e30205472616e736974696f6e616c2f2f454e222022687474703a2f2f7777772e77332e6f72672f54522f7868746d6c312f4454442f7868746d6c312d7472616e736974696f6e616c2e647464223e0a3c68746d6c20786d6c6e733d22687474703a2f2f7777772e77332e6f72672f313939392f7868746d6c223e0a20203c686561643e0a20202020202020203c6d657461206e616d653d2276696577706f72742220636f6e74656e743d2277696474683d6465766963652d77696474682c20696e697469616c2d7363616c653d312c20757365722d7363616c61626c653d6e6f223e20200a20202020202020203c6d65746120687474702d65717569763d22636f6e74656e742d747970652220636f6e74656e743d22746578742f68746d6c3b20636861727365743d7574662d38223e0a20202020202020203c6d65746120687474702d65717569763d22636f6e74656e742d747970652220636f6e74656e743d22746578742f68746d6c3b20636861727365743d49534f2d383835392d3135223e0a20202020202020203c7469746c653e456d61696c657220343c2f7469746c653e0a202020200a20203c7374796c653e0a202020202e6d61696e2d7461626c657b0a202020202020202077696474683a2038303070783b0a20202020202020206261636b67726f756e642d636f6c6f723a20236666666666663b0a20202020202020206d617267696e3a2030206175746f3b0a2020202020202020626f726465723a206e6f6e653b0a202020207d0a2020202074647b0a2020202020202020626f726465723a206e6f6e653b0a202020207d0a202020202e62616e6e657220696d677b0a20202020202020206d61782d77696474683a20313030253b0a202020207d0a0a202020202f2a202053746172742048656164657220637373202a2f0a2020202074722e6c6f676f2d7472207b6865696768743a20343470783b7d0a2020202074722e6c6f676f2d747220617b636f6c6f723a20233030303030303b666f6e742d73697a653a20313370783b20206c696e652d6865696768743a20313270783b746578742d6465636f726174696f6e3a206e6f6e653b7d0a202020202e6c6f676f2d74647b77696474683a203235253b70616464696e673a203020333070783b626f726465723a31707820736f6c69642020233043424645422021696d706f7274616e743b626f726465722d72696768743a6e6f6e652021696d706f7274616e747d0a202020202e6c696e6b2d74647b77696474683a203735253b70616464696e673a203020333070783b746578742d616c69676e3a2072696768743b626f726465723a31707820736f6c69642020233043424645422021696d706f7274616e743b2020626f726465722d6c6566743a6e6f6e652021696d706f7274616e747d0a2f2a2020456e642048656164657220637373202a2f0a202f2a2020537461727420466f6f74657220637373202a2f0a202e666f6f7465722d74727b0a20202020202020206261636b67726f756e642d636f6c6f723a20234632463246323b200a202020207d0a202020202e626f6f746f6d7b0a202020202020202070616464696e673a203235707820313570783b0a202020207d0a202020202e626f6f746f6d2d6e61767b0a202020202020202070616464696e672d6c6566743a20303b0a20202020202020206c6973742d7374796c652d747970653a206e6f6e653b0a20202020202020206d617267696e3a20303b0a202020207d0a202020202e626f6f746f6d2d6e6176206c697b0a2020202020202020646973706c61793a20696e6c696e652d626c6f636b3b0a202020202020202070616464696e673a20387078203570783b0a202020207d0a202020202e626f6f746f6d2d6e6176206c6920617b0a2020202020202020666f6e742d73697a653a20313670783b0a20202020202020206c696e652d6865696768743a20313970783b0a2020202020202020636f6c6f723a20233636363636363b0a2020202020202020746578742d6465636f726174696f6e3a206e6f6e653b0a202020207d0a202020202e636f707972696768742d707b0a2020202020202020636f6c6f723a20233535353535353b090a2020202020202020666f6e742d73697a653a20313670783b090a20202020202020206c696e652d6865696768743a20313970783b0a202020207d0a202020202e636f707972696768742d70207370616e7b0a2020202020202020666f6e742d73697a653a20313670783b0a2020202020202020666f6e742d7765696768743a203630303b0a202020207d0a202020202e636f707972696768742d7020696d677b0a2020202020202020766572746963616c2d616c69676e3a206d6964646c653b0a20202020202020206d617267696e2d6c6566743a203770783b0a202020207d0a202020200a202020202e6d2d307b0a20202020202020206d617267696e3a20302021696d706f7274616e743b0a202020207d0a202020202e746578742d63656e7465727b0a2020202020202020746578742d616c69676e3a2063656e7465723b0a202020207d0a20202020612e696e766974652d62746e7b0a2020202020202020626f726465723a2031707820736f6c696420233043424645423b0a2020202020202020636f6c6f723a20233043424645423b0a2020202020202020666f6e742d73697a653a20313270783b090a2020202020202020746578742d6465636f726174696f6e3a206e6f6e653b0a202020202020202070616464696e673a2037707820323570783b0a2020202020202020666f6e742d7765696768743a20626f6c643b090a20202020202020206c696e652d6865696768743a20323170783b0a2020202020202020746578742d7472616e73666f726d3a207570706572636173653b0a202020207d0a202020202e696e766974652d707b0a2020202020202020636f6c6f723a20233535353535353b090a2020202020202020666f6e742d73697a653a20313170783b090a20202020202020206c696e652d6865696768743a20313970783b0a202020207d0a202020202f2a20456e6420466f6f74657220637373202a2f0a202020202e6d6964646c652d636f6e74656e742068317b0a2020202020202020636f6c6f723a20233030303030303b090a2020202020202020666f6e742d73697a653a20333270783b090a2020202020202020666f6e742d7765696768743a20626f6c643b090a20202020202020206c696e652d6865696768743a20333670783b090a2020202020202020746578742d7472616e73666f726d3a207570706572636173653b0a2020202020202020746578742d616c69676e3a2063656e7465723b0a20202020202020206d617267696e3a2033357078203020313570783b0a202020207d0a202020202e626f726465727b0a20202020202020206865696768743a203470783b090a202020202020202077696474683a20383070783b090a2020202020202020626f726465722d7261646975733a20332e3570783b090a20202020202020206261636b67726f756e642d636f6c6f723a20233030413035373b0a20202020202020206d617267696e3a2031307078206175746f3b0a202020207d0a202020202e6d6964646c652d636f6e74656e7420707b0a2020202020202020636f6c6f723a20233636363636363b0a2020202020202020666f6e742d73697a653a20323270783b0a20202020202020206c696e652d6865696768743a20333270783b090a20202020202020206d617267696e3a203335707820303b0a2020202020202020746578742d616c69676e3a2063656e7465723b0a202020207d0a202020202e6d6964646c652d636f6e74656e742070207370616e7b0a2020202020202020636f6c6f723a20233030303030303b0a2020202020202020666f6e742d73697a653a20323370783b0a2020202020202020666f6e742d7765696768743a20626f6c643b0a202020207d0a202020202e626f6f6b696e672d62746e2d777261707065727b0a2020202020202020746578742d616c69676e3a2063656e7465723b0a20202020202020206d617267696e3a202035307078206175746f3b0a202020207d0a20202020612e626f6f6b696e672d62746e7b0a2020202020202020636f6c6f723a20234646464646463b090a2020202020202009666f6e742d73697a653a20323070783b090a20202020202020206c696e652d6865696768743a20323570783b0a202020202020202070616464696e673a20313770782031353670783b0a2020202020202020666f6e742d7765696768743a203630303b0a2020202020202020746578742d6465636f726174696f6e3a206e6f6e653b0a2020202020202020626f726465722d7261646975733a203470783b090a20202020202020206261636b67726f756e642d636f6c6f723a20233030413035373b090a2020202020202020626f782d736861646f773a203020327078203470782030207267626128302c302c302c302e32293b0a202020207d0a202020202e6c6173742d636f6e74656e747b0a202020202020202070616464696e673a203430707820303b0a202020207d0a202020202e6c6173742d636f6e74656e7420707b0a2020202020202020636f6c6f723a20233636363636363b0a2020202020202020666f6e742d73697a653a20313470783b090a20202020202020206c696e652d6865696768743a20323270783b0a20202020202020206d617267696e3a203570783b0a2020202020202020746578742d616c69676e3a2063656e7465723b0a202020207d0a202020202e6c6173742d636f6e74656e74207020617b0a2020202020202020636f6c6f723a20233636363636363b0a2020202020202020746578742d6465636f726174696f6e3a206e6f6e653b0a202020207d0a20202020406d6564696120286d61782d77696474683a20373637707829207b0a20202020202020202e6d61696e2d7461626c657b0a20202020202020202020202077696474683a20313030253b0a20202020202020207d0a20202020202020202e62616e6e65727b0a202020202020202020202020206865696768743a2032353070783b0a20202020202020207d0a20202020202020202e62616e6e657220696d677b0a202020202020202020202020206d61782d77696474683a20313030253b0a20202020202020207d0a20202020202020202e6d6964646c652d636f6e74656e742068317b0a202020202020202020202020666f6e742d73697a653a20323570783b0a20202020202020207d0a20202020202020202e626f6f6b696e672d62746e2d777261707065727b0a2020202020202020202020206d617267696e3a2032357078206175746f20323070783b0a20202020202020207d0a20202020202020202e626f6f6b696e672d62746e2d7772617070657220617b0a20202020202020202020202070616464696e673a203137707820333570780a20202020202020207d0a20202020202020202e6d6964646c652d636f6e74656e7420707b0a2020202020202020202020206d617267696e3a203335707820313570783b0a20202020202020207d0a20202020202020202e696e766974652d62746e7b0a20202020202020202020202070616464696e673a2037707820333570782021696d706f7274616e743b0a20202020202020207d0a0a202020207d0a202020200a20203c2f7374796c653e0a3c2f686561643e0a3c626f647920206267636f6c6f723d222345434542454222207374796c653d22666f6e742d66616d696c793a417269616c2c2073616e732d7365726966223e0a202020203c7461626c6520626f726465723d22222063656c6c70616464696e673d2230222063656c6c73706163696e673d22302220636c6173733d226d61696e2d7461626c65223e0a2020202020202020203c212d2d205374617274206865616465722073656374696f6e2d2d3e0a2020202020202020203c747220636c6173733d226c6f676f2d7472223e0a202020202020202020202020202020203c746420636c6173733d226c6f676f2d7464223e0a2020202020202020202020202020202020202020202020203c6120687265663d227b7b574542534954455f55524c7d7d223e3c696d67207372633d227b7b534954455f55524c7d7d63726f6e2f6173736574732f696d672f6c6f676f2e706e67223e3c2f613e0a202020202020202020202020202020203c2f74643e0a202020202020202020202020202020203c746420636c6173733d226c696e6b2d7464223e0a2020202020202020202020202020202020202020202020203c6120687265663d227b7b574542534954455f55524c7d7d223e7b7b574542534954455f444f4d41494e7d7d3c2f613e0a20202020202020200a202020202020202020202020202020203c2f74643e0a2020202020202020202020203c2f74723e0a202020202020202020202020202020203c212d2d456e64206865616465722073656374696f6e2d2d3e0a20202020202020200a20202020202020203c212d2d53746172742042616e6e65722073656374696f6e2d2d3e0a20202020202020203c74723e0a2020202020202020202020203c746420636c6173733d2262616e6e65722220636f6c7370616e3d2232223e0a202020202020202020202020202020203c696d67207372633d227b7b534954455f55524c7d7d61646d696e2f7372632f6173736574732f696d672f636173682d62616e6e65722e706e672220616c743d2262616e6e65722d696d67223e0a2020202020202020202020203c2f74643e0a20202020202020203c2f74723e0a20202020202020203c212d2d456e642042616e6e65722073656374696f6e2d2d3e0a20202020202020203c212d2d5374617274206d6964646c652073656374696f6e2d2d3e0a20202020202020203c74723e0a2020202020202020202020203c746420636f6c7370616e3d2232223e0a202020202020202020202020202020203c64697620636c6173733d226d6964646c652d636f6e74656e74223e0a20202020202020202020202020202020202020203c68313e0a202020202020202020202020202020202020202020202020596f752077696c6c20676574207374756d7065640a20202020202020202020202020202020202020203c2f68313e0a20202020202020202020202020202020202020203c64697620636c6173733d22626f72646572223e3c2f6469763e0a20202020202020202020202020202020202020203c703e4a6f696e206f757220636f6e74657374203c7370616e3e7b7b636f6e746573745f6e616d657d7d3c2f7370616e3e206e6f7720616e642074616b6520612073686f742061743c62722f3e200a20202020202020202020202020202020202020202020202077696e6e696e6720626967206d6f6e65792e3c2f703e0a202020202020202020202020202020203c2f6469763e0a2020202020202020202020202020200a2020202020202020202020203c2f74643e0a20202020202020203c2f74723e0a20202020202020203c74723e0a2020202020202020202020203c746420636f6c7370616e3d2232223e0a202020202020202020202020202020203c64697620636c6173733d22626f6f6b696e672d62746e2d77726170706572223e0a2020202020202020202020202020202020202020203c6120687265663d227b7b434f4e544553545f55524c7d7d2220636c6173733d22626f6f6b696e672d62746e223e506c6179204e6f773c2f613e0a202020202020202020202020202020203c2f6469763e0a2020202020202020202020203c2f74643e0a20202020202020203c2f74723e0a20202020202020203c74723e0a2020202020202020202020203c746420636f6c7370616e3d2232223e0a202020202020202020202020202020203c64697620636c6173733d226c6173742d636f6e74656e74223e0a2020202020202020202020202020202020203c703e0a202020202020202020202020202020202020202020202020546f20706572736f6e616c697a6520796f757220656d61696c206e6f74696669636174696f6e2073657474696e67732066726f6d207b7b534954455f5449544c457d7d2c203c6120687265663d2223223e436c69636b20686572652e3c2f613e20200a2020202020202020202020202020202020203c2f703e0a2020202020202020202020202020202020203c703e0a202020202020202020202020202020202020202020202020436f707972696768742026233136393b207b7b796561727d7d207b7b534954455f5449544c457d7d2c20416c6c207269676874732072657365727665642e0a2020202020202020202020202020202020203c2f703e0a202020202020202020202020202020203c2f6469763e0a2020202020202020202020203c2f74643e0a20202020202020203c2f74723e0a20202020202020203c212d2d456e64206d6964646c652073656374696f6e2d2d3e0a20202020202020203c212d2d537461727420666f6f7465722073656374696f6e2d2d3e0a202020203c747220636c6173733d22666f6f7465722d7472223e0a2020202020202020202020203c746420636f6c7370616e3d22332220636c6173733d22746578742d63656e746572223e0a202020202020202020202020202020203c64697620636c6173733d22626f6f746f6d223e0a20202020202020202020202020202020202020203c7020636c6173733d22636f707972696768742d70223e496e7669746520796f757220667269656e64732026206561726e206173207468657920706c6179213c2f703e0a20202020202020202020202020202020202020203c64697620636c6173733d22626f6f6b696e672d62746e2d77726170706572206d2d30223e0a2020202020202020202020202020202020202020202020203c6120687265663d22232220636c6173733d22696e766974652d62746e223e496e76697465204e6f773c2f613e200a20202020202020202020202020202020202020203c2f6469763e0a20202020202020202020202020202020202020203c7020636c6173733d22636f707972696768742d70223e446f776e6c6f6164207468652061707020203c696d67207372633d227b7b534954455f55524c7d7d63726f6e2f6173736574732f696d672f616e64726f69642d62746e2e706e672220636c6173733d22616e64726f69642d62746e223e3c2f703e0a20202020202020202020202020202020202020203c756c20636c6173733d22626f6f746f6d2d6e6176223e0a202020202020202020202020202020202020202020202020202020203c6c693e0a20202020202020202020202020202020202020202020202020202020202020203c6120687265663d227b7b534954455f55524c7d7d636f6e746163742d7573223e436f6e746163742055733c2f613e3c2f6c693e0a202020202020202020202020202020202020202020202020202020203c6c693e7c3c2f6c693e0a202020202020202020202020202020202020202020202020202020203c6c693e0a20202020202020202020202020202020202020202020202020202020202020203c6120687265663d227b7b534954455f55524c7d7d666171223e464151733c2f613e0a202020202020202020202020202020202020202020202020202020203c2f6c693e0a202020202020202020202020202020202020202020202020202020203c6c693e7c3c2f6c693e0a202020202020202020202020202020202020202020202020202020203c6c693e0a20202020202020202020202020202020202020202020202020202020202020203c6120687265663d227b7b534954455f55524c7d7d72756c65732d616e642d73636f72696e67223e52756c657320262053636f72696e673c2f613e0a202020202020202020202020202020202020202020202020202020203c2f6c693e0a202020202020202020202020202020202020202020202020202020203c212d2d203c6c693e7c3c2f6c693e0a202020202020202020202020202020202020202020202020202020203c6c693e0a20202020202020202020202020202020202020202020202020202020202020203c6120687265663d2223223e456d61696c20507265666572656e6365733c2f613e0a202020202020202020202020202020202020202020202020202020203c2f6c693e202d2d3e0a2020202020202020202020202020202020202020202020203c2f756c3e0a20202020202020202020202020202020202020203c756c20636c6173733d22626f6f746f6d2d6e6176223e0a202020202020202020202020202020202020202020202020202020203c6c693e0a20202020202020202020202020202020202020202020202020202020202020203c6120687265663d227b7b46425f4c494e4b7d7d223e3c696d67207372633d227b7b534954455f55524c7d7d63726f6e2f6173736574732f696d672f66622e706e67223e3c2f613e200a202020202020202020202020202020202020202020202020202020203c2f6c693e0a202020202020202020202020202020202020202020202020202020203c6c693e0a20202020202020202020202020202020202020202020202020202020202020203c6120687265663d227b7b545749545445525f4c494e4b7d7d223e3c696d67207372633d227b7b534954455f55524c7d7d63726f6e2f6173736574732f696d672f747769747465722e706e67223e3c2f613e0a202020202020202020202020202020202020202020202020202020203c2f6c693e0a202020202020202020202020202020202020202020202020202020203c6c693e0a20202020202020202020202020202020202020202020202020202020202020203c6120687265663d227b7b494e5354414752414d5f4c494e4b7d7d223e3c696d67207372633d227b7b534954455f55524c7d7d63726f6e2f6173736574732f696d672f696e7374616772616d2e706e67223e3c2f613e0a202020202020202020202020202020202020202020202020202020203c2f6c693e0a2020202020202020202020202020202020202020202020203c2f756c3e0a20202020202020202020202020202020202020203c7020636c6173733d22636f707972696768742d70223e26233136393b207b7b796561727d7d203c7370616e3e7b7b534954455f5449544c457d7d2e3c2f7370616e3e20416c6c20526967687473205265736572766564203c2f703e0a202020202020202020202020202020203c2f6469763e0a2020202020202020202020203c2f74643e0a202020202020202020202020202020203c2f74723e0a202020202020202020202020202020200a202020202020202020202020202020203c212d2d456e6420666f6f7465722073656374696f6e2d2d3e0a202020203c2f7461626c653e0a3c2f626f64793e0a3c2f68746d6c3e, 'Fun Competition, Easy Winnings. Join {{contest_name}} contest for the {{collection_name}} match. Play Now,  ', '{{FRONTEND_BITLY_URL}}', '', 0, 'Promotion for Contest', NULL, NULL);";
        $this->db->query($sql);

    }
    
    public function down(){
        $sql = "UPDATE ".$this->db->dbprefix(NOTIFICATION_DESCRIPTION)." SET `message` = 'You have recieved {{amount}} coins for daily checkin Day {{day_number}}',en_message='You have recieved {{amount}} coins for Daily Check-In Day {{day_number}}' WHERE `notification_type` = 138;";
        $this->db->query($sql);

        $sql = "UPDATE ".$this->db->dbprefix(CD_EMAIL_TEMPLATE)." SET 
		template_name='promotion-for-fixture'
		WHERE cd_email_template_id = 4";
        $this->db->query($sql);
        
        //updateding collection name in game join notification
        $sql = "UPDATE ".$this->db->dbprefix(NOTIFICATION_DESCRIPTION)." SET 
        `message` = 'Game {{contest_name}} joined successfully',
        en_message='Game {{contest_name}} joined successfully',
        hi_message='आपने खेल​ {{contest_name}} में सफलतापूर्वक प्रवेश लिया है।',
        guj_message='તમે સફળતાપૂર્વક રમત દાખલ કરેલ {{contest_name}}.',
        ben_message='খেলা {{contest_name}} সফলভাবে যোগদান',
        fr_message='Jeu {{contest_name}} avec succès',
        pun_message='ਗੇਮ {{contest_name}} ਸਫਲਤਾਪੂਰਵਕ ਸ਼ਾਮਲ ਹੋਵੋ'
        WHERE `notification_type` = 1;";
        $this->db->query($sql);
    }
}