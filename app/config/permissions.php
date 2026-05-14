<?php
/**
 * Permission Mapping Configuration
 * Permission Slug => Routes it grants access to
 */
return [
    'home'        => ['/home', '/'],
    'users'       => ['/users', '/add/user', '/edit/user', '/view/user'],
    'settings'    => ['/settings', '/print/report']
];
