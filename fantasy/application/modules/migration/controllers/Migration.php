<?php


class Migration extends CI_Controller
{

  public function __construct()
  {
    // Create the controller.
    parent::__construct();

    //load db object
    $this->load->dbforge();

    // Load the migration config file.
    $this->config->load('migration', true );
    
    //echo "<pre>";print_r($_SERVER);die;
    // Get the migration realm, user and password.
    $user     = $this->config->item('migration_user',   'migration');
    $password = $this->config->item('migration_password', 'migration');
    $realm    = $this->config->item('migration_realm',  'migration');
    
    // If we've specified a realm, require basic authentication.
    if( $realm !== false )
    {
      // Check if the user matches what's in our config file.
          if ( !isset($_SERVER['PHP_AUTH_USER']) || ( $_SERVER['PHP_AUTH_USER'] !== $user || $_SERVER['PHP_AUTH_PW'] !== $password ) )
          {
             $this->output->set_header('WWW-Authenticate: Basic realm="'.$realm.'"');
             $this->output->set_header('HTTP/1.0 401 Unauthorized');
             $this->output->set_output( "Please enter a valid username and password" );
             $this->output->_display();
             exit;
          }
    }
    
  }

  public function index()
  {
    echo 'Hello'; die;
  }

  public function do_migration($version = NULL)
  {
      $this->load->library('migration');
      //echo $this->migration->version($version);die;
      if(isset($version) && ($this->migration->version($version) === FALSE))
      {
        show_error($this->migration->error_string());
      }

      elseif(is_null($version) && $this->migration->latest() === FALSE)
      {
        show_error($this->migration->error_string());
      }
      else
      {
        echo 'The migration has concluded successfully.';
      }
      exit();
  }
  
  public function undo_migration($version = NULL)
  {
      $this->load->library('migration');
      $migrations = $this->migration->find_migrations();
      //echo "<pre>";print_r($version);die;
      $migration_keys = array();
      foreach($migrations as $key => $migration)
      {
        $migration_keys[] = $key;
      }
      if(isset($version) && array_key_exists($version,$migrations) && $this->migration->version($version))
      {
        echo 'The migration was reset to the version: '.$version;
        exit;
      }
      elseif(isset($version) && !array_key_exists($version,$migrations))
      {
        echo 'The migration with version number '.$version.' doesn\'t exist.';
      }
      else
      {
        $penultimate = (sizeof($migration_keys)==1) ? 0 : $migration_keys[sizeof($migration_keys) - 2];
        if($this->migration->version($penultimate))
        {
          echo 'The migration has been rolled back successfully.';
          exit;
        }
        else
        {
          echo 'Couldn\'t roll back the migration.';
          exit;
        }
      }
       exit();
  }

  public function reset_migration()
  {
      $this->load->library('migration');
      if($this->migration->current()!== FALSE)
      {
        echo 'The migration was reset to the version set in the config file.';
        //return TRUE;
         exit();
      }
      else
      {
        echo 'Couldn\'t reset migration.';
        show_error($this->migration->error_string());
        exit();
      }
       exit();
  }

}

