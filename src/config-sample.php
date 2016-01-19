<?php
/* ----------------------------------------------------------------------------
 * Easy!Appointments - Open Source Web Scheduler
 * 
 * @package     EasyAppointments
 * @author      A.Tselegidis <alextselegidis@gmail.com>
 * @copyright   Copyright (c) 2013 - 2016, Alex Tselegidis
 * @license     http://opensource.org/licenses/GPL-3.0 - GPLv3 
 * @link        http://easyappointments.org
 * @since       v1.0.0
 * ---------------------------------------------------------------------------- */

/**
 * Easy!Appointments Configuration File 
 * 
 * Set your installation BASE_URL * without the trailing slash * and the database 
 * credentials in order to connect to the database. You can enable the DEBUG_MODE
 * while developing the application.
 * 
 * IMPORTANT: 
 * If you are updating from version 1.0 you will have to create a new "config.php"
 * file because the old "configuration.php" is not used anymore.
 */
class Config {
    // ------------------------------------------------------------------------
    // General Settings
    // ------------------------------------------------------------------------
    const BASE_URL      = 'http://url-to-easyappointments-directory';
    const DEBUG_MODE    = FALSE;
    
    // options: 'english','german','greek','hungarian','portuguese',
	// 'chinese','dutch','french','japanese','polish','spanish','italian',
    // 'danish','luxembourgish','slovak','finnish','russian','romanian',
    // 'turkish','hindi'
    const DEFAULT_LANGUAGE = english;
     
    // ------------------------------------------------------------------------
    // Database Settings
    // ------------------------------------------------------------------------
    const DB_HOST       = '';    
    const DB_NAME       = '';
    const DB_USERNAME   = '';
    const DB_PASSWORD   = '';

    // ------------------------------------------------------------------------
    // Google Calendar Sync
    // ------------------------------------------------------------------------
    const GOOGLE_SYNC_FEATURE   = FALSE; // Enter TRUE or FALSE
    const GOOGLE_PRODUCT_NAME   = '';
    const GOOGLE_CLIENT_ID      = ''; 
    const GOOGLE_CLIENT_SECRET  = ''; 
    const GOOGLE_API_KEY        = '';
}
/* End of file config.php */
/* Location: ./config.php */
