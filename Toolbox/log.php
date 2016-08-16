<?php
/**
 * Copyright 2016 Lengow SAS.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may
 * not use this file except in compliance with the License. You may obtain
 * a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 *
 * @author    Team Connector <team-connector@lengow.com>
 * @copyright 2016 Lengow SAS
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 */

$action = isset($_REQUEST['action']) ?  $_REQUEST['action'] : null;
$file = isset($_REQUEST['file']) ?  $_REQUEST['file'] : null;
require_once('./config.inc.php');

switch ($action) {
    case 'download':
        Shopware_Plugins_Backend_Lengow_Components_LengowLog::download($file);
        break;
    case 'download_all':
        Shopware_Plugins_Backend_Lengow_Components_LengowLog::download();
        break;
}

$listFile = Shopware_Plugins_Backend_Lengow_Components_LengowLog::getPaths();
require('./views/header.php');
?>
    <div class="container">
        <h1><?php echo $locale->t('toolbox/log/log_files'); ?></h1>
        <ul class="list-group">
            <?php
            foreach ($listFile as $file) {
                echo '<li class="list-group-item">';
                echo '<a href="/engine/Shopware/Plugins/Community/Backend/Lengow/Toolbox/log.php?action=download&file='
                    .urlencode($file['short_path']).'">
                    <i class="fa fa-download"></i> '.$file['name'].'</a>';
                echo '</li>';
            }
            echo '<li class="list-group-item">';
            echo '<a href="/engine/Shopware/Plugins/Community/Backend/Lengow/Toolbox/log.php?action=download_all">
                <i class="fa fa-download"></i> '.$locale->t('toolbox/log/download_all').'</a>';
            echo '</li>';
            ?>
        </ul>
    </div><!-- /.container -->
<?php
require 'views/footer.php';
