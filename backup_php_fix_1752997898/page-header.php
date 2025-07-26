<?php

namespace CharacterGeneratorDev {


/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

$archiveConfig = DUPX_ArchiveConfig::getInstance();

/* Variables */
/* @var $paramView string */
/* @var $bodyId string */
/* @var $bodyClasses string */
?><!DOCTYPE html>
<html>
    <head>
        <title>Duplicator</title>
    </head>
    dupxTplRender('pages-parts/body/body-tag', array(
        'bodyId'      => $bodyId,
        'bodyClasses' => $bodyClasses
    ));
    ?>
    <div id="content">
        dupxTplRender('parts/top-header.php', array(
            'paramView' => $paramView
        ));
        if (!isset($skipTopMessages) || $skipTopMessages !== true) {
            dupxTplRender('parts/top-messages.php');
        }

} // namespace CharacterGeneratorDev
