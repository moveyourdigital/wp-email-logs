<?php

$role = get_role( 'administrator' );
$role->remove_cap( 'read_emailog' );
$role->remove_cap( 'delete_emailog' );
