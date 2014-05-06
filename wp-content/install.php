<?php
/**
 * Designwall Quickstart install
 * Version: 2.0
 * Author: tronglp@designwall.com 
 */

/** Load WordPress Bootstrap */
require_once( dirname( dirname( __FILE__ ) ) . '/wp-load.php' );
/** WordPress Administration API */
require_once(ABSPATH . '/wp-admin/includes/admin.php');



if( !isset($_POST['data_select'] ) && !isset($_GET['step']) && !is_blog_installed() ){
     
     wallpress_install_hook();
     exit();
}
   
      

    //Override WP_INSTALL
      function wp_install( $blog_title, $user_name, $user_email, $public, $deprecated = '', $user_password = '' ) {

         global $wpdb,$table_prefix,$wp_rewrite;

            if ( !empty( $deprecated ) )
               _deprecated_argument( __FUNCTION__, '2.6' );

            wp_check_mysql_version();
            wp_cache_flush();
            

            $dbms_schema = ABSPATH.'/sql/sample_data.sql';

            $sql_query = @fread(@fopen($dbms_schema, 'r'), @filesize($dbms_schema)) or die('problem ');
            $sql_query = remove_remarks($sql_query,$table_prefix,wp_guess_url());

            preg_match('/siteurl\', \'(.+)\',/',$sql_query,$siteurl);

            $sql_query = split_sql_file($sql_query, ';');

            foreach($sql_query as $sql){
                 
              if ($wpdb->query($sql) === FALSE) {
                  wp_die( __('Crap! well that’s screwed up: ' . $wpdb->last_error) ); 
              }
            
            }

            $connection = @mysql_connect( DB_HOST, DB_USER, DB_PASSWORD );
            // Do we have any tables and if so build the all tables array
               $all_tables = array( );
               @mysql_select_db( DB_NAME, $connection );
               $all_tables_mysql = @mysql_query( 'SHOW TABLES LIKE "'.$table_prefix.'%"', $connection );

               if ( ! $all_tables_mysql ) {
                  $errors[] = mysql_error( );
                  $step = 2;
               } else {
                  while ( $table = mysql_fetch_array( $all_tables_mysql ) ) {
                     $all_tables[] = $table[ 0 ];
                  }
               }

               $srch = $siteurl[1];
               $rplc =wp_guess_url();

               if ( isset( $connection ) )
                  $report = icit_srdb_replacer( $connection, $srch, $rplc, $all_tables );
               // print_r($srch);
               // echo '<br>';
               // print_r($rplc);
               // echo '<br>';
               // print_r($all_tables);
               // echo '<br>';
               // print_r($report);

            update_option('blogname', $blog_title);
            update_option('admin_email', $user_email);
            update_option('blog_public', $public);

            $guessurl = wp_guess_url();

            update_option('siteurl', $guessurl);

            // If not a public blog, don't ping.
            if ( ! $public )
               update_option('default_pingback_flag', 0);

            // Create default user. If the user already exists, the user tables are
            // being shared among blogs. Just set the role in that case.
            $user_id = username_exists($user_name);
            $user_password = trim($user_password);
            $email_password = false;
            if ( !$user_id && empty($user_password) ) {
               $user_password = wp_generate_password( 12, false );
               $message = __('<strong><em>Note that password</em></strong> carefully! It is a <em>random</em> password that was generated just for you.');
               $user_id = wp_create_user($user_name, $user_password, $user_email);
               update_user_option($user_id, 'default_password_nag', true, true);
               $email_password = true;
            } else if ( !$user_id ) {
               // Password has been provided
               $message = '<em>'.__('Your chosen password.').'</em>';
               $user_id = wp_create_user($user_name, $user_password, $user_email);
            } else if ($user_id && empty($user_password)) {
                $user_password = wp_generate_password( 12, false );
               $message = __('<strong><em>Note that password</em></strong> carefully! It is a <em>random</em> password that was generated just for you.');
                wp_update_user(array('ID'=>$user_id,'user_pass'=>$user_password));
               update_user_option($user_id, 'default_password_nag', true, true);
               $email_password = true;
              
            }
            else{
                 wp_update_user(array('ID'=>$user_id,'user_pass'=>$user_password));
                 $message = '<em>'.__('Your chosen password.').'</em>';
            }

            if($user_name!='admin'){
               $adminID = username_exists('admin');
               wp_delete_user($adminID, $user_id);
            }

            $user = new WP_User($user_id);
            $user->set_role('administrator');
           
            update_option('permalink_structure', '');
            //flush_rewrite_rules();

            wp_new_blog_notification($blog_title, $guessurl, $user_id, ($email_password ? $user_password : __('The password you chose during the install.') ) );

            wp_cache_flush();
            
            return array('url' => $guessurl, 'user_id' => $user_id, 'password' => $user_password, 'password_message' => $message);
            
         }


function wallpress_install_hook(){

?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
<head>
   <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
   <title><?php _e( 'WordPress &rsaquo; Installation' ); ?></title>
   <?php wp_admin_css( 'install', true ); ?>
</head>
<body<?php if ( is_rtl() ) echo ' class="rtl"'; ?>>
<h1 id="logo"><img alt="WordPress" src="../wp-admin/images/wordpress-logo.png?ver=20120216" /></h1>
<h1>Hello, I'm Designwall Quickstart! </h1>
<p>You're installing me using the Quickstart Installation package, which will help me to look exactly like what you have seen on <a href="http://demo.designwall.com" target="_blank">DesignWall</a> demo!</p>
<form id="setup" method="post" action="install.php">
   <table class="form-table">
      
      <tr>
         <th scope="row"><label for="data_select"><?php _e( 'Database' ); ?></label></th>
         <td colspan="2">
               <input type="radio" name="data_select" value="1" checked> Designwall sample data <br>
         </td>
      </tr>
   </table>
   <p class="step"><input type="submit" name="Submit" value="<?php esc_attr_e( 'Next step' ); ?>" class="button" /></p>
</form>

</body>
</html>
<?php

}

function recalculate_array($matches) {
   $recalculate = preg_replace('!s:(\d+):"(.*?)";!e', "'s:'.strlen('$2').':\"$2\";'", $matches[2]);
   return $matches[1].$recalculate.$matches[3];
}

//
// remove_remarks will strip the sql comment lines out of an uploaded sql file
//
function remove_remarks($sql,$prefix,$gurl)
{
   preg_match('/(`\w+)(options`)/',$sql,$current_prefix);
   preg_match('/siteurl\', \'(.+)\',/',$sql,$siteurl);
   preg_match('/wallpress_theme_options\', \'(.+)\',/',$sql,$arrcustomize);
  
   $newcustomize = recursive_unserialize_replace($siteurl[1],$gurl,$arrcustomize[1]);

   $lines = explode("\n", $sql);
   $patterns = array();
   
   $patterns[0] = '/'.$current_prefix[1].'/'; 
   $patterns[1] = '/'.preg_quote($arrcustomize[1],'/').'/';
   //$patterns[2] = '/'.str_replace("/","\\/",$siteurl[1]).'/';
   $patterns[3] ='/\w+user_roles/';
   $patterns[4] ='/\w+capabilities/';
   $patterns[5] ='/\w+user_level/';

   //print_r($patterns); exit();
   $replacements = array();
   $replacements[0] = '`'.$prefix;
   $replacements[1] = $newcustomize;
   //$replacements[2] = $gurl;
   $replacements[3] = $prefix.'user_roles';
   $replacements[4] = $prefix.'capabilities';
   $replacements[5] = $prefix.'user_level';



   // try to keep mem. use down
   $sql = "";

   $linecount = count($lines);
   $output = "";

   for ($i = 0; $i < $linecount; $i++)
   {
      if (($i != ($linecount - 1)) || (strlen($lines[$i]) > 0))
      {
         if (isset($lines[$i][0]) && $lines[$i][0] != "#")
         {
            $lines[$i] = preg_replace($patterns, $replacements, $lines[$i]);
            $output .= $lines[$i] . "\n";
         }
         else
         {
            $output .= "\n";
         }
         // Trading a bit of speed for lower mem. use here.
         $lines[$i] = "";
      }
   }

   return $output;

}

//
// split_sql_file will split an uploaded sql file into single sql statements.
// Note: expects trim() to have already been run on $sql.
//
function split_sql_file($sql, $delimiter)
{
   // Split up our string into "possible" SQL statements.
   $tokens = explode($delimiter, $sql);

   // try to save mem.
   $sql = "";
   $output = array();

   // we don't actually care about the matches preg gives us.
   $matches = array();

   // this is faster than calling count($oktens) every time thru the loop.
   $token_count = count($tokens);
   for ($i = 0; $i < $token_count; $i++)
   {
      // Don't wanna add an empty string as the last thing in the array.
      if (($i != ($token_count - 1)) || (strlen($tokens[$i] > 0)))
      {
         // This is the total number of single quotes in the token.
         $total_quotes = preg_match_all("/'/", $tokens[$i], $matches);
         // Counts single quotes that are preceded by an odd number of backslashes,
         // which means they're escaped quotes.
         $escaped_quotes = preg_match_all("/(?<!\\\\)(\\\\\\\\)*\\\\'/", $tokens[$i], $matches);

         $unescaped_quotes = $total_quotes - $escaped_quotes;

         // If the number of unescaped quotes is even, then the delimiter did NOT occur inside a string literal.
         if (($unescaped_quotes % 2) == 0)
         {
            // It's a complete sql statement.
            $output[] = $tokens[$i];
            // save memory.
            $tokens[$i] = "";
         }
         else
         {
            // incomplete sql statement. keep adding tokens until we have a complete one.
            // $temp will hold what we have so far.
            $temp = $tokens[$i] . $delimiter;
            // save memory..
            $tokens[$i] = "";

            // Do we have a complete statement yet?
            $complete_stmt = false;

            for ($j = $i + 1; (!$complete_stmt && ($j < $token_count)); $j++)
            {
               // This is the total number of single quotes in the token.
               $total_quotes = preg_match_all("/'/", $tokens[$j], $matches);
               // Counts single quotes that are preceded by an odd number of backslashes,
               // which means they're escaped quotes.
               $escaped_quotes = preg_match_all("/(?<!\\\\)(\\\\\\\\)*\\\\'/", $tokens[$j], $matches);

               $unescaped_quotes = $total_quotes - $escaped_quotes;

               if (($unescaped_quotes % 2) == 1)
               {
                  // odd number of unescaped quotes. In combination with the previous incomplete
                  // statement(s), we now have a complete statement. (2 odds always make an even)
                  $output[] = $temp . $tokens[$j];

                  // save memory.
                  $tokens[$j] = "";
                  $temp = "";

                  // exit the loop.
                  $complete_stmt = true;
                  // make sure the outer loop continues at the right point.
                  $i = $j;
               }
               else
               {
                  // even number of unescaped quotes. We still don't have a complete statement.
                  // (1 odd and 1 even always make an odd)
                  $temp .= $tokens[$j] . $delimiter;
                  // save memory.
                  $tokens[$j] = "";
               }

            } // for..
         } // else
      }
   }

   return $output;
}


/**
 * Used to check the $_post tables array and remove any that don't exist.
 *
 * @param array $table The list of tables from the $_post var to be checked.
 *
 * @return array  Same array as passed in but with any tables that don'e exist removed.
 */
function check_table_array( $table = '' ){
   global $all_tables;
   return in_array( $table, $all_tables );
}


/**
 * Simply create a submit button with a JS confirm popup if there is need.
 *
 * @param string $text    Button string.
 * @param string $warning Submit warning pop up text.
 *
 * @return null
 */
function icit_srdb_submit( $text = 'Submit', $warning = '' ){
   $warning = str_replace( "'", "\'", $warning ); ?>
   <input type="submit" class="button" value="<?php echo htmlentities( $text, ENT_QUOTES, 'UTF-8' ); ?>" <?php echo ! empty( $warning ) ? 'onclick="if (confirm(\'' . htmlentities( $warning, ENT_QUOTES, 'UTF-8' ) . '\')){return true;}return false;"' : ''; ?>/> <?php
}


/**
 * Simple html esc
 *
 * @param string $string Thing that needs escaping
 * @param bool $echo   Do we echo or return?
 *
 * @return string    Escaped string.
 */
function esc_html_attr( $string = '', $echo = false ){
   $output = htmlentities( $string, ENT_QUOTES, 'UTF-8' );
   if ( $echo )
      echo $output;
   else
      return $output;
}


/**
 * Walk and array replacing one element for another. ( NOT USED ANY MORE )
 *
 * @param string $find    The string we want to replace.
 * @param string $replace What we'll be replacing it with.
 * @param array $data    Used to pass any subordinate arrays back to the
 * function for searching.
 *
 * @return array    The original array with the replacements made.
 */
function recursive_array_replace( $find, $replace, $data ) {
    if ( is_array( $data ) ) {
        foreach ( $data as $key => $value ) {
            if ( is_array( $value ) ) {
                recursive_array_replace( $find, $replace, $data[ $key ] );
            } else {
                // have to check if it's string to ensure no switching to string for booleans/numbers/nulls - don't need any nasty conversions
                if ( is_string( $value ) )
               $data[ $key ] = str_replace( $find, $replace, $value );
            }
        }
    } else {
        if ( is_string( $data ) )
         $data = str_replace( $find, $replace, $data );
    }
}


/**
 * Take a serialised array and unserialise it replacing elements as needed and
 * unserialising any subordinate arrays and performing the replace on those too.
 *
 * @param string $from       String we're looking to replace.
 * @param string $to         What we want it to be replaced with
 * @param array  $data       Used to pass any subordinate arrays back to in.
 * @param bool   $serialised Does the array passed via $data need serialising.
 *
 * @return array  The original array with all elements replaced as needed.
 */
function recursive_unserialize_replace( $from = '', $to = '', $data = '', $serialised = false ) {

   // some unseriliased data cannot be re-serialised eg. SimpleXMLElements
   try {

      if ( is_string( $data ) && ( $unserialized = @unserialize( $data ) ) !== false ) {
         $data = recursive_unserialize_replace( $from, $to, $unserialized, true );
      }

      elseif ( is_array( $data ) ) {
         $_tmp = array( );
         foreach ( $data as $key => $value ) {
            $_tmp[ $key ] = recursive_unserialize_replace( $from, $to, $value, false );
         }

         $data = $_tmp;
         unset( $_tmp );
      }

      else {
         if ( is_string( $data ) )
            $data = str_replace( $from, $to, $data );
      }

      if ( $serialised )
         return serialize( $data );

   } catch( Exception $error ) {

   }

   return $data;
}




/**
 * The main loop triggered in step 5. Up here to keep it out of the way of the
 * HTML. This walks every table in the db that was selected in step 3 and then
 * walks every row and column replacing all occurences of a string with another.
 * We split large tables into 50,000 row blocks when dealing with them to save
 * on memmory consumption.
 *
 * @param mysql  $connection The db connection object
 * @param string $search     What we want to replace
 * @param string $replace    What we want to replace it with.
 * @param array  $tables     The tables we want to look at.
 *
 * @return array    Collection of information gathered during the run.
 */
function icit_srdb_replacer( $connection, $search = '', $replace = '', $tables = array( ) ) {
   global $guid, $exclude_cols;
   $guid=0;   
   $report = array( 'tables' => 0,
                'rows' => 0,
                'change' => 0,
                'updates' => 0,
                'start' => microtime( ),
                'end' => microtime( ),
                'errors' => array( ),
                );

   if ( is_array( $tables ) && ! empty( $tables ) ) {
      foreach( $tables as $table ) {
         $report[ 'tables' ]++;

         $columns = array( );

         // Get a list of columns in this table
          $fields = mysql_query( 'DESCRIBE ' . $table, $connection );
         while( $column = mysql_fetch_array( $fields ) )
            $columns[ $column[ 'Field' ] ] = $column[ 'Key' ] == 'PRI' ? true : false;

         // Count the number of rows we have in the table if large we'll split into blocks, This is a mod from Simon Wheatley
         $row_count = mysql_query( 'SELECT COUNT(*) FROM ' . $table, $connection );
         $rows_result = mysql_fetch_array( $row_count );
         $row_count = $rows_result[ 0 ];
         if ( $row_count == 0 )
            continue;

         $page_size = 50000;
         $pages = ceil( $row_count / $page_size );

         for( $page = 0; $page < $pages; $page++ ) {

            $current_row = 0;
            $start = $page * $page_size;
            $end = $start + $page_size;
            // Grab the content of the table
            $data = mysql_query( sprintf( 'SELECT * FROM %s LIMIT %d, %d', $table, $start, $end ), $connection );

            if ( ! $data )
               $report[ 'errors' ][] = mysql_error( );

            while ( $row = mysql_fetch_array( $data ) ) {

               $report[ 'rows' ]++; // Increment the row counter
               $current_row++;

               $update_sql = array( );
               $where_sql = array( );
               $upd = false;

               foreach( $columns as $column => $primary_key ) {
                  if ( $guid == 1 && in_array( $column, $exclude_cols ) )
                     continue;

                  $edited_data = $data_to_fix = $row[ $column ];

                  // Run a search replace on the data that'll respect the serialisation.
                  $edited_data = recursive_unserialize_replace( $search, $replace, $data_to_fix );

                  // Something was changed
                  if ( $edited_data != $data_to_fix ) {
                     $report[ 'change' ]++;
                     $update_sql[] = $column . ' = "' . mysql_real_escape_string( $edited_data ) . '"';
                     $upd = true;
                  }

                  if ( $primary_key )
                     $where_sql[] = $column . ' = "' . mysql_real_escape_string( $data_to_fix ) . '"';
               }

               if ( $upd && ! empty( $where_sql ) ) {
                  $sql = 'UPDATE ' . $table . ' SET ' . implode( ', ', $update_sql ) . ' WHERE ' . implode( ' AND ', array_filter( $where_sql ) );
                  $result = mysql_query( $sql, $connection );
                  if ( ! $result )
                     $report[ 'errors' ][] = mysql_error( );
                  else
                     $report[ 'updates' ]++;

               } elseif ( $upd ) {
                  $report[ 'errors' ][] = sprintf( '"%s" has no primary key, manual change needed on row %s.', $table, $current_row );
               }

            }
         }
      }

   }
   $report[ 'end' ] = microtime( );

   return $report;
}


/**
 * Take an array and turn it into an English formatted list. Like so:
 * array( 'a', 'b', 'c', 'd' ); = a, b, c, or d.
 *
 * @param array $input_arr The source array
 *
 * @return string    English formatted string
 */
function eng_list( $input_arr = array( ), $sep = ', ', $before = '"', $after = '"' ) {
   if ( ! is_array( $input_arr ) )
      return false;

   $_tmp = $input_arr;

   if ( count( $_tmp ) >= 2 ) {
      $end2 = array_pop( $_tmp );
      $end1 = array_pop( $_tmp );
      array_push( $_tmp, $end1 . $after . ' or ' . $before . $end2 );
   }

   return $before . implode( $before . $sep . $after, $_tmp ) . $after;
}


?>