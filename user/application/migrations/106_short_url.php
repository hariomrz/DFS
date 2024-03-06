<?php
defined('BASEPATH') OR exit("No direct script access allowed");

class Migration_Short_url extends CI_Migration
{
public function up(){
    $fields = array(
            'id'                =>array(
                                        'type'=>'BIGINT',
                                        'constraint'=>'10',
                                        'unsigned'=>TRUE,
                                        'auto_increment'=>TRUE,
                                ),
            'short_id'          =>array(
                                        'type'=>'VARCHAR',
                                        'constraint'=>'30',
                                        'null'=>FALSE,
                                        'default'=>'',
                                ),
            'url'               =>array(
                                        'type'=>'VARCHAR',
                                        'constraint'=>'255',
                                        'null'=>FALSE,
                                        'default'=>'',
                                ),
            'url_type'          =>array(
                                        'type'=>'INT',
                                        'constraint'=>'10',
                                        'null'=>FALSE,
                                        'default'=>'0',
                                ),
            'added_date'        =>array(
                                        'type'=>'DATETIME',
                                        'null'=>FALSE,
                                        'default'=>format_date('today'),
                                ),
    );
    $attributes = array("ENGINE"=>'InnoDB');
    $this->dbforge->add_field($fields);
    $this->dbforge->add_key('id',true);
    $this->dbforge->create_table(SHORTENED_URLS,FALSE,$attributes);


}

public function down()
{
$this->dbforge->drop_table(SHORTENED_URLS);
}

}
?>