<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * CodeIgniter MongoDB Active Record Library
 *
 * A library to interface with the NoSQL database MongoDB. For more information see http://www.mongodb.org
 *
 * @package CodeIgniter
 * @author Intekhab Rizvi | www.intekhab.in | me@intekhab.in
 * @copyright Copyright (c) 2014, Intekhab Rizvi.
 * @license http://www.opensource.org/licenses/mit-license.php
 * @link http://intekhab.in
 * @version Version 1.0
 * Thanks to Alex Bilbie (http://alexbilbie.com) for help.
 */
Class Mongo_db {

    private $CI;
    private $config = array();
    private $param = array();
    private $activate;
    private $connect;
    private $db;
    private $hostname;
    private $port;
    private $database;
    private $username;
    private $password;
    private $debug;
    private $write_concerns;
    private $journal;
    private $selects = array();
    private $updates = array();
    private $wheres = array();
    private $limit = 999999;
    private $offset = 0;
    private $sorts = array();
    private $return_as = 'array';
    public $benchmark = array();
    protected $enable = IS_MONGO_ENABLE;

    /**
     * --------------------------------------------------------------------------------
     * Class Constructor
     * --------------------------------------------------------------------------------
     *
     * Automatically check if the Mongo PECL extension has been installed/enabled.
     * Get Access to all CodeIgniter available resources.
     * Load mongodb config file from application/config folder.
     * Prepare the connection variables and establish a connection to the MongoDB.
     * Try to connect on MongoDB server.
     */
    function __construct($param) {
        if (!$this->enable)
            return FALSE;
        if (!class_exists('Mongo') && !class_exists('MongoDB\Driver\Manager')) {
            log_message("error", "The MongoDB PECL extension has not been installed or enabled");
        }
        $this->CI = & get_instance();
        $this->CI->load->config('mongo_db');
        $this->config = $this->CI->config->item('mongo_db');
        
        $this->param = $param;
        $this->connect();
    }

    /**
     * --------------------------------------------------------------------------------
     * Class Destructor
     * --------------------------------------------------------------------------------
     *
     * Close all open connections.
     */
    function __destruct() {
        /* if (is_object($this->connect)) {
          $this->connect->close();
          } */
    }

    /**
     * --------------------------------------------------------------------------------
     * Prepare configuration for mongoDB connection
     * --------------------------------------------------------------------------------
     * 
     * Validate group name or autoload default group name from config file.
     * Validate all the properties present in config file of the group.
     */
    private function prepare() {
        if (!$this->enable)
            return FALSE;
        if (is_array($this->param) && count($this->param) > 0 && isset($this->param['activate']) == TRUE) {
            $this->activate = $this->param['activate'];
        } else if (isset($this->config['active']) && !empty($this->config['active'])) {
            $this->activate = $this->config['active'];
        } else {
            log_message("error", "MongoDB configuration is missing.");
        }

        if (isset($this->config[$this->activate]) == TRUE) {
            if (empty($this->config[$this->activate]['hostname'])) {
                log_message("error", "Hostname missing from mongodb config group : {$this->activate}");
            } else {
                $this->hostname = trim($this->config[$this->activate]['hostname']);
            }

            if (empty($this->config[$this->activate]['port'])) {
                log_message("error", "Port number missing from mongodb config group : {$this->activate}");
            } else {
                $this->port = trim($this->config[$this->activate]['port']);
            }

            if (isset($this->config[$this->activate]['no_auth']) == FALSE && empty($this->config[$this->activate]['username'])) {
                log_message("error", "Username missing from mongodb config group : {$this->activate}");
            } else {
                $this->username = trim($this->config[$this->activate]['username']);
            }

            if (isset($this->config[$this->activate]['no_auth']) == FALSE && empty($this->config[$this->activate]['password'])) {
                log_message('error', "Password missing from mongodb config group : {$this->activate}");
            } else {
                $this->password = trim($this->config[$this->activate]['password']);
            }

            if (empty($this->config[$this->activate]['database'])) {
                log_message("error", "Database name missing from mongodb config group : {$this->activate}");
            } else {
                $this->database = trim($this->config[$this->activate]['database']);
            }

            if (empty($this->config[$this->activate]['db_debug'])) {
                $this->debug = FALSE;
            } else {
                $this->debug = $this->config[$this->activate]['db_debug'];
            }

            if (empty($this->config[$this->activate]['write_concerns'])) {
                $this->write_concerns = 1;
            } else {
                $this->write_concerns = $this->config[$this->activate]['write_concerns'];
            }

            if (empty($this->config[$this->activate]['journal'])) {
                $this->journal = TRUE;
            } else {
                $this->journal = $this->config[$this->activate]['journal'];
            }

            if (empty($this->config[$this->activate]['return_as'])) {
                $this->return_as = 'array';
            } else {
                $this->return_as = $this->config[$this->activate]['return_as'];
            }
        } else {
            log_message("error", "mongodb config group :  <strong>{$this->activate}</strong> does not exist.");
        }
    }

    /**
     * --------------------------------------------------------------------------------
     * Connect to MongoDB Database
     * --------------------------------------------------------------------------------
     * 
     * Connect to mongoDB database or throw exception with the error message.
     */
    private function connect() {
        if (!$this->enable)
            return FALSE;
        $this->prepare();
        try {
            //$dns = "mongodb://{$this->username}:{$this->password}@{$this->hostname}:{$this->port}";
            $dns = "mongodb://{$this->hostname}:{$this->port}/{$this->database}";
            if (isset($this->config[$this->activate]['no_auth']) && $this->config[$this->activate]['no_auth']) {
                $options = array();
            } else {
                $dns = "mongodb://{$this->username}:{$this->password}@{$this->hostname}:{$this->port}";
                $options = array('username' => $this->username, 'password' => $this->password);
            }
            $this->connect = new MongoDB\Client($dns, $options);
            $this->db = $this->connect->{$this->database};
        } catch (MongoConnectionException $e) {
            if (isset($this->debug) == TRUE && $this->debug == TRUE) {
                log_message("error", "Unable to connect to MongoDB: {$e->getMessage()}");
            } else {
                log_message("error", "Unable to connect to MongoDB");
            }
        }
    }

    /**
     * --------------------------------------------------------------------------------
     * //! Insert
     * --------------------------------------------------------------------------------
     *
     * Insert a new document into the passed collection
     *
     * @usage : $this->mongo_db->insert('foo', $data = array());
     */
    public function insert($collection, $insert = array()) {
        if (!$this->enable)
        return FALSE;
        try {
            $this->db->{$collection}->insertOne($insert, array('w' => $this->write_concerns, 'j' => $this->journal));
            if (isset($insert['_id'])) {
                return ($insert['_id']);
            } else {
                return (FALSE);
            }
        } catch (MongoCursorException $e) {
            return (FALSE);
        }catch(MongoDB\Driver\Exception\BulkWriteException $e){
            return (FALSE);
        }
    }    

    /**
     * --------------------------------------------------------------------------------
     * Batch Insert
     * --------------------------------------------------------------------------------
     *
     * Insert a multiple document into the collection
     *
     * @usage : $this->mongo_db->batch_insert('foo', $data = array());
     * @return : bool or array : if query fail then false else array of _id successfully inserted.
     */
    public function batch_insert($collection = "", $insert = array()) {
        if (!$this->enable)
            return FALSE;
        if (empty($collection)) {
            log_message("error", "No Mongo collection selected to insert into");
        }
        if (count($insert) == 0 || !is_array($insert)) {
            log_message("error", "Nothing to insert into Mongo collection or insert is not an array");
        }
        try {
            $insertManyResult = $this->db->{$collection}->insertMany($insert, array('w' => $this->write_concerns, 'j' => $this->journal));
            if (is_array($insertManyResult) && count($insertManyResult) > 0) {
                return $insertManyResult->getInsertedIds();
            } else {
                return (FALSE);
            }
        } catch (MongoCursorException $e) {
            if (isset($this->debug) == TRUE && $this->debug == TRUE) {
                log_message("error", "Batch insert of data into MongoDB failed: {$e->getMessage()}");
            } else {
                log_message("error", "Batch insert of data into MongoDB failed");
            }
        }
    }

    /**
     * --------------------------------------------------------------------------------
     * //! Select
     * --------------------------------------------------------------------------------
     *
     * Determine which fields to include OR which to exclude during the query process.
     * If you want to only choose fields to exclude, leave $includes an empty array().
     *
     * @usage: $this->mongo_db->select(array('foo', 'bar'))->get('foobar');
     */
    public function select($includes = array(), $excludes = array()) {
        if (!$this->enable)
            return FALSE;
        if (!is_array($includes)) {
            $includes = array();
        }
        if (!is_array($excludes)) {
            $excludes = array();
        }
        if (!empty($includes)) {
            foreach ($includes as $key => $col) {
                if (is_array($col)) {
                    //support $elemMatch in select
                    $this->selects[$key] = $col;
                } else {
                    $this->selects[$col] = 1;
                }
            }
        }
        if (!empty($excludes)) {
            foreach ($excludes as $col) {
                $this->selects[$col] = 0;
            }
        }
        return ($this);
    }

    /**
     * --------------------------------------------------------------------------------
     * //! Where
     * --------------------------------------------------------------------------------
     *
     * Get the documents based on these search parameters. The $wheres array should
     * be an associative array with the field as the key and the value as the search
     * criteria.
     *
     * @usage : $this->mongo_db->where(array('foo' => 'bar'))->get('foobar');
     */
    public function where($wheres, $value = null) {
        if (is_array($wheres)) {
            foreach ($wheres as $wh => $val) {
                $this->wheres[$wh] = $val;
            }
        } else {
            $this->wheres[$wheres] = $value;
        }
        return $this;
    }

    /**
     * --------------------------------------------------------------------------------
     * or where
     * --------------------------------------------------------------------------------
     *
     * Get the documents where the value of a $field may be something else
     *
     * @usage : $this->mongo_db->where_or(array('foo'=>'bar', 'bar'=>'foo'))->get('foobar');
     */
    public function where_or($wheres = array()) {
        if (is_array($wheres) && count($wheres) > 0) {
            if (!isset($this->wheres['$or']) || !is_array($this->wheres['$or'])) {
                $this->wheres['$or'] = array();
            }
            foreach ($wheres as $wh => $val) {
                $this->wheres['$or'][] = array($wh => $val);
            }
            return ($this);
        } else {
            log_message("error", "Where value should be an array.");
        }
    }

    /**
     * --------------------------------------------------------------------------------
     * Where in
     * --------------------------------------------------------------------------------
     *
     * Get the documents where the value of a $field is in a given $in array().
     *
     * @usage : $this->mongo_db->where_in('foo', array('bar', 'zoo', 'blah'))->get('foobar');
     */
    public function where_in($field = "", $in = array()) {
        if (empty($field)) {
            log_message("error", "Mongo field is require to perform where in query.");
        }

        if (is_array($in) && count($in) > 0) {
            $this->_w($field);
            $this->wheres[$field]['$in'] = $in;
            return ($this);
        } else {
            log_message("error", "in value should be an array.");
        }
    }

    /**
     * --------------------------------------------------------------------------------
     * Where in all
     * --------------------------------------------------------------------------------
     *
     * Get the documents where the value of a $field is in all of a given $in array().
     *
     * @usage : $this->mongo_db->where_in_all('foo', array('bar', 'zoo', 'blah'))->get('foobar');
     */
    public function where_in_all($field, $in = array()) {
        if (is_array($in) && count($in) > 0) {
            $this->_w($field);
            $this->wheres[$field]['$all'] = $in;
            return ($this);
        }
    }

    /**
     * --------------------------------------------------------------------------------
     * Where not in
     * --------------------------------------------------------------------------------
     *
     * Get the documents where the value of a $field is not in a given $in array().
     *
     * @usage : $this->mongo_db->where_not_in('foo', array('bar', 'zoo', 'blah'))->get('foobar');
     */
    public function where_not_in($field, $in = array()) {
        if (is_array($in) && count($in) > 0) {
            $this->_w($field);
            $this->wheres[$field]['$nin'] = $in;
            return ($this);
        }
    }

    /**
     * --------------------------------------------------------------------------------
     * Where greater than
     * --------------------------------------------------------------------------------
     *
     * Get the documents where the value of a $field is greater than $x
     *
     * @usage : $this->mongo_db->where_gt('foo', 20);
     */
    public function where_gt($field, $x) {
        $this->_w($field);
        $this->wheres[$field]['$gt'] = $x;
        return ($this);
    }

    /**
     * --------------------------------------------------------------------------------
     * Where greater than or equal to
     * --------------------------------------------------------------------------------
     *
     * Get the documents where the value of a $field is greater than or equal to $x
     *
     * @usage : $this->mongo_db->where_gte('foo', 20);
     */
    public function where_gte($field, $x) {
        $this->_w($field);
        $this->wheres[$field]['$gte'] = $x;
        return($this);
    }

    /**
     * --------------------------------------------------------------------------------
     * Where less than
     * --------------------------------------------------------------------------------
     *
     * Get the documents where the value of a $field is less than $x
     *
     * @usage : $this->mongo_db->where_lt('foo', 20);
     */
    public function where_lt($field, $x) {
        $this->_w($field);
        $this->wheres[$field]['$lt'] = $x;
        return($this);
    }

    /**
     * --------------------------------------------------------------------------------
     * Where less than or equal to
     * --------------------------------------------------------------------------------
     *
     * Get the documents where the value of a $field is less than or equal to $x
     *
     * @usage : $this->mongo_db->where_lte('foo', 20);
     */
    public function where_lte($field, $x) {
        $this->_w($field);
        $this->wheres[$field]['$lte'] = $x;
        return ($this);
    }

    /**
     * --------------------------------------------------------------------------------
     * Where between
     * --------------------------------------------------------------------------------
     *
     * Get the documents where the value of a $field is between $x and $y
     *
     * @usage : $this->mongo_db->where_between('foo', 20, 30);
     */
    public function where_between($field, $x, $y) {
        $this->_w($field);
        $this->wheres[$field]['$gte'] = $x;
        $this->wheres[$field]['$lte'] = $y;
        return ($this);
    }

    /**
     * --------------------------------------------------------------------------------
     * Where between and but not equal to
     * --------------------------------------------------------------------------------
     *
     * Get the documents where the value of a $field is between but not equal to $x and $y
     *
     * @usage : $this->mongo_db->where_between_ne('foo', 20, 30);
     */
    public function where_between_ne($field, $x, $y) {
        $this->_w($field);
        $this->wheres[$field]['$gt'] = $x;
        $this->wheres[$field]['$lt'] = $y;
        return ($this);
    }

    /**
     * --------------------------------------------------------------------------------
     * Where not equal
     * --------------------------------------------------------------------------------
     *
     * Get the documents where the value of a $field is not equal to $x
     *
     * @usage : $this->mongo_db->where_ne('foo', 1)->get('foobar');
     */
    public function where_ne($field, $x) {
        $this->_w($field);
        $this->wheres[$field]['$ne'] = $x;
        return ($this);
    }

    /**
     * --------------------------------------------------------------------------------
     * // Get
     * --------------------------------------------------------------------------------
     *
     * Get the documents based upon the passed parameters
     *
     * @usage : $this->mongo_db->get('foo');
     */
    public function get($collection) {
        if (!$this->enable)
            return FALSE;
        try {
            $documents = $this->db->{$collection}
                    ->find($this->wheres, $this->selects, ['limit' => (int) $this->limit, 'skip' => (int) $this->offset, 'sort' => $this->sorts]);
            $this->_clear();
            $returns = array();
            if ($this->return_as == 'object') {
                return (object) $documents;
            } else {
                return json_decode(json_encode($documents->toArray()), true);
            }
        } catch (MongoCursorException $e) {
            return array();
        }
    }

//    public function find_one($collection,$where) {
//        $find_res = $this->db->{$collection}->findOne( $where );
//    }

    /**
     * --------------------------------------------------------------------------------
     * // Get where
     * --------------------------------------------------------------------------------
     *
     * Get the documents based upon the passed parameters
     *
     * @usage : $this->mongo_db->get_where('foo', array('bar' => 'something'));
     */
    public function get_where($collection, $where = array()) {
        if (!$this->enable)
            return FALSE;
        if (is_array($where) && count($where) > 0) {
            return $this->where($where)
                            ->get($collection);
        }
    }

    /**
     * --------------------------------------------------------------------------------
     * // Find One
     * --------------------------------------------------------------------------------
     *
     * Get the single document based upon the passed parameters
     *
     * @usage : $this->mongo_db->find_one('foo');
     */
    public function find_one($collection) {
        if (!$this->enable)
            return FALSE;
        try {

            $document = $this->db->{$collection}->findOne($this->wheres, $this->selects);
            // Clear
            $this->_clear();
            if (is_null($document)) {
                return false;
            } else {
                if ($this->return_as == 'object') {
                    return (object) $document;
                } else {
                    return $document;
                }
            }
        } catch (MongoCursorException $e) {
            if (isset($this->debug) == TRUE && $this->debug == TRUE) {
                log_message("error", "MongoDB query failed: {$e->getMessage()}");
            } else {
                log_message("error", "MongoDB query failed.");
            }
        }
    }

    /**
     * --------------------------------------------------------------------------------
     * Count
     * --------------------------------------------------------------------------------
     *
     * Count the documents based upon the passed parameters
     *
     * @usage : $this->mongo_db->count('foo');
     */
    public function count($collection) {
        if (!$this->enable)
            return FALSE;
        $count = $this->db->{$collection}->find($this->wheres)->limit((int) $this->limit)->skip((int) $this->offset)->count();
        $this->_clear();
        return ($count);
    }

    /**
     * --------------------------------------------------------------------------------
     * Set
     * --------------------------------------------------------------------------------
     *
     * Sets a field to a value
     *
     * @usage: $this->mongo_db->where(array('blog_id'=>123))->set('posted', 1)->update('blog_posts');
     * @usage: $this->mongo_db->where(array('blog_id'=>123))->set(array('posted' => 1, 'time' => time()))->update('blog_posts');
     */
    public function set($fields, $value = NULL) {
        if (!$this->enable)
            return FALSE;
        $this->_u('$set');
        if (is_string($fields)) {
            $this->updates['$set'][$fields] = $value;
        } elseif (is_array($fields)) {
            foreach ($fields as $field => $value) {
                $this->updates['$set'][$field] = $value;
            }
        }
        return $this;
    }

    /**
     * --------------------------------------------------------------------------------
     * Unset
     * --------------------------------------------------------------------------------
     *
     * Unsets a field (or fields)
     *
     * @usage: $this->mongo_db->where(array('blog_id'=>123))->unset('posted')->update('blog_posts');
     * @usage: $this->mongo_db->where(array('blog_id'=>123))->set(array('posted','time'))->update('blog_posts');
     */
    public function unset_field($fields) {
        if (!$this->enable)
            return FALSE;
        $this->_u('$unset');
        if (is_string($fields)) {
            $this->updates['$unset'][$fields] = 1;
        } elseif (is_array($fields)) {
            foreach ($fields as $field) {
                $this->updates['$unset'][$field] = 1;
            }
        }
        return $this;
    }

    /**
     * --------------------------------------------------------------------------------
     * Push
     * --------------------------------------------------------------------------------
     *
     * Pushes values into a field (field must be an array)
     *
     * @usage: $this->mongo_db->where(array('blog_id'=>123))->push('comments', array('text'=>'Hello world'))->update('blog_posts');
     * @usage: $this->mongo_db->where(array('blog_id'=>123))->push(array('comments' => array('text'=>'Hello world')), 'viewed_by' => array('Alex')->update('blog_posts');
     */
    public function push($fields, $value = array()) {
        if (!$this->enable)
            return FALSE;
        $this->_u('$push');
        if (is_string($fields)) {
            $this->updates['$push'][$fields] = $value;
        } elseif (is_array($fields)) {
            foreach ($fields as $field => $value) {
                $this->updates['$push'][$field] = $value;
            }
        }
        return $this;
    }

    /**
     * --------------------------------------------------------------------------------
     * Pop
     * --------------------------------------------------------------------------------
     *
     * Pops the last value from a field (field must be an array)
     *
     * @usage: $this->mongo_db->where(array('blog_id'=>123))->pop('comments')->update('blog_posts');
     * @usage: $this->mongo_db->where(array('blog_id'=>123))->pop(array('comments', 'viewed_by'))->update('blog_posts');
     */
    public function pop($field) {
        if (!$this->enable)
            return FALSE;
        $this->_u('$pop');
        if (is_string($field)) {
            $this->updates['$pop'][$field] = -1;
        } elseif (is_array($field)) {
            foreach ($field as $pop_field) {
                $this->updates['$pop'][$pop_field] = -1;
            }
        }
        return $this;
    }

    /**
     * --------------------------------------------------------------------------------
     * Pull
     * --------------------------------------------------------------------------------
     *
     * Removes by an array by the value of a field
     *
     * @usage: $this->mongo_db->pull('comments', array('comment_id'=>123))->update('blog_posts');
     */
    public function pull($field = "", $value = array()) {
        if (!$this->enable)
            return FALSE;
        $this->_u('$pull');
        $this->updates['$pull'] = array($field => $value);
        return $this;
    }

    /**
     * --------------------------------------------------------------------------------
     * Inc
     * --------------------------------------------------------------------------------
     *
     * Increments the value of a field
     *
     * @usage: $this->mongo_db->where(array('blog_id'=>123))->inc(array('num_comments' => 1))->update('blog_posts');
     */
    public function inc($fields = array(), $value = 0) {
        if (!$this->enable)
            return FALSE;
        $this->_u('$inc');
        if (is_string($fields)) {
            $this->updates['$inc'][$fields] = $value;
        } elseif (is_array($fields)) {
            foreach ($fields as $field => $value) {
                $this->updates['$inc'][$field] = $value;
            }
        }
        return $this;
    }

    /**
     * --------------------------------------------------------------------------------
     * Multiple
     * --------------------------------------------------------------------------------
     *
     * Multiple the value of a field
     *
     * @usage: $this->mongo_db->where(array('blog_id'=>123))->mul(array('num_comments' => 3))->update('blog_posts');
     */
    public function mul($fields = array(), $value = 0) {
        if (!$this->enable)
            return FALSE;
        $this->_u('$mul');
        if (is_string($fields)) {
            $this->updates['$mul'][$fields] = $value;
        } elseif (is_array($fields)) {
            foreach ($fields as $field => $value) {
                $this->updates['$mul'][$field] = $value;
            }
        }
        return $this;
    }

    /**
     * --------------------------------------------------------------------------------
     * Maximum
     * --------------------------------------------------------------------------------
     *
     * The $max operator updates the value of the field to a specified value if the specified value is greater than the current value of the field.
     *
     * @usage: $this->mongo_db->where(array('blog_id'=>123))->max(array('num_comments' => 3))->update('blog_posts');
     */
    public function max($fields = array(), $value = 0) {
        if (!$this->enable)
            return FALSE;
        $this->_u('$max');
        if (is_string($fields)) {
            $this->updates['$max'][$fields] = $value;
        } elseif (is_array($fields)) {
            foreach ($fields as $field => $value) {
                $this->updates['$max'][$field] = $value;
            }
        }
        return $this;
    }

    /**
     * --------------------------------------------------------------------------------
     * Minimum
     * --------------------------------------------------------------------------------
     *
     * The $min updates the value of the field to a specified value if the specified value is less than the current value of the field.
     *
     * @usage: $this->mongo_db->where(array('blog_id'=>123))->min(array('num_comments' => 3))->update('blog_posts');
     */
    public function min($fields = array(), $value = 0) {
        if (!$this->enable)
            return FALSE;
        $this->_u('$min');
        if (is_string($fields)) {
            $this->updates['$min'][$fields] = $value;
        } elseif (is_array($fields)) {
            foreach ($fields as $field => $value) {
                $this->updates['$min'][$field] = $value;
            }
        }
        return $this;
    }

    /**
     * --------------------------------------------------------------------------------
     * //! distinct
     * --------------------------------------------------------------------------------
     *
     * Finds the distinct values for a specified field across a single collection
     *
     * @usage: $this->mongo_db->distinct('collection', 'field');
     */
    public function distinct($collection, $field) {
        if (!$this->enable)
            return FALSE;
        try {
            $documents = $this->db->{$collection}->distinct($field, $this->wheres);
            $this->_clear();
            if ($this->return_as == 'object') {
                return (object) $documents;
            } else {
                return $documents;
            }
        } catch (MongoCursorException $e) {
            if (isset($this->debug) == TRUE && $this->debug == TRUE) {
                log_message("error", "MongoDB Distinct Query Failed: {$e->getMessage()}");
            } else {
                log_message("error", "MongoDB failed");
            }
        }
    }

    /**
     * --------------------------------------------------------------------------------
     * //! Update
     * --------------------------------------------------------------------------------
     *
     * Updates a single document in Mongo
     *
     * @usage: $this->mongo_db->update('foo', $data = array());
     */
    public function update($collection, $options = array()) {
        if (!$this->enable)
            return FALSE;
        try {
            $options = array_merge(array('w' => $this->write_concerns, 'j' => $this->journal, 'multiple' => FALSE), $options);
            $this->db->{$collection}->updateOne($this->wheres, $this->updates, $options);
            $this->_clear();
            return (TRUE);
        } catch (MongoCursorException $e) {
            if (isset($this->debug) == TRUE && $this->debug == TRUE) {
                log_message("error", "Update of data into MongoDB failed: {$e->getMessage()}");
            } else {
                log_message("error", "Update of data into MongoDB failed");
            }
        }
    }

    /**
     * --------------------------------------------------------------------------------
     * Update all
     * --------------------------------------------------------------------------------
     *
     * Updates a collection of documents
     *
     * @usage: $this->mongo_db->update_all('foo', $data = array());
     */
    public function update_all($collection, $data = array(), $options = array()) {
        if (!$this->enable)
            return FALSE;
        if (is_array($data) && count($data) > 0) {
            $this->updates = array_merge($data, $this->updates);
        }
        if (count($this->updates) == 0) {
            log_message("error", "Nothing to update in Mongo collection or update is not an array");
        }
        try {
            $options = array_merge(array('w' => $this->write_concerns, 'j' => $this->journal, 'multiple' => TRUE), $options);
            $this->db->{$collection}->update($this->wheres, $this->updates, $options);
            $this->_clear();
            return (TRUE);
        } catch (MongoCursorException $e) {
            if (isset($this->debug) == TRUE && $this->debug == TRUE) {
                log_message("error", "Update of data into MongoDB failed: {$e->getMessage()}");
            } else {
                log_message("error", "Update of data into MongoDB failed");
            }
        }
    }

    /**
     * --------------------------------------------------------------------------------
     * //! Delete
     * --------------------------------------------------------------------------------
     *
     * delete document from the passed collection based upon certain criteria
     *
     * @usage : $this->mongo_db->delete('foo');
     */
    public function delete($collection) {
        if (!$this->enable)
            return FALSE;
        try {
            $this->db->{$collection}->deleteMany($this->wheres, array('w' => $this->write_concerns, 'j' => $this->journal, 'justOne' => TRUE));
            $this->_clear();
            return (TRUE);
        } catch (MongoCursorException $e) {
            if (isset($this->debug) == TRUE && $this->debug == TRUE) {
                log_message("error", "Delete of data into MongoDB failed: {$e->getMessage()}");
            } else {
                log_message("error", "Delete of data into MongoDB failed");
            }
        }
    }

    /**
     * --------------------------------------------------------------------------------
     * Delete all
     * --------------------------------------------------------------------------------
     *
     * Delete all documents from the passed collection based upon certain criteria
     *
     * @usage : $this->mongo_db->delete_all('foo', $data = array());
     */
    public function delete_all($collection = "") {
        if (!$this->enable)
            return FALSE;
        try {
            $this->db->{$collection}->remove($this->wheres, array('w' => $this->write_concerns, 'j' => $this->journal, 'justOne' => FALSE));
            $this->_clear();
            return (TRUE);
        } catch (MongoCursorException $e) {
            if (isset($this->debug) == TRUE && $this->debug == TRUE) {
                log_message("error", "Delete of data into MongoDB failed: {$e->getMessage()}");
            } else {
                log_message("error", "Delete of data into MongoDB failed");
            }
        }
    }

    /**
     * --------------------------------------------------------------------------------
     * Aggregation Operation
     * --------------------------------------------------------------------------------
     *
     * Perform aggregation on mongodb collection
     *
     * @usage : $this->mongo_db->aggregate('foo', $ops = array());
     */
    public function aggregate($collection, $operation) {
        if (!$this->enable)
            return FALSE;
        try {
            $documents = $this->db->{$collection}->aggregate($operation);
            $this->_clear();
            if ($this->return_as == 'object') {
                return (object) $documents;
            } else {
                return $documents;
            }
        } catch (MongoResultException $e) {
            if (isset($this->debug) == TRUE && $this->debug == TRUE) {
                log_message("error", "Aggregation operation failed: {$e->getMessage()}");
            } else {
                log_message("error", "Aggregation operation failed.");
            }
        }
    }

    /**
     * --------------------------------------------------------------------------------
     * // Order by
     * --------------------------------------------------------------------------------
     *
     * Sort the documents based on the parameters passed. To set values to descending order,
     * you must pass values of either -1, FALSE, 'desc', or 'DESC', else they will be
     * set to 1 (ASC).
     *
     * @usage : $this->mongo_db->order_by(array('foo' => 'ASC'))->get('foobar');
     */
    public function order_by($fields = array()) {
        if (!$this->enable)
            return FALSE;
        foreach ($fields as $col => $val) {
            if ($val == -1 || $val === FALSE || strtolower($val) == 'desc') {
                $this->sorts[$col] = -1;
            } else {
                $this->sorts[$col] = 1;
            }
        }
        return ($this);
    }

    /**
     * --------------------------------------------------------------------------------
     * Mongo Date
     * --------------------------------------------------------------------------------
     *
     * Create new MongoDate object from current time or pass timestamp to create
     * mongodate.
     *
     * @usage : $this->mongo_db->date($timestamp);
     */
    public function date($stamp = FALSE) {
        if (!$this->enable)
            return FALSE;
        if ($stamp == FALSE) {
            return new MongoDate();
        } else {
            return new MongoDate($stamp);
        }
    }

    /**
     * --------------------------------------------------------------------------------
     * // Limit results
     * --------------------------------------------------------------------------------
     *
     * Limit the result set to $x number of documents
     *
     * @usage : $this->mongo_db->limit($x);
     */
    public function limit($x = 99999) {
        if (!$this->enable)
            return FALSE;
        if ($x !== NULL && is_numeric($x) && $x >= 1) {
            $this->limit = (int) $x;
        }
        return ($this);
    }

    /**
     * --------------------------------------------------------------------------------
     * // Offset
     * --------------------------------------------------------------------------------
     *
     * Offset the result set to skip $x number of documents
     *
     * @usage : $this->mongo_db->offset($x);
     */
    public function offset($x = 0) {
        if (!$this->enable)
            return FALSE;
        if ($x !== NULL && is_numeric($x) && $x >= 1) {
            $this->offset = (int) $x;
        }
        return ($this);
    }

    /**
     * --------------------------------------------------------------------------------
     * //! Switch database
     * --------------------------------------------------------------------------------
     *
     * Switch from default database to a different db
     *
     * $this->mongo_db->switch_db('foobar');
     */
    public function switch_db($database = '') {
        if (!$this->enable)
            return FALSE;
        $this->database = $database;

        try {
            $this->db = $this->connect->{$this->database};
            return (TRUE);
        } catch (Exception $e) {
            log_message("error", "Unable to switch Mongo Databases: {$e->getMessage()}");
        }
    }

    /**
     * --------------------------------------------------------------------------------
     * _clear
     * --------------------------------------------------------------------------------
     *
     * Resets the class variables to default settings
     */
    private function _clear() {
        if (!$this->enable)
            return FALSE;
        $this->selects = array();
        $this->updates = array();
        $this->wheres = array();
        $this->limit = 999999;
        $this->offset = 0;
        $this->sorts = array();
    }

    /**
     * --------------------------------------------------------------------------------
     * Where initializer
     * --------------------------------------------------------------------------------
     *
     * Prepares parameters for insertion in $wheres array().
     */
    private function _w($param) {
        if (!isset($this->wheres[$param])) {
            $this->wheres[$param] = array();
        }
    }

    /**
     * --------------------------------------------------------------------------------
     * Update initializer
     * --------------------------------------------------------------------------------
     *
     * Prepares parameters for insertion in $updates array().
     */
    private function _u($method) {
        if (!isset($this->updates[$method])) {
            $this->updates[$method] = array();
        }
    }

}
