<?php
/**
 * This file is meant for use during smoke tests to manually set the
 * 'HTTP_X_WPENGINE_SEGMENT' server var
 *
 * @package segment-cache-for-wp-engine
 */

$_SERVER['HTTP_X_WPENGINE_SEGMENT'] = 'smoketest';
