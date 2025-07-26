<?php

namespace CharacterGeneratorDev {


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use CustomTables\common;

$errors = common::getTransientMessages('customtables_error_message');
$messages = common::getTransientMessages('customtables_success_message');

?>


<div class="wrap ct_doc">

    <h2 class="nav-tab-wrapper wp-clearfix">
        <button data-toggle="tab" data-tabs=".gtabs.demo" data-tab=".tab-1" class="nav-tab nav-tab-active">Diagram</button>
        <button data-toggle="tab" data-tabs=".gtabs.demo" data-tab=".tab-2" class="nav-tab">Checks</button>
    </h2>


    <div class="gtabs demo">
        <div class="gtab active tab-1">
        </div>

        <div class="gtab tab-2">
        </div>
    </div>

</div>

} // namespace CharacterGeneratorDev
