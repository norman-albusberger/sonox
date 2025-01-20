<?php
require_once "loxberry_web.php";
require_once "loxberry_log.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Sprachdateien laden
$L = LBSystem::readlanguage("language.ini");

// Titel und Navigation
require_once "navigation.php";
$navbar[3]['active'] = true;

// Header ausgeben
LBWeb::lbheader($L['COMMON.TITLE'], "http://www.loxwiki.eu:80/x/2wzL", "help.html");

?>
<h1><?= $L['COMMON.TITLE']; ?></h1>

<h2><?= $L['ABOUT.TITLE']; ?></h2>
<p><strong><?= $L['ABOUT.PLUGIN_NAME_LABEL']; ?>:</strong> <?= $L['ABOUT.PLUGIN_NAME']; ?></p>
<p><strong><?= $L['ABOUT.VERSION_LABEL']; ?>:</strong> <?= $L['ABOUT.VERSION']; ?></p>
<p>
    <strong><?= $L['ABOUT.AUTHOR_LABEL']; ?>:</strong> <?= $L['ABOUT.AUTHOR']; ?>
    <a href="<?= $L['ABOUT.AUTHOR_BIO_LINK']; ?>" target="_blank"><?= $L['ABOUT.AUTHOR_BIO_LABEL']; ?></a>
</p>
<p><?= $L['ABOUT.DESCRIPTION']; ?></p>

<h2><?= $L['ABOUT.LINKS_TITLE']; ?></h2>
<ul data-role="listview" data-inset="true">
    <li>
        <a href="https://wiki.loxberry.de/plugins/sonox/start" target="_blank">
            <?= $L['ABOUT.LOXBERRY_WIKI_LINK_TEXT']; ?>
        </a>
    </li>
    <li>
        <a href="https://www.loxone.com/enen/kb/virtual-inputs-outputs/" target="_blank">
            <?= $L['ABOUT.LOXONE_DOCS_LINK_TEXT']; ?>
        </a>
    </li>
    <li>
        <a href="https://github.com/norman-albusberger/sonox" target="_blank">
            <?= $L['ABOUT.GITHUB_REPO_LINK_TEXT']; ?>
        </a>
    </li>
</ul>

<h3><?= $L['ABOUT.ISSUES_TITLE']; ?></h3>
<p><?= $L['ABOUT.ISSUES_DESCRIPTION']; ?></p>
<ol data-role="listview" data-inset="true" data-count-theme="b">
    <li> <a href="https://github.com/norman-albusberger/sonox/issues"><?= $L['ABOUT.ISSUE_STEP1'] ?></a></li>
    <li><?= $L['ABOUT.ISSUE_STEP2']; ?></li>
    <li><?= $L['ABOUT.ISSUE_STEP3']; ?></li>
</ol>


<?php
// Footer ausgeben
LBWeb::lbfooter();
?>
