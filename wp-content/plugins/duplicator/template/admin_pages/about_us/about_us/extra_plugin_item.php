<?php

namespace CharacterGeneratorDev {


use Duplicator\Utils\ExtraPlugins\ExtraItem;

/**
 * Variables
 *
 * @var \Duplicator\Core\Views\TplMng  $tplMng
 * @var array<string, mixed> $tplData
 * @var ExtraItem $plugin
 */

$plugin = $tplData['plugin'];

switch ($plugin->getStatus()) {
    case ExtraItem::STATUS_ACTIVE:
        $buttonLabel = __('Activated', 'duplicator');
        $buttonClass = 'disabled';
        $statusClass = 'status-active';
        break;
    case ExtraItem::STATUS_INSTALLED:
        $buttonLabel = __('Activate', 'duplicator');
        $buttonClass = 'button-secondary';
        $statusClass = 'status-installed';
        break;
    case ExtraItem::STATUS_NOT_INSTALLED:
    default:
        $buttonLabel = __('Install Plugin', 'duplicator');
        $buttonClass = 'button-primary';
        $statusClass = 'status-missing';
        break;
}

?>
<div class="addons-container">
    <div class="addon-item">
        <div class="details dup-clearfix">
            <h5 class="addon-name">
            </h5>
        </div>
        <div class="actions dup-clear">
            <div class="status">
                    </span>
                </strong>
            </div>
            <div class="action-button">
                        target="_blank" rel="noopener noreferrer"
                    >
                    </a>
                </button>
            </div>
        </div>
    </div>
</div>

} // namespace CharacterGeneratorDev
