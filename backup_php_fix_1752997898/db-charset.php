<?php

namespace CharacterGeneratorDev {


/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Params\Descriptors\ParamDescDatabase;
use Duplicator\Installer\Core\Params\PrmMng;
use Duplicator\Libs\Snap\SnapJson;

$paramsManager = PrmMng::getInstance();
?>
<script>

    $(document).ready(function ()
    {
        $('#' + dbCharsetDefaultID).on('change', function () {
            let collateDefault = $(this).find(':selected').data('collation-default');
            let collations = $(this).find(':selected').data('collations');
            let collateObj = $('#' + dbCollateDefaultID);

            collateObj.empty();
            $("<option></option>")
                    .appendTo(collateObj)
                    .attr('value', '')
                    .prop('selected', true);

            for (let i = 0; i < collations.length; i++) {
                $("<option></option>")
                        .appendTo(collateObj)
                        .attr('value', collations[i])
                        .text(label);
            }
        });
    });
</script>
} // namespace CharacterGeneratorDev
