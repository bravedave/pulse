<?php
  // file: src/app/pulse/dao/db/pulse.php
  // MIT License

/**
 * note:
 *  id, autoincrement primary key is added to all tables - no need to specify
 *  field types are MySQL and are converted to SQLite equivalents as required
 */

$dbc = \sys::dbCheck('pulse');

$dbc->defineField('created', 'datetime');
$dbc->defineField('updated', 'datetime');

$dbc->defineField('title', 'varchar');
$dbc->defineField('content', 'text');
$dbc->defineField('created_by', 'int');

$dbc->check();  // actually do the work, check that table and fields exist