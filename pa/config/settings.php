<?php
/**
 * Poweradmin Settings Configuration File
 * 
 * This file was automatically migrated from the old configuration format.
 * Generated on: 2025-08-13 21:00:57
 * 
 * IMPORTANT: Review this file to ensure all settings were correctly migrated.
 * For more information about configuration options, see settings.defaults.php
 */

return [
  'database' => 
  [
    'host' => 'localhost',
    'user' => 'powerdns',
    'password' => 'zY2D2d43d8pf3R',
    'name' => 'powerdns',
    'type' => 'mysql',
  ],
  'security' => 
  [
    'session_key' => '^fMfF*#yZ8PReDD4ZlRqVvHzJc8EXC5jy@VB(U1r#@zP@O',
  ],
  'interface' => 
  [
    'language' => 'cs_CZ',
    'style' => 'dark',
    'display_serial_in_zone_list' => true,
    'add_reverse_record' => false,
    'theme' => 'default',
  ],
  'dns' => 
  [
    'hostmaster' => 'hostmaster.rhscz.eu',
    'ns1' => 'ns1.rhscz.eu',
    'ns2' => 'ns2.rhscz.eu',
    'ttl' => 300,
    'top_level_tld_check' => true,
    'third_level_check' => true,
    'txt_auto_quote' => true,
  ],
  'dnssec' => 
  [
    'enabled' => true,
  ],
  'pdns_api' => 
  [
    'url' => 'http://127.0.0.1:8081/',
    'key' => 'ccdc175b-8c8c-4cd9-b2bc-efcb99e92f15',
  ],
  'logging' => 
  [
  ],
  'ldap' => 
  [
  ],
  'misc' => 
  [
  ],
];
