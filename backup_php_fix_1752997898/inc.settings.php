<?php

namespace CharacterGeneratorDev {


use Duplicator\Core\Views\TplMng;

defined('ABSPATH') || defined('DUPXABSPATH') || exit;
?>

<!-- ==============================
SERVER SETTINGS -->
<div class="dup-box">
<div class="dup-box-title">
    <i class="fas fa-tachometer-alt"></i>
    <div class="dup-box-arrow"></div>
</div>
        'parts/tools/server_settings_table',
        [
            'serverSettings' => DUP_Server::getServerSettingsData(),
        ]
    ); ?>
</div> <!-- end .dup-box-panel -->
</div> <!-- end .dup-box -->
<br/>

} // namespace CharacterGeneratorDev
