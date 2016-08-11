<?php
$MD5_FILE_NAME = "checkmd5.csv";

$listFile = array(
    'Bootstrap/Database.php',
    'Bootstrap/Form.php',
    'Components/LengowCheck.php',
    'Components/LengowConfiguration.php',
    'Components/LengowElements.php',
    'Components/LengowException.php',
    'Components/LengowExport.php',
    'Components/LengowFeed.php',
    'Components/LengowFile.php',
    'Components/LengowImport.php',
    'Components/LengowImportOrder.php',
    'Components/LengowLog.php',
    'Components/LengowMain.php',
    'Components/LengowMarketplace.php',
    'Components/LengowOrder.php',
    'Components/LengowProduct.php',
    'Components/LengowStatistic.php',
    'Components/LengowSync.php',
    'Components/LengowTranslation.php',
    'Controllers/Backend/Lengow.php',
    'Controllers/Backend/LengowExport.php',
    'Controllers/Backend/LengowHelp.php',
    'Controllers/Backend/LengowHome.php',
    'Controllers/Backend/LengowImport.php',
    'Controllers/Backend/LengowLogs.php',
    'Controllers/Backend/LengowSync.php',
    'Models/Lengow/Order.php',
    'Models/Lengow/Settings.php',
    'Toolbox/views/footer.php',
    'Toolbox/views/header.php',
    'Toolbox/checksum.php',
    'Toolbox/config.inc.php',
    'Toolbox/index.php',
    'Toolbox/log.php',
    'Toolbox/checksum.php',
    'Tools/Translate.php',
    'Views/backend/lengow/controller/dashboard.js',
    'Views/backend/lengow/controller/export.js',
    'Views/backend/lengow/controller/help.js',
    'Views/backend/lengow/controller/import.js',
    'Views/backend/lengow/controller/main.js',
    'Views/backend/lengow/model/article.js',
    'Views/backend/lengow/model/logs.js',
    'Views/backend/lengow/model/shops.js',
    'Views/backend/lengow/resources/css/lengow-components.css',
    'Views/backend/lengow/resources/css/lengow-layout.css',
    'Views/backend/lengow/resources/css/lengow-pages.css',
    'Views/backend/lengow/resources/lengow-template.tpl',
    'Views/backend/lengow/store/article.js',
    'Views/backend/lengow/store/logs.js',
    'Views/backend/lengow/store/shops.js',
    'Views/backend/lengow/view/dashboard/panel.js',
    'Views/backend/lengow/view/export/container.js',
    'Views/backend/lengow/view/export/grid.js',
    'Views/backend/lengow/view/export/panel.js',
    'Views/backend/lengow/view/export/tree.js',
    'Views/backend/lengow/view/help/panel.js',
    'Views/backend/lengow/view/import/panel.js',
    'Views/backend/lengow/view/logs/panel.js',
    'Views/backend/lengow/view/main/home.js',
    'Views/backend/lengow/view/main/sync.js',
    'Views/backend/lengow/app.js',
    'Webservice/cron.php',
    'Webservice/export.php',
    'Bootstrap.php',
    'plugin.json',
);

$dir = dirname(dirname(__FILE__));
$fp = fopen($MD5_FILE_NAME, 'w');
foreach ($listFile as $file) {
    $path = $dir . DIRECTORY_SEPARATOR . $file;
    $md5 = md5_file($path);
    fwrite($fp, $file.'|'.$md5.PHP_EOL);
}
fclose($fp);