<?php

namespace CharacterGeneratorDev {


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use CustomTables\Diagram;

require_once(CUSTOMTABLES_LIBRARIES_PATH . DIRECTORY_SEPARATOR . 'customtables' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'admin-diagram.php');
$diagram = new Diagram();


//admin_enqueue_scripts( 'customtables-js-raphael', home_url().'/wp-content/plugins/customtables/libraries/customtables/media/js/raphael.min.js', array('jquery'), $this->version, false );
//admin_enqueue_scripts( 'customtables-js-diagram', home_url().'/wp-content/plugins/customtables/libraries/customtables/media/js/diagram.js', array('jquery'), $this->version, false );


?>
<form method="post" name="customtables-diagram" id="customtables-diagram" class="validate" novalidate="novalidate">

    <style>
        #canvas_container {
            width: 100%;
            border: 1px solid #aaa;
        }
    </style>
    <div id="canvas_container"></div>

    <script>
        TableCategoryID = null;
    </script>

    <div class="row">
        <div class="col-md-12">
            <div id="j-main-container" class="j-main-container">



                <input type="hidden" name="task" value=""/>
            </div>
        </div>
    </div>
 */ ?>
</form>
} // namespace CharacterGeneratorDev
