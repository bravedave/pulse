<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
 * note:
 *  id, autoincrement primary key is added to all tables - no need to specify
 *  field types are MySQL and are converted to SQLite equivalents as required
 */

$dbc = \sys::dbCheck('pulse_state');

$dbc->defineField('created', 'datetime');
$dbc->defineField('updated', 'datetime');

$dbc->defineField('pulse_id', 'bigint');
$dbc->defineField('users_id', 'bigint');
$dbc->defineField('seen', 'tinyint');

$dbc->check();  // actually do the work, check that table and fields exist