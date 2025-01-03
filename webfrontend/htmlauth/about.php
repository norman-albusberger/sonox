<?php
require_once "loxberry_web.php";
require_once "loxberry_log.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Sprachdateien laden
$L = LBSystem::readlanguage("language.ini");

// plugin.cfg auslesen
$plugin_cfg = parse_ini_file(LBPPLUGINDIR . "/plugin.cfg", true);

// Titel und Navigation
require_once "navigation.php";
$navbar[3]['active'] = true;

// Header ausgeben
LBWeb::lbheader($plugin_cfg['PLUGIN']['TITLE'], "http://www.loxwiki.eu:80/x/2wzL", "help.html");

?>

<div class="container">
    <h1><?php echo $plugin_cfg['PLUGIN']['TITLE']; ?></h1>

    <h2><?php echo $L['ABOUT.TITLE']; ?></h2>
    <p><strong><?php echo $L['ABOUT.PLUGIN_NAME_LABEL']; ?>:</strong> <?php echo $plugin_cfg['PLUGIN']['NAME']; ?></p>
    <p><strong><?php echo $L['ABOUT.VERSION_LABEL']; ?>:</strong> <?php echo $plugin_cfg['PLUGIN']['VERSION']; ?></p>
    <p><strong><?php echo $L['ABOUT.AUTHOR_LABEL']; ?>:</strong> <?php echo $plugin_cfg['AUTHOR']['NAME']; ?></p>
    <p><?php echo $L['ABOUT.DESCRIPTION']; ?></p>

    <h2><?php echo $L['ABOUT.LINKS_TITLE']; ?></h2>
    <ul>
        <li><a href="https://www.loxone.com/enen/kb/virtual-inputs-outputs/" target="_blank"><?php echo $L['ABOUT.LOXONE_DOCS_LINK_TEXT']; ?></a></li>
        <li><a href="https://github.com/norman-albusberger/sonox" target="_blank"><?php echo $L['ABOUT.GITHUB_REPO_LINK_TEXT']; ?></a></li>
    </ul>

    <h3><?php echo $L['ABOUT.ISSUES_TITLE']; ?></h3>
    <p><?php echo $L['ABOUT.ISSUES_DESCRIPTION']; ?></p>
    <ol>
        <li><?php echo $L['ABOUT.ISSUE_STEP1']; ?></li>
        <li><?php echo $L['ABOUT.ISSUE_STEP2']; ?></li>
        <li><?php echo $L['ABOUT.ISSUE_STEP3']; ?></li>
    </ol>
</div>

<?php
// Footer ausgeben
LBWeb::lbfooter();
?>
