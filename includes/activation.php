<?php

$role = get_role( 'administrator' );
$role->add_cap( 'read_emailog' );
$role->add_cap( 'delete_emailog' );
