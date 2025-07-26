<?php

namespace CharacterGeneratorDev {


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use CustomTables\common;

$errors = common::getTransientMessages('customtables_error_message');
$messages = common::getTransientMessages('customtables_success_message');

?>

<div class="wrap">

    <hr style="margin-bottom: 30px; "/>


    <a href="admin.php?page=customtables-tables" style="margin-right:30px;" class="button">
    </a>

    <a href="admin.php?page=customtables-layouts" style="margin-right:30px;" class="button">
    </a>

    <a href="admin.php?page=customtables-import-tables" style="margin-right:30px;" class="button">
    </a>

    <a href="admin.php?page=customtables-documentation" class="button">
    </a>

</div>

} // namespace CharacterGeneratorDev
