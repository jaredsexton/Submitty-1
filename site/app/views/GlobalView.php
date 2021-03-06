<?php

namespace app\views;

class GlobalView extends AbstractView {
    public function header($breadcrumbs, $css=array(), $js=array()) {
        $messages = <<<HTML
<div id='messages'>

HTML;

        foreach (array('error', 'notice', 'success') as $type) {
            foreach ($_SESSION['messages'][$type] as $key => $error) {
                $messages .= <<<HTML
    <div id='{$type}-{$key}' class="inner-message alert alert-{$type}">
        <a class="fa fa-times message-close" onClick="removeMessagePopup('{$type}-{$key}');"></a>
        <i class="fa fa-times-circle"></i> {$error}
    </div>

HTML;
                unset($_SESSION['messages'][$type][$key]);
            }
        }
        $messages .= <<<HTML
</div>
HTML;

        $return = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>

HTML;
        if($this->core->getConfig()->isCourseLoaded()) {
            $return .= <<<HTML
    <title>{$this->core->getFullCourseName()}</title>

HTML;
        }
        else {
            $return .= <<<HTML
    <title>Submitty</title>

HTML;
        }

        $return .= <<<HTML
    <link rel="shortcut icon" href="{$this->core->getConfig()->getBaseUrl()}img/favicon.ico" type="image/x-icon" />

HTML;

        foreach($css as $css_ref) {
            $return .= "    <link rel='stylesheet' type='text/css' href='{$css_ref}' />\n";
        }

        if ($this->core->getConfig()->isCourseLoaded() && file_exists($this->core->getConfig()->getCoursePath()."/config/override.css")) {
            $return .= "    <style type='text/css'>\n".file_get_contents($this->core->getConfig()->getCoursePath()."/config/override.css")."\n    </style>\n";
        }

        foreach ($js as $js_ref) {
            $return .= "    <script type='text/javascript' src='{$js_ref}'></script>\n";
        }
        $return .= <<<HTML
</head>
<script>var onAjaxInit;</script>
<body onload="setSiteDetails('{$this->core->getConfig()->getSiteUrl()}', '{$this->core->getCsrfToken()}'); if (onAjaxInit) { onAjaxInit(); }">
{$messages}
<div id="container">

HTML;

        if ($this->core->getConfig()->isCourseLoaded() && $this->core->userLoaded()) {
            if($this->core->getUser()->accessGrading()) {
                if($this->core->getUser()->accessAdmin()) {
                    $return .= <<<HTML
    <div id="nav">
        <ul>
            <li>
                <a href="{$this->core->buildUrl(array('component' => 'admin', 'page' => 'configuration', 'action' => 'view'))}">Course Settings</a>
            </li>
            <li>
                <a href="{$this->core->buildUrl(array('component' => 'admin', 'page' => 'users'))}">Students</a>
            </li>
            <li>
                <a href="{$this->core->buildUrl(array('component' => 'admin', 'page' => 'users', 'action' => 'graders'))}">Graders</a>
            </li>
            <li>
                <a href="{$this->core->buildUrl(array('component' => 'admin', 'page' => 'users', 'action' => 'rotating_sections'))}">Setup Rotating Sections</a>
            </li>
            <li>
                <a href="{$this->core->buildUrl(array('component' => 'admin', 'page' => 'late', 'action' => 'view_late'))}">Late Days Allowed</a>
            </li>
            <li>
                <a href="{$this->core->buildUrl(array('component' => 'admin', 'page' => 'late', 'action' => 'view_extension'))}">Excused Absence Extensions</a>
            </li>
            <li>
                <a href="{$this->core->buildUrl(array('component' => 'admin', 'page' => 'reports', 'action' => 'reportpage'))}">HWReports, CSV Reports, and Grade Summaries</a>
            </li>
            <li>
                <a href="{$this->core->buildUrl(array('component' => 'admin', 'page' => 'plagiarism'))}">Plagiarism Detection</a>
            </li>

HTML;

                    if ($this->core->getUser()->isDeveloper()) {
                        $return .= <<<HTML
            <li><a href="#" onClick="togglePageDetails();">Show Page Details</a></li>

HTML;
                    }
                    $return .= <<<HTML
        </ul>
    </div>
    
HTML;
                }
            }
        }


        $return .= <<<HTML
<div id="header">
    <a href="http://submitty.org" target=_blank><div id="logo-submitty"></div></a>
    <div id="header-text">
        <h2>
HTML;
        if ($this->core->userLoaded()) {
            $logout_link = $this->core->buildUrl(array('component' => 'authentication', 'page' => 'logout'));
            $my_preferred_name = $this->core->getUser()->getDisplayedFirstName();
            $return .= <<<HTML
            <span id="login">Hello <span id="login-id">{$my_preferred_name}</span></span> (<a id='logout' href='{$logout_link}'>Logout</a>)
HTML;
        }
        else {
            $return .= <<<HTML
            <span id="login-guest">Welcome to Submitty</span>
HTML;
        }
        $return .= <<<HTML
        </h2>
        <h2>
        {$breadcrumbs}
        </h2>
    </div>
</div>


HTML;
        return $return;
     }

    public function footer($runtime) {
        $return = <<<HTML
    <div id="push"></div>
</div>
<div id="footer">
    <span id="copyright">&copy; 2017 RPI | An <a href="https://rcos.io" target="_blank">RCOS project</a></span>|
    <span id="github">
        <a href="https://github.com/Submitty/Submitty" target="blank" title="Fork us on Github">
            <i class="fa fa-github fa-lg"></i>
        </a>
    </span>
</div>
HTML;
        if ($this->core->userLoaded() && $this->core->getUser()->isDeveloper()) {
            $return .= <<<HTML
<div id='page-info'>
    Total Queries: {$this->core->getCourseDB()->getQueryCount()}<br />
    Runtime: {$runtime}<br />
    Queries: <br /> {$this->core->getCourseDB()->getPrintQueries()}
</div>
HTML;
        }
        $return .= <<<HTML
</body>
</html>

HTML;

        return $return;
    }

    public function invalidPage($page) {
        return <<<HTML
<div class="box">
The page {$page} does not exist. Please try again.
</div>
HTML;

    }
}
