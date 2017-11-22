<?php
/**
 * SanitizeFilename transport snippet
 * Copyright 2011 Benjamin Vauchel <contact@omycode.fr>
 * @author Benjamin Vauchel <contact@omycode.fr>
 * 12/15/11
 *
 * SanitizeFilename is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the Free
 * Software Foundation; either version 2 of the License, or (at your option) any
 * later version.
 *
 * SanitizeFilename is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * SanitizeFilename; if not, write to the Free Software Foundation, Inc., 59 Temple
 * Place, Suite 330, Boston, MA 02111-1307 USA
 *
 * @package sanitizefilename
 */
/**
 * Description:  Array of plugin objects for SanitizeFilename package
 * @package sanitizefilename
 * @subpackage build
 */

if (! function_exists('getSnippetContent')) {
    function getSnippetContent($filename) {
        $o = file_get_contents($filename);
        $o = str_replace('<?php','',$o);
        $o = str_replace('?>','',$o);
        $o = trim($o);
        return $o;
    }
}

$snippet = array();

/* create the plugin object */
$snippet[0] = $modx->newObject('modSnippet');
$snippet[0]->set('id',1);
$snippet[0]->set('name', PKG_NAME);
$snippet[0]->set('description', PKG_NAME . ' ' . PKG_VERSION . '-' . PKG_RELEASE . ' snippet for MODX Revolution');
$snippet[0]->set('snippet', getSnippetContent($sources['source_core'] . '/elements/snippet/gcalparser.plugin.php'));
$snippet[0]->set('category', 0);
 

return $snippet;