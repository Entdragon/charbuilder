<?php

namespace CharacterGeneratorDev {


/**
 * Duplicator schedule success mail
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

defined("ABSPATH") or die("");

use Duplicator\Utils\LinkManager;
use Duplicator\Utils\Email\EmailHelper;
use Duplicator\Utils\Email\EmailSummary;

/**
 * Variables
 *
 * @var array<string, mixed> $tplData
 */

?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width">
        <style type="text/css">
            a {
              text-decoration: none;
            }

            @media only screen and (max-width: 599px) {
              table.body .main-tbl {
                width: 95% !important;
              }

              .header {
                padding: 15px 15px 12px 15px !important;
              }

              .header img {
                width: 200px !important;
                height: auto !important;
              }
              .content {
                padding: 30px 40px 20px 40px !important;
              }
            }
        </style>
    </head>
                               <img 
                                    alt="logo"
                                > 
                            </td>
                        </tr>
                                                printf(
                                                    _x(
                                                        'Here\'s a quick overview of your backups in the past %s.',
                                                        '%s is the frequency of email summaries.',
                                                        'duplicator'
                                                    ),
                                                    EmailSummary::getFrequencyText()
                                                );
                                                ?>
                                            </p>
                                                </strong>
                                                </br>
                                                if (rand(0, 100) % 2 === 0) {
                                                    _e(
                                                        'With Duplicator Pro you can create fully automatic backups! Schedule your preferred ' .
                                                        'intervals for backups - daily, weekly, or monthly and never worry about data loss again!',
                                                        'duplicator'
                                                    );
                                                } else {
                                                    _e(
                                                        'With Duplicator Pro you can store backups in Google Drive, Amazon S3, OneDrive, Dropbox, ' .
                                                        'or any SFTP/FTP server for added protection.',
                                                        'duplicator'
                                                    );
                                                }
                                                ?>
                                            </p>
                                                    printf(
                                                        esc_html_x(
                                                            'To unlock scheduled backups, remote storages and many other features, %supgrade to PRO%s!',
                                                            '%s and %s are opening and closing link tags to the pricing page.',
                                                            'duplicator'
                                                        ),
                                                        '<a href="' . LinkManager::getCampaignUrl('email-summary', 'Upgrade to PRO') . '" style="'
                                                        . EmailHelper::getStyle('inline-link') . '">',
                                                        '</a>'
                                                    );
                                                    ?>
                                            </p>
                                            </p>
                                                    </th>
                                                    </th>
                                                </tr>
                                                    </td>
                                                            </span>
                                                    </td>
                                                </tr>
                                            </table>
                                            </p>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                                printf(
                                    _x(
                                        'This email was auto-generated and sent from %s.',
                                        '%s is an <a> tag with a link to the current website.',
                                        'duplicator'
                                    ),
                                    '<a href="' . get_site_url() . '" ' .
                                    'style="' . EmailHelper::getStyle('footer-link') . '">'
                                    . wp_specialchars_decode(get_bloginfo('name')) . '</a>'
                                );
                                ?>

                                $faqUrl = LinkManager::getDocUrl('how-to-disable-email-summaries', 'email_summary', 'how to disable');
                                printf(
                                    esc_html_x(
                                        'Learn %1show to disable%2s.',
                                        '%1s and %2s are opening and closing link tags to the documentation.',
                                        'duplicator'
                                    ),
                                    '<a href="' . $faqUrl . '" style="' . EmailHelper::getStyle('footer-link') . '">',
                                    '</a>'
                                );
                                ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
</html>
    

} // namespace CharacterGeneratorDev
