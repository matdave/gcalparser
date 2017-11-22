<?php

$values = array(
    'ignorefilename' => '0',
    'filename_prefix' => '',
    'guid_use' => '0',
);
switch ($options[xPDOTransport::PACKAGE_ACTION]) {
    case xPDOTransport::ACTION_INSTALL:
  
        $setting = $modx->getObject('modSystemSetting',array('key' => 'gcalparser.key'));
        if ($setting != null) { $values['key'] = $setting->get('value'); }
        unset($setting);
    break;
     case xPDOTransport::ACTION_UPGRADE:break;
    case xPDOTransport::ACTION_UNINSTALL: break;
}



$output = '<div class="installer">
    <h2>Configuration Options</h2>
    <table cellpadding="5" cellspacing="0" border="0" width="600">
        <tr>
            <td width="400">
                <label for="ignorefilename">
                    <strong>Google Calendar API Key</strong><br />
                    <small>Insert your Google Calendar v3 API key from <a href="https://console.developers.google.com/"> Google Console </a></small>
                </label>
            </td>
        </tr>
        <tr>
            <td>
                <input style="width:300px;" type="text" name="key" id="key" />
            </td>
        </tr>
    </table>
    <br />
    <small>This snippet requires the use of a Google API key.</small>  
    
    <ol>
        <li>Go to the <a href="https://console.developers.google.com/">Google Developer Console</a> and create a new project (it might take a second).</li>
        <li>Once in the project, go to APIs & auth > APIs on the sidebar.</li>
        <li>Find "Calendar API" in the list and turn it ON.</li>
        <li>On the sidebar, click APIs & auth > Credentials.</li>
        <li>In the "Public API access" section, click "Create new Key".</li>
        <li>Choose "Browser key".</li>
        <li>If you know what domains will host your calendar, enter them into the box. Otherwise, leave it blank. You can always change it later.</li>
        <li>Your new API key will appear. It might take second or two before it starts working.</li>
    </ol>
</div>';

return $output;