<?php
/*
Plugin Name: ACF oEmbed Field Type
Plugin URI:  https://github.com/cftp/acf-oembed-field
Description: An oEmbed field type for Advanced Custom Fields
Version:     1.0
Author:      Code For The People
Author URI:  http://codeforthepeople.com/
Text Domain: acf-oembed-field
Domain Path: /languages/
License:     GPL v2 or later

Copyright © 2014 Code for the People ltd

                _____________
               /      ____   \
         _____/       \   \   \
        /\    \        \___\   \
       /  \    \                \
      /   /    /          _______\
     /   /    /          \       /
    /   /    /            \     /
    \   \    \ _____    ___\   /
     \   \    /\    \  /       \
      \   \  /  \____\/    _____\
       \   \/        /    /    / \
        \           /____/    /___\
         \                        /
          \______________________/


This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

*/

if ( !function_exists('acf_register_oembed_field') ):

	add_action('acf/register_fields', 'acf_register_oembed_field');

	function acf_register_oembed_field() {
		include_once dirname( __FILE__ ) . '/field.php';
	}

endif;
