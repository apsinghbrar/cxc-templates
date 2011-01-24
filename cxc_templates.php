<?php

// This is a PLUGIN TEMPLATE.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Plugin names should start with a three letter prefix which is
// unique and reserved for each plugin author ("abc" is just an example).
// Uncomment and edit this line to override:
$plugin['name'] = 'cxc_templates';

// Allow raw HTML help, as opposed to Textile.
// 0 = Plugin help is in Textile format, no raw HTML allowed (default).
// 1 = Plugin help is in raw HTML.  Not recommended.
# $plugin['allow_html_help'] = 1;

$plugin['version'] = '0.0.6';
$plugin['author'] = '~cXc~';
$plugin['author_uri'] = 'http://gworldz.com';
$plugin['description'] = 'Template engine for TextPattern 4.3.0 with support for forms, pages, plugins, sections, styles and other template specific assets.';

// Plugin load order:
// The default value of 5 would fit most plugins, while for instance comment
// spam evaluators or URL redirectors would probably want to run earlier
// (1...4) to prepare the environment for everything else that follows.
// Values 6...9 should be considered for plugins which would work late.
// This order is user-overrideable.
$plugin['order'] = '5';

// Plugin 'type' defines where the plugin is loaded
// 0 = public       : only on the public side of the website (default)
// 1 = public+admin : on both the public and admin side
// 2 = library      : only when include_plugin() or require_plugin() is called
// 3 = admin        : only on the admin side
$plugin['type'] = '1';

// Plugin "flags" signal the presence of optional capabilities to the core plugin loader.
// Use an appropriately OR-ed combination of these flags.
// The four high-order bits 0xf000 are available for this plugin's private use
if (!defined('PLUGIN_HAS_PREFS')) define('PLUGIN_HAS_PREFS', 0x0001); // This plugin wants to receive "plugin_prefs.{$plugin['name']}" events
if (!defined('PLUGIN_LIFECYCLE_NOTIFY')) define('PLUGIN_LIFECYCLE_NOTIFY', 0x0002); // This plugin wants to receive "plugin_lifecycle.{$plugin['name']}" events

$plugin['flags'] = '2';

if (!defined('txpinterface'))
        @include_once('zem_tpl.php');

# --- BEGIN PLUGIN CODE ---
 
/*
	PUBLIC PLUGIN CONFIG
	-------------------------------------------------------------------------
*/
	$cxc_templates = array(
		'base_dir'			=>	'tpl',
		'cache_dir'			=>	'cache',

		'subdir_css'		=>	'style',
		'subdir_forms'		=>	'forms',
		'subdir_pages'		=>	'pages',
		'subdir_plugins'	=>	'plugins',
		'subdir_sections'	=>	'sections',

		'ext_css'			=>	'.css',
		'ext_forms'			=>	'.form',
		'ext_pages'			=>	'.page',
		'ext_plugins'		=>	'.plugin',
		'ext_section'		=>	'.section'
	);

/*
	PLUGIN CODE (no editing below this line, please)
	-------------------------------------------------------------------------
*/
	define('_CXC_TEMPLATES_IMPORT', 1);
	define('_CXC_TEMPLATES_EXPORT', 2);
	$GLOBALS['_CXC_TEMPLATES'] = $cxc_templates;

/*
	PLUGIN CODE::INSTANTIATION
	-------------------------------------------------------------
*/	
	if (@txpinterface == 'admin') {
		$import = 'cxc_templates';
		$import_tab = 'Templates';

		add_privs($import, '1,2');
		register_tab('extensions', $import, $import_tab);
		register_callback('cxc_templates', $import);
		register_callback('cxc_tpl_prep', 'plugin_lifecycle.cxc_templates');
	}

/*
	PLUGIN CODE::LIFECYCLE
	-------------------------------------------------------------
*/
	function cxc_tpl_prep($event, $step) {
		global $prefs;
		switch ($step) {
			case 'disabled':
				if (isset($prefs['cxc_tpl_current'])) {
					$prep = safe_delete('txp_prefs','name = "cxc_tpl_current"');
				}
				break;
			case 'deleted':
				if (isset($prefs['cxc_tpl_current'])) {
					$prep = safe_delete('txp_prefs','name = "cxc_tpl_current"');
				}
				break;
		}
	}

/*
	PLUGIN CODE::MAIN CALLBACK
	-------------------------------------------------------------
*/
	function cxc_templates($event, $step='') {
		$GLOBALS['prefs'] = get_prefs();
		global $prefs;
		$template = new cxc_template();

		pagetop('Process Templates', '');
		print '
		<style type="text/css">
			.cxc-tpl-boxedup { display: block; width: 450px; }
			.cxc-tpl-success { color: #009900; }
			.cxc-tpl-failure { color: #FF0000; }
			.cxc-tpl-capital { text-transform: capitalize; }
			.cxc-tpl-current { border: medium ridge;float: right; margin: 0 0 0 5px; padding: 1em 1em 0; text-align: center; width: 220px; }
			.cxc-tpl-preview { background: #fff; border: medium ridge; float: right; margin: 0 0 0 10px; padding: 1em 1em 0; text-align: center; width: 220px; }
			.cxc-tpl-default { max-height: 200px; overflow: hidden; }
			.cxc-tpl-padded { border: 1px solid; padding: 2em; }
			.cxc-tpl-smaller { font-size: 80%; }
		</style>

		<script type="text/javascript">
			$(document).ready(function()
			{
			  $(".cxc-tpl-slide-body").hide();
			  $(".cxc-tpl-slide-head").click(function()
			  {
				$(this).next(".cxc-tpl-slide-body").slideToggle(600);
			  });
			});
		</script>

		<table cellpadding="0" cellspacing="0" border="0" id="list" align="center">
			<tr>
				<td>
		';

		if (!isset($prefs['cxc_tpl_current']) && !set_pref('cxc_tpl_current', '', 'publish', 2) && !get_pref('cxc_tpl_current')) {
			print '
				<h1 class="cxc-tpl-failure">Plugin Preferences</h1>
				<ul class="results">
					<li><span class="cxc-tpl-failure">Database update failed</span> entry for current template will be unavailable.</li>
				</ul>
				<br />
			';
		}

		$theme_dir = $prefs['path_to_site']. DIRECTORY_SEPARATOR .$template->_config['base_dir'];
		$cache_dir = $prefs['path_to_site']. DIRECTORY_SEPARATOR .$template->_config['cache_dir'];
		if (is_dir($theme_dir) && is_dir($cache_dir)) {

			$theme_index = $theme_dir. DIRECTORY_SEPARATOR .'index.html';
			$cache_index = $cache_dir. DIRECTORY_SEPARATOR .'index.html';
			if (!file_exists($theme_index) || !file_exists($cache_index)) {
				$template->writeIndexFiles($theme_dir);
				$template->writeIndexFiles($cache_dir);
			}
							
			switch ($step) {
				case 'import':
					$import_full = ps('import_full');
					$template->import($import_full, ps('import_dir'));
					$template->writeIndexFiles($theme_dir. DIRECTORY_SEPARATOR .ps('import_dir'));
					print '
						<h2><a href="index.php?event=cxc_templates">&#8617; Click here to return to the template manager.</a></h2>
					';
					break;

				case 'export':
					$dir = ps('export_dir');

					$dir =  str_replace(
								array(' '),
								array('-'),
								$dir
							);
					$template->export($dir);
					$template->writeIndexFiles($theme_dir. DIRECTORY_SEPARATOR .ps('export_dir'));
					print '
						<h2><a href="index.php?event=cxc_templates">&#8617; Click here to return to the template manager.</a></h2>
					';
					break;

				case 'rusure':
					$dir = $theme_dir. DIRECTORY_SEPARATOR .ps('remove_dir');
					if (ps('remove_dir') != 'preimport-data') {
						$tpl_dir = $prefs['path_to_site']. DIRECTORY_SEPARATOR .$template->_config['base_dir']. DIRECTORY_SEPARATOR .ps('remove_dir');
						if (is_dir($tpl_dir)) {
							print '<div class="cxc-tpl-current">';
							$template->cxc_tpl_preview(ps('remove_dir'), $tpl_dir);
							print '</div>';
						}
					}

					print '
						<h1 class="cxc-tpl-failure">Delete Directory Confirmation</h1>
						<p>This will completely remove the "<strong>'.$dir.'</strong>" directory from your site, click "<strong>GO</strong>" to continue or use the link below to return to the template manager.</p>
						'.form(
							graf(''.
								hInput('remove_dir',ps('remove_dir')).' '.
								fInput('submit', 'go', 'Go', 'smallerbox').
								eInput('cxc_templates').sInput('remove')
							)
						).'
						<h2><a href="index.php?event=cxc_templates">&#8617; Click here to return to the template manager.</a></h2>
						';

					break;

				case 'remove':
					$dir = $theme_dir. DIRECTORY_SEPARATOR .ps('remove_dir');
					if (is_dir($dir)) {
						$objects = scandir($dir);
						foreach ($objects as $object) {
							if ($object != '.' && $object != '..') {
								if (is_dir($dir. DIRECTORY_SEPARATOR .$object)) {
									$template->removeDirectory($dir. DIRECTORY_SEPARATOR .$object);
								} else { 
									@unlink($dir. DIRECTORY_SEPARATOR .$object);
								}
							}
						}
						reset($objects);
						@rmdir($dir);
					}
					if (!is_dir($dir)){					
						print '
							<h1 class="cxc-tpl-success"><span class="cxc-tpl-capital">'.str_replace('_', ' ', ps('remove_dir')).'</span> Template Removed</h1>
							<p>The <span class="cxc-tpl-capital">'.str_replace('_', ' ', ps('remove_dir')).'</span> template directory has been removed from the "'.$template->_config['base_dir'].'" directory.</p>
						';
					} else {
						print '
							<h1 class="cxc-tpl-failure">Unable to Remove Template</h1>
							<p>The <span class="cxc-tpl-capital">'.str_replace('_', ' ', ps('remove_dir')).'</span> template directory was not removed, this might be due to the server configuration of your host and removal of templates may need to be done manually</p>
						';
					}
					print '
						<h2><a href="index.php?event=cxc_templates">&#8617; Click here to return to the template manager.</a></h2>
					';
					break;

				case 'importZip':
					$adv_live = ps('adv_live');
					$adv_root = ps('adv_root');
					$import_full = ps('import_full');
					$tpl_alist = scandir($theme_dir);
					$rel_temp_dir = '..'. DIRECTORY_SEPARATOR .$template->_config['cache_dir']. DIRECTORY_SEPARATOR . $_FILES['file']['name'];															
					move_uploaded_file($_FILES['file']['tmp_name'],$rel_temp_dir);
					$template->importZip($adv_live, $adv_root, $rel_temp_dir,$_FILES['file']['name']);
					$tpl_blist = scandir($theme_dir);
					$newtpl = array_merge(array_diff($tpl_blist,$tpl_alist));
					if ($adv_live){
						if ($newtpl != '' && count($newtpl) == 1) {
							$template->import($import_full, $newtpl[0]);
							$template->writeIndexFiles($theme_dir. DIRECTORY_SEPARATOR .$newtpl[0]);
						} else {
							print '
								<h1>Template Import: <span class="cxc-tpl-capital">'.str_replace('_', ' ', str_replace('.zip', '', $_FILES['file']['name'])).'</span></h1>
								<ul class="results">
									<li><span class="cxc-tpl-failure">Failed importing</span> the \'<span class="cxc-tpl-capital">'.str_replace(' ', '-', str_replace('.zip', '', $_FILES['file']['name'])).'</span>\' template files</li>
								</ul>
								<br />
								<p>It was not possible to import the uploaded template because the Zip file contained more than one template or it was already present in the templates directory.</p>
							';
						}
					}
					print '
						<h2><a href="index.php?event=cxc_templates">&#8617; Click here to return to the template manager.</a></h2>
					';
					break;

				case 'docs':
					$template->cxc_tpl_docs($prefs['cxc_tpl_current']);
					print '
						<h2><a href="index.php?event=cxc_templates">&#8617; Click here to return to the template manager.</a></h2>
					';
					break;

				case 'downzip':
					$zipdir	= ps('zip_dir');
					$stripz	= $prefs['path_to_site']. DIRECTORY_SEPARATOR .$template->_config['base_dir']. DIRECTORY_SEPARATOR;
					$template->writeIndexFiles($theme_dir. DIRECTORY_SEPARATOR .$zipdir);
					$template->cxc_tpl_downzip($theme_dir. DIRECTORY_SEPARATOR .$zipdir, $zipdir.'.zip', $stripz);
					print '
						<h2><a href="index.php?event=cxc_templates">&#8617; Click here to return to the template manager.</a></h2>
					';
					break;

				default:
					$importlist = $template->getTemplateList();

					if (!empty($prefs['cxc_tpl_current']) && $prefs['cxc_tpl_current'] != 'preimport-data') {
						$tpl_dir = $prefs['path_to_site']. DIRECTORY_SEPARATOR .$template->_config['base_dir']. DIRECTORY_SEPARATOR .$prefs['cxc_tpl_current'];
						if (is_dir($tpl_dir)) {
							print '<div class="cxc-tpl-current">';
							$template->cxc_tpl_current($tpl_dir);
							print '</div>';
						}
					}

					if (empty($importlist) || $importlist == '') {
						print '
							<h1>Import Template</h1>
							<p class="cxc-tpl-boxedup">There are no templates installed in the \'<strong>'.$template->_config['base_dir'].'</strong>\' directory, please clone your current template or upload a new one.</p>
							<span class="cxc-tpl-slide-head">
								'.form(
									graf(''.
										checkbox('show_alt', 'show_alt', '0', '', 'show_alt').' Use Alternate Template Directory (<em class="cxc-tpl-failure">not recommended</em>) &lt;/&gt;')
								).'
							</span>
							<div class="cxc-tpl-slide-body">
								<p class="cxc-tpl-boxedup">You will need to adjust the location to be used as the template directory by modifying <code>\$cxc_templates[\'base_dir\']</code> in the plugin\'s code. After you have adjusted the plugin\'s code it will try to automatically create the chosen directory for you if not already present in your webroot.</p>
								<p class="cxc-tpl-boxedup"><strong>Note:</strong> this could affect template assets and result in broken links to css, images, JS and other template files so it is recommended you use the default or select the directory used by the templates designer.</p>
							</div>
						';
					} else {
						print '
							<h1>Import Template</h1>
						'.form(
							graf('Which template would you like to import?'.' <br />'.
								selectInput('import_dir', $importlist, '', 1).' <br />'.
								checkbox('import_full', 'import_full', '0', '', 'import_full').' Use Import Safe Mode (<em class="cxc-tpl-failure">non-destructive</em>) <br />'.
								fInput('submit', 'go', 'Go', 'smallerbox').
								eInput('cxc_templates').sInput('import')
							)
						);
					}

					print '
						<h1>Export Template</h1>	
					'.form(
						graf('Choose a name for the exported template.'.' <br />'.
							fInput('text', 'export_dir', '').
							fInput('submit', 'go', 'Go', 'smallerbox').
							eInput('cxc_templates').sInput('export')
						)
					);

					if (!empty($importlist) && !$importlist == '') {
						print '
							<h1>Delete Template</h1>
						'.form(
							graf(''.
								selectInput('remove_dir', $importlist, '', 1).' '.
								fInput('submit', 'go', 'Go', 'smallerbox').
								eInput('cxc_templates').sInput('rusure')
							)
						);
					}

					if (!empty($importlist) && !$importlist == '' && class_exists('ZipArchive')) {
						print '
							<h1>Zip Project Folder</h1>
						'.form(
							graf(''.
								selectInput('zip_dir', $importlist, '', 1).' '.
								fInput('submit', 'go', 'Go', 'smallerbox').
								eInput('cxc_templates').sInput('downzip')
							)
						);
					}

					if (class_exists('ZipArchive')) {
						print '
							<h1>Upload Template</h1>
						'.form(
							graf('Please select the template you would like to upload.'.' <br />'.
								fInput('file', 'file', '', '', '', '',50,'','file').
								eInput('cxc_templates').sInput('importZip').' <br />'.
								checkbox('adv_live', 'adv_live', '1', '', 'adv_live').' Import Uploaded Template <br />'.
								checkbox('import_full', 'import_full', '0', '', 'import_full').' Use Import Safe Mode (<em class="cxc-tpl-failure">non-destructive</em>) <br />
								<span class="cxc-tpl-slide-head cxc-tpl-boxedup"><a id="upload-advanced-options">Advanced Options</a> &lt;/&gt;</span>
								<span class="cxc-tpl-slide-body cxc-tpl-boxedup">'.
									checkbox('adv_root', 'adv_root', '0', '', 'adv_root').' Web Root Installation (<em class="cxc-tpl-failure">not recommended</em>) <br />
									<strong>Note:</strong> <em>do not use unless required or you know what you are doing!</em>
								</span>'.
								fInput('submit', 'go', 'Go', 'smallerbox','','')
							), '', '', 'post', '', str_replace('\\', '', '\" enctype=\"multipart/form-data'), ''
						);
					}

					break;
			}
		} else {
			$error = false;
			if (!is_dir($theme_dir) && !mkdir($theme_dir, 0777)) { $error = true; }
			if (is_dir($theme_dir) && !is_writable($theme_dir) && !chmod($theme_dir, 0777)) { $error = true; }
			if (!is_dir($cache_dir) && !mkdir($cache_dir, 0777)) { $error = true; }
			if (is_dir($cache_dir) && !is_writable($cache_dir) && !chmod($cache_dir, 0777)) { $error = true; }
			if (!$error) { // no errors, let’s do your thing
				print '
					<h1 class="cxc-tpl-failure">Required Directories Created</h1>
					<p><a href="index.php?event=cxc_templates">Click here to reload this page</a> and display the template manager.</p>
				';
			} else {
				if (!is_dir($theme_dir) && !is_dir($cache_dir)) {
					print '
						<h1 class="cxc-tpl-failure">Required Directories Missing</h1>
						<p>This plugin requires the \'<strong>'.$template->_config['cache_dir'].'</strong>\' and \'<strong>'.$template->_config['base_dir'].'</strong>\' directories to be located in the webroot for it to function properly, either `<strong>'.$cache_dir.'</strong>\' or \'<strong>'.$theme_dir.'</strong>\' does not exist, and could not be automatically created. You could also adjust the plugin\'s directory by modifying <code>\$cxc_templates[\'base_dir\']</code> and/or <code>\$cxc_templates[\'cache_dir\']</code> in the plugin\'s code.</p>
						<p>Please create these directories manually using your FTP client, hosting control panel or by running something like  ...</p>
						<pre><code>    mkdir '.$cache_dir.'\n    chmod 777 '.$cache_dir.'</code></pre>
						<p>... and / or ...</p> 
						<pre><code>    mkdir '.$theme_dir.'\n    chmod 777 '.$theme_dir.'</code></pre>
						<p>After you have created the missing directories, return to (or reload) this page to display the template manager.</p>
						<p>For additional security you may also want to include empty index.html files or adjust your .htaccess for these directories.</p>
						<h2><a href="index.php?event=cxc_templates">&#8617; Click here to return to the template manager.</a></h2>
					';
				} else {
					if (!is_dir($theme_dir)){
						$missing_dir = $template->_config['base_dir'];
						$is_missing_dir = 'base_dir';
					}else{
						$missing_dir = $template->_config['cache_dir'];
						$is_missing_dir = 'cache_dir';
					}
					print '
						<h1 class="cxc-tpl-failure">Required Directories Missing</h1>
						<p>This plugin requires the \'<strong>'.$missing_dir.'</strong>\' directory to be located in the webroot for it to function properly, \'<strong>'.$missing_dir.'</strong>\' does not exist, and could not be automatically created. You could also adjust the plugin\'s directory by modifying <code>\$cxc_templates[\''.$is_missing_dir.'\']</code> in the plugin\'s code.</p>
						<p>Please create the directory manually using your FTP client, hosting control panel or by running something like  ...</p>
						<pre><code>    mkdir '.$missing_dir.'\n    chmod 777 '.$missing_dir.'</code></pre>
						<p>After you have created the missing directory, return to (or reload) this page to display the template manager.</p>
						<p>For additional security you may also want to include empty index.html files or adjust your .htaccess for the directory.</p>
						<h2><a href="index.php?event=cxc_templates">&#8617; Click here to return to the template manager.</a></h2>
					';
				}
			}
		}

		print "
				</td>
			</tr>
		</table>
		";
	}

	class cxc_template {
		function cxc_template() {
			global $prefs;
			global $_CXC_TEMPLATES;

			$this->_config = $_CXC_TEMPLATES;

		/*
			PRIVATE CONFIG
			------------------------------------------------------
		*/
			$this->_config['root_path']         =   $prefs['path_to_site'];
			$this->_config['full_base_path']    =   sprintf(
														'%s'. DIRECTORY_SEPARATOR .'%s',
														$this->_config['root_path'],
														$this->_config['base_dir']
													);

			$this->_config['error_template']    =   '
				<h1 class="cxc-tpl-failure">%s</h1>
				<p>%s</p>
			';

			$missing_dir_head   = 'Template Directory Missing';
			$missing_dir_text   = 'The template directory `<strong>%1\$s</strong>` does not exist, and could not be automatically created. Would you mind creating it yourself by running something like</p><pre><code>    mkdir %1\$s\n    chmod 777 %1\$s</code></pre><p>That should fix the issue. You could also adjust the plugin\'s directory by modifying <code>\$cxc_templates[\'base_dir\']</code> in the plugin\'s code.';
			$cant_write_head    = 'Template Directory Not Writable';
			$cant_write_text    = 'I can\'t seem to write to the template directory \'<strong>%1\$s</strong>\'.  Would you mind running something like</p><pre><code>    chmod 777 %1\$s</code></pre><p>to fix the problem?';
			$cant_read_head     = 'Template Directory Not Readable';
			$cant_read_text     = 'I can\'t seem to read from the template directory \'<strong>%1\$s</strong>\'.  Would you mind running something like</p><pre><code>    chmod 777 %%1\$s</code></pre><p>to fix the problem?';
			$wrong_file_head	= 'Unsupported File Type';
			$wrong_file_text	= 'The file is either corrupt or is an unsupported file type, only zip files are currently supported by this plugin.';

			$this->_config['error_missing_dir'] =   sprintf(
														$this->_config['error_template'],
														$missing_dir_head,
														$missing_dir_text
													);
			$this->_config['error_cant_write']  =   sprintf(
														$this->_config['error_template'],
														$cant_write_head,
														$cant_write_text
													);
			$this->_config['error_cant_read']   =   sprintf(
														$this->_config['error_template'],
														$cant_read_head,
														$cant_read_text
													);
			$this->_config['error_wrong_file']	=	sprintf(
														$this->_config['error_template'],
														$wrong_file_head,
														$wrong_file_text
													);
	
			$this->exportTypes = array(
				'css'		=>	array(
									'ext'       =>  $this->_config['ext_css'],
									'data'      =>  'css',
									'fields'    =>  'name, css',
									'nice_name' =>  'CSS Rules',
									'regex'     =>  '/(.+)'.$this->_config['ext_css'].'/',
									'sql'       =>  '`css` = "%s"',
									'subdir'    =>  $this->_config['subdir_css'],
									'table'     =>  'txp_css',
									'filter'    =>	'1=1'
								),
				'forms'		=>	array(
									'ext'       =>  $this->_config['ext_forms'],
									'data'      =>  'Form',
									'fields'    =>  'name, type, Form',
									'nice_name' =>  'Form Files',
									'regex'     =>  '/(.+)\.(.+)'.$this->_config['ext_forms'].'/',
									'sql'       =>  '`Form` = "%s", `type` = "%s"',
									'subdir'    =>  $this->_config['subdir_forms'],
									'table'     =>  'txp_form',
									'filter'    =>	'1=1'
								),
				'pages'		=>	array(
									'ext'       =>  $this->_config['ext_pages'],
									'data'      =>  'user_html',
									'fields'    =>  'name, user_html',
									'nice_name' =>  'Page Files',
									'regex'     =>  '/(.+)'.$this->_config['ext_pages'].'/',
									'sql'       =>  '`user_html` = "%s"',
									'subdir'    =>  $this->_config['subdir_pages'],
									'table'     =>  'txp_page',
									'filter'    =>	'1=1'
								),
				'plugins'	=>	array(
									'ext'       =>  $this->_config['ext_plugins'],
									'data'      =>  'code',
									'fields'    =>  'name, status, author, author_uri, version, description, help, code, code_restore, code_md5, type',
									'nice_name' =>  'Plugin Files',
									'regex'     =>  '/(.+)\.(.+)'.$this->_config['ext_plugins'].'/',
									'sql'       =>  '`status` = %d, `author` = "%s", `author_uri` = "%s", `version` = "%s", `description` = "%s", `help` = "%s", `code` = "%s", `code_restore` = "%s", `code_md5` = "%s", `type` = %d',
									'subdir'    =>  $this->_config['subdir_plugins'],
									'table'     =>  'txp_plugin',
									'filter'    =>	'`status` = 1'
								),
				'sections'	=>	array(
									'ext'       =>  $this->_config['ext_section'],
									'data'      =>  'section',
									'fields'    =>  'name, page, css, is_default, in_rss, on_frontpage, searchable, title',
									'nice_name' =>  'Section Parameters',
									'regex'     =>  '/(.+)'.$this->_config['ext_section'].'/',
									'sql'       =>  '`page` = "%s", `css` = "%s", `is_default` = "%d", `in_rss` = "%d", `on_frontpage` = "%d", `searchable` = "%d", `title` = "%s"',
									'subdir'    =>  $this->_config['subdir_sections'],
									'table'     =>  'txp_section',
									'filter'    =>	'1=1'
								)
			);
		}

		function checkdir($dir = '', $type = _CXC_TEMPLATES_EXPORT) {
			/*
				If $type == _EXPORT, then:
					1.  Check to see that /base/path/$dir exists, and is
						writable.  If not, create it.
					2.  Check to see that /base/path/$dir/subdir_* exist,
						and are writable.  If not, create them.

				If $type == _IMPORT, then:
					1.  Check to see that /base/path/$dir exists, and is readable.
					2.  Check to see that /base/path/$dir/subdir_* exist, and are readable.
			*/
			$dir =  sprintf(
						'%s'. DIRECTORY_SEPARATOR .'%s',
						$this->_config['full_base_path'],
						$dir
					);

			$tocheck =  array(
							$dir,
							$dir. DIRECTORY_SEPARATOR .$this->_config['subdir_css'],
							$dir. DIRECTORY_SEPARATOR .$this->_config['subdir_forms'],
							$dir. DIRECTORY_SEPARATOR .$this->_config['subdir_pages'],
							$dir. DIRECTORY_SEPARATOR .$this->_config['subdir_plugins'],
							$dir. DIRECTORY_SEPARATOR .$this->_config['subdir_sections']
						);
			foreach ($tocheck as $curDir) {
				switch ($type) {
					case _CXC_TEMPLATES_IMPORT:
						if (!is_dir($curDir) && !mkdir($curDir, 0777)) {
							echo sprintf($this->_config['error_missing_dir'], $curDir);
							return false;
						}
						if (is_dir($curDir) && !is_readable($curDir)) {
							echo sprintf($this->_config['error_cant_read'], $curDir);
							return false;
						}
						break;

					case _CXC_TEMPLATES_EXPORT:
						if (!is_dir($curDir) && !mkdir($curDir, 0777)) {
								echo sprintf($this->_config['error_missing_dir'], $curDir);
								return false;
						}
						if (is_dir($curDir) && !is_writable($curDir) && !chmod($theme_dir, 0777)) {
							echo sprintf($this->_config['error_cant_write'], $curDir);
							return false;
						}
						break;
				}
			}
			return true;
		}

		function checkdirImportZip() {

			$dir =  $this->_config['full_base_path'];

			if (!is_dir($dir)) {
			   echo sprintf($this->_config['error_missing_dir'], $dir);
			   return false;
			}
			if (!is_readable($dir)) {
			   echo sprintf($this->_config['error_cant_read'], $dir);
			   return false;
			}
			return true;
		}

		function checkFileTypeZip() {
			if ($_FILES['file']['type'] != 'application/zip') {
			   echo sprintf($this->_config['error_wrong_text']);
			   return false;
			}
			return true;
		}

		/*
			EXPORT FUNCTIONS
			----------------------------------------------------------
		*/
		function export($dir = '') {
			if (!$this->checkdir($dir, _CXC_TEMPLATES_EXPORT)) {
				return;
			}

			print '
				<h1 class="cxc-tpl-slide-head"><a id="exporting-details" title="Click here to open/close detailed list of export events.">Template Export: Current</a> &lt;/&gt;</h1>
				<div class="cxc-tpl-slide-body">
				<blockquote>
			';

			foreach ($this->exportTypes as $type => $config) {
				print '
					<h1>'.$config['nice_name'].'</h1>
					<ul class="results">
				';

				$rows = safe_rows($config['fields'], $config['table'], $config['filter']);

				foreach ($rows as $row) {
					$filename		=	sprintf(
											'%s'.  DIRECTORY_SEPARATOR  .'%s'.  DIRECTORY_SEPARATOR  .'%s'.  DIRECTORY_SEPARATOR  .'%s%s',
											$this->_config['full_base_path'],
											$dir,
											$config['subdir'],
											$row['name'] . (isset($row['type'])?'.'.$row['type']:''),
											$config['ext']
										);
					$nicefilename	=	sprintf(
											'...'.  DIRECTORY_SEPARATOR  .'%s'.  DIRECTORY_SEPARATOR  .'%s'.  DIRECTORY_SEPARATOR  .'%s%s',
											$dir,
											$config['subdir'],
											$row['name'] . (isset($row['type'])?'.'.$row['type']:''),
											$config['ext']
										);

					$data = '';

					if (isset($row['css'])) {
						$data = $row['css'];
					} elseif ($type=='plugins') {
						$data = base64_encode(serialize($row));
					} elseif ($config['subdir'] != 'sections') {
						$data = $row[$config['data']];
					}

					$f = @fopen($filename, 'w+');
					if ($f) {
						if ($config['subdir'] == 'sections'){
							$this->writeSectionFiles($f,$row);
						} else {
							fwrite($f,$data);
						}
						fclose($f);
						print '
						<li><span class="cxc-tpl-success">Successfully exported</span> '.$config['nice_name'].' \''.$row['name'].'\' to \''.$nicefilename.'\'</li>
						';
					} else {
						print '
						<li><span class="cxc-tpl-failure">Failed exporting</span> '.$config['nice_name'].' \''.$row['name'].'\' to \''.$nicefilename.'\'</li>
						';
					}
				}
				print '
					</ul>
				<br />
				';
			}
			print '
				</blockquote>
				</div>
			';
		}

		function writeSectionFiles($f,$row){
			if ($row['name'] == 'default'){
				$name = "name=default\n";
				$page = "page=".$row['page']."\n";
				$css = "css=".$row['css']."\n";
				$is_default = "is_default=0\n";
				$in_rss = "in_rss=1\n";
				$on_frontpage = "on_frontpage=1\n";
				$searchable = "searchable=1\n";
				$title = "title=default";
				fwrite($f,$name);
				fwrite($f,$page);
				fwrite($f,$css);
				fwrite($f,$is_default);
				fwrite($f,$in_rss);
				fwrite($f,$on_frontpage);
				fwrite($f,$searchable);
				fwrite($f,$title);
			}else{
				$name = "name=".$row['name']."\n";
				$page = "page=".$row['page']."\n";
				$css = "css=".$row['css']."\n";
				$is_default = "is_default=".$row['is_default']."\n";
				$in_rss = "in_rss=".$row['in_rss']."\n";
				$on_frontpage = "on_frontpage=".$row['on_frontpage']."\n";
				$searchable = "searchable=".$row['searchable']."\n";
				$title = "title=".$row['title'];
				fwrite($f,$name);
				fwrite($f,$page);
				fwrite($f,$css);
				fwrite($f,$is_default);
				fwrite($f,$in_rss);
				fwrite($f,$on_frontpage);
				fwrite($f,$searchable);
				fwrite($f,$title);
			}
		}

		/*
			IMPORT FUNCTIONS
			----------------------------------------------------------
		*/
		function getTemplateList() {
			if (!is_readable($this->_config['full_base_path'])) {
				return array();
			}
			$list = '';
			$dir = opendir($this->_config['full_base_path']);

			while(false !== ($filename = readdir($dir))) {
				if (
					is_dir(
						sprintf(
							'%s'.  DIRECTORY_SEPARATOR  .'%s',
							$this->_config['full_base_path'],
							$filename
						)
					) && $filename != '.' && $filename != '..'
				) {
					$list[$filename] = $filename;
				}
			}

			return $list;
		}

		function import($import_full, $dir) {
			if (!$this->checkdir($dir, _CXC_TEMPLATES_IMPORT)) {
				return;
			}
			$basedir =  sprintf(
							'%s'. DIRECTORY_SEPARATOR .'%s',
							$this->_config['full_base_path'],
							$dir
						);
			if (!set_pref('cxc_tpl_current', $dir, 'publish', 2)){
				print '
					<ul class="results">
						<li><span class="cxc-tpl-failure">Unable to update</span> entry for current template in the database, '.str_replace('_', ' ', $dir).' template information will be unavailable.</li>
					</ul>
					<br />
				';
			}
			if (file_exists($basedir. DIRECTORY_SEPARATOR .'README.txt')){
				print '
				<div>'.
					file_get_contents($basedir. DIRECTORY_SEPARATOR .'README.txt').'
				</div>
				<br />
				';
			}

			/*
				Auto export into `preimport-data`
			*/
			print '
				<h1>Template Processing</h1>
				<p>Your current template data will be available for re-import as `preimport-data` for future use</p>
				<p><strong>Note:</strong> <em>installing another template will overwrite the current `preimport-data` so rename the directory to something else after it is exported to preserve a more permanent backup.</em></p>
			';

			$this->export('preimport-data');

			print '
				<h1 class="cxc-tpl-slide-head"><a id="importing-details" title="Click here to open/close detailed list of import events.">Template Import: <span class="cxc-tpl-capital">'.str_replace('_', ' ', $dir).'</span></a> &lt;/&gt;</h1>
				<div class="cxc-tpl-slide-body">
				<blockquote>
			';

			foreach ($this->exportTypes as $type => $config) {
				print '
					<h1>'.$config['nice_name'].'</h1>
					<ul class="results">
				';

				$exportdir =    sprintf(
									'%s'.  DIRECTORY_SEPARATOR  .'%s',
									$basedir,
									$config['subdir']
								);

				$dir	= opendir($exportdir);
				while (false !== ($filename = readdir($dir))) {
					if (preg_match($config['regex'], $filename, $filedata)) {
						$templateName = addslashes($filedata[1]);
						$templateType = (isset($filedata[2]))?$filedata[2]:'';

						$f =    sprintf(
									'%s'.  DIRECTORY_SEPARATOR  .'%s',
									$exportdir,
									$filename
								);

						if ($data = file($f)) {
							if ($type == 'css') {
								$data = doSlash(implode('', $data));
							} elseif ($type == 'plugins') {
								$data = doSlash(unserialize(base64_decode(implode('', $data))));
							} elseif ($type != 'sections') {
								$data = addslashes(implode('', $data));
							}

							if ($type == 'plugins') {
								$rs = safe_row('version, status', $config['table'], 'name="'.$templateName.'"');
								$set = sprintf($config['sql'], $data['status'], $data['author'], $data['author_uri'], $data['version'], $data['description'], $data['help'], $data['code'], $data['code_restore'], $data['code_md5'], $data['type']);
								if ($rs) {
									if ($rs['status'] == 0 || strcasecmp($data['version'], $rs['version']) < 0) {
										$result = safe_update($config['table'], $set, '`name` = "'.$templateName.'"');
									} else {
										$result = 1;
									}
									$success = ($result)?1:0;
								} else {
									$result = safe_insert($config['table'], $set.', `name` = "'.$templateName.'"');
									$success = ($result)?1:0;
								}
							} elseif ($type == 'sections') {
								$rs = safe_row('page, css, is_default, in_rss, on_frontpage, searchable, title', $config['table'], 'name="'.$templateName.'"');
								$set = $this->parseSectionFile($config['sql'], $data, $filename, $config['ext']);
								if ($import_full == 0){
									if ($rs) {
										$result = safe_update($config['table'], $set, '`name` = "'.$templateName.'"');
									} else {
										$result = safe_insert($config['table'], $set.', `name` = "'.$templateName.'"');
									}
								} else {
									$result = 1;
								}
								$success = ($result)?1:0;
							} else {
								if (safe_field('name', $config['table'], 'name="'.$templateName.'"')) {
									if ($import_full == 0){
										$result = safe_update($config['table'], sprintf($config['sql'], $data, $templateType), '`name` = "'.$templateName.'"');
									} else {
										$result = 0;
									}
									$success = ($result)?1:0;
								} else {
									$result = safe_insert($config['table'], sprintf($config['sql'], $data, $templateType).', `name` = "'.$templateName.'"');
									$success = ($result)?1:0;
								}
							}
						}

						//$success = true;
						if ($success == 1) {
							print '
						<li><span class="cxc-tpl-success">Successfully imported</span> file "'.$filename.'"</li>
							';
						} else {
							if ($type == 'sections' && $import_full == 1){
								print '
						<li><span class="cxc-tpl-failure">Skipped importing</span> file "'.$filename.'"</li>
								';
							} elseif ($import_full == 1){
								print '
						<li><span class="cxc-tpl-failure">Skipped importing</span> "'.$filename.'", it was already present.</li>
								';
							} else {
								print '
						<li><span class="cxc-tpl-failure">Failed importing</span> file "'.$filename.'"</li>
								';
							}
						}
					}
				}

				print '
					</ul>
					<br />
				';
			}
			print '
				</blockquote>
				</div>
			';
			if (file_exists($basedir. DIRECTORY_SEPARATOR .'DESIGNER.txt')){
				print '
				<h1 class="cxc-tpl-slide-head" title="Click here to see additional information from the imported templates designer."><a id="additional-info">Additional Information</a> &lt;/&gt;</h1>
				<div class="cxc-tpl-slide-body">'.
					file_get_contents($basedir. DIRECTORY_SEPARATOR .'DESIGNER.txt').'
				</div>
				';
			}
		}

		function importZip($adv_live, $adv_root, $rel_temp_dir, $fileName) {
			global $prefs;

			if (!$this->checkdirImportZip()) {
					unlink($rel_temp_dir);
				return;
			}

			if (!$adv_root){
				$templates_base_dir = $this->_config['full_base_path'];
			} else {
				$templates_base_dir = $prefs['path_to_site'];
			}
			$full_temp_dir = $this->_config['root_path']. DIRECTORY_SEPARATOR ."textpattern". DIRECTORY_SEPARATOR .$rel_temp_dir;

			print '
				<ul class="results">
			';
			$zip = new ZipArchive;
			if ($zip->open($full_temp_dir) === TRUE) {
				$zip->extractTo($templates_base_dir);
				$zip->close();
				@unlink($full_temp_dir);
				print '<li><span class="cxc-tpl-success">Successfully uploaded</span> file "'.$fileName.'"</li>';
			} else {
				@unlink($full_temp_dir);
				print '<li><span class="cxc-tpl-failure">Failed uploading</span> file "'.$fileName.'"</li>';
			}
			print '
				</ul>
				<br />
			';
		}

		function parseSectionFile($sql,$data,$fname,$ext) {
			$sectionValues = array  (
					// if section title is not within the file, use the filename without the extension
					'title'			=> substr($fname,0,-strlen($ext)),
					'page'			=> 'default',
					'css'			=> 'default',
					'is_default'	=> 0,
					'in_rss'		=> 1,
					'on_frontpage'	=> 1,
					'searchable'	=> 1,
				);

			foreach($data as $line) {
				// Split the 'attribute = value' from within the section parameters file.
				// Ignore whitespace surrounding both the attribute and the value.
				// Limit the split to 2 values (just in case the right part contains another '='
				// which is very unlikely, anyway.
				$splitText = split('=',$line,2);
				$sectionParameter = trim($splitText[0]);
				$sectionValues[$sectionParameter] = trim($splitText[1]);
			}

			$sectionLine = sprintf($sql, $sectionValues['page'], $sectionValues['css'], $sectionValues['is_default'], $sectionValues['in_rss'], $sectionValues['on_frontpage'], $sectionValues['searchable'], $sectionValues['title']);
			return $sectionLine;
		}

		/*
			OTHER FUNCTIONS
			----------------------------------------------------------
		*/

		function cxc_tpl_current($tpl_dir){
			global $prefs;

			$tpl_pre = $tpl_dir. DIRECTORY_SEPARATOR .'preview';
			$tpl_alt = str_replace('_',' ',$prefs['cxc_tpl_current']).' Template Preview';
			$readme = $tpl_dir. DIRECTORY_SEPARATOR .'README.txt';
			$design = $tpl_dir. DIRECTORY_SEPARATOR .'DESIGNER.txt';
			
			if (!empty($prefs['cxc_tpl_current']) && is_dir($tpl_dir)){

				if ($img_size = @getimagesize($tpl_pre.'.gif')) {
					$tpl_preview = '../'.$this->_config['base_dir'].'/'.$prefs['cxc_tpl_current'].'/preview.gif';
				} elseif ($img_size = @getimagesize($tpl_pre.'.jpg')) {
					$tpl_preview = '../'.$this->_config['base_dir'].'/'.$prefs['cxc_tpl_current'].'/preview.jpg';
				} elseif ($img_size = @getimagesize($tpl_pre.'.png')) {
					$tpl_preview = '../'.$this->_config['base_dir'].'/'.$prefs['cxc_tpl_current'].'/preview.png';
				}
	
				print '<h2 class="cxc-tpl-capital">'.str_replace('_',' ',$prefs['cxc_tpl_current']).' Template</h2>';
				if (isset($tpl_preview)) {
					print '<p class="cxc-tpl-default"><img src="'.$tpl_preview.'" width="200px" height="auto" alt="'.$tpl_alt.'" /></p>';
				} else {
					print '<p class="cxc-tpl-padded">No Preview Image Available</p>';
				}
				if (file_exists($readme) || file_exists($design)) {
					print form(''.
							graf(
								fInput('submit', 'go', 'Template Documentation', 'smallerbox').
								eInput('cxc_templates').sInput('docs')
							)
					);
				} else {
					print '<p class="cxc-tpl-smaller">(<em><span class="cxc-tpl-capital">'.str_replace('_',' ',$prefs['cxc_tpl_current']).'</span> was the last template imported</em>)</p>';					
				}
			}
		}

		function cxc_tpl_preview($dir, $tpl_dir){
			global $prefs;

			$tpl_pre = $tpl_dir. DIRECTORY_SEPARATOR .'preview';
			$tpl_alt = str_replace('_',' ',$dir).' Template Preview';
			if (is_dir($tpl_dir)){

				if ($img_size = @getimagesize($tpl_pre.'.gif')) {
					$tpl_preview = '../'.$this->_config['base_dir'].'/'.$dir.'/preview.gif';
				} elseif ($img_size = @getimagesize($tpl_pre.'.jpg')) {
					$tpl_preview = '../'.$this->_config['base_dir'].'/'.$dir.'/preview.jpg';
				} elseif ($img_size = @getimagesize($tpl_pre.'.png')) {
					$tpl_preview = '../'.$this->_config['base_dir'].'/'.$dir.'/preview.png';
				}
	
				if ($dir == '') {
					print '
					<h2>Top Level "<strong>'.str_replace('_',' ',$this->_config['base_dir']).'</strong>" Directory</h2>
					<p class="cxc-tpl-smaller"><strong>Note:</strong> <em class="cxc-tpl-failure">this will remove all templates.</em></p>				
					';
				} else {
					print '<h2 class="cxc-tpl-capital">'.str_replace('_',' ',$dir).' Template</h2>';
				}
				if (isset($tpl_preview)) {
					print '<p class="cxc-tpl-default"><img src="'.$tpl_preview.'" width="200px" height="auto" alt="'.$tpl_alt.'" /></p>';
				} else {
					print '<p class="cxc-tpl-padded">No Preview Image Available</p>';
				}
			}
		}

		function cxc_tpl_docs($tpl_dir){
			global $prefs;

			$basedir =  sprintf(
							'%s'. DIRECTORY_SEPARATOR .'%s',
							$this->_config['full_base_path'],
							$tpl_dir
						);
			$tpl_dir = $prefs['path_to_site']. DIRECTORY_SEPARATOR .$this->_config['base_dir']. DIRECTORY_SEPARATOR .$prefs['cxc_tpl_current'];
			$tpl_pre = $tpl_dir. DIRECTORY_SEPARATOR .'preview';
			$tpl_alt = str_replace('_',' ',$prefs['cxc_tpl_current']).' Template Preview';
			$readme = $basedir. DIRECTORY_SEPARATOR .'README.txt';
			$design = $basedir. DIRECTORY_SEPARATOR .'DESIGNER.txt';

			if (!empty($prefs['cxc_tpl_current']) && $prefs['cxc_tpl_current'] != 'preimport-data') {
				print '
					<div class="cxc-tpl-preview">
				';

				if ($img_size = @getimagesize($tpl_pre.'.gif')) {
					$tpl_preview = '../'.$this->_config['base_dir'].'/'.$prefs['cxc_tpl_current'].'/preview.gif';
				} elseif ($img_size = @getimagesize($tpl_pre.'.jpg')) {
					$tpl_preview = '../'.$this->_config['base_dir'].'/'.$prefs['cxc_tpl_current'].'/preview.jpg';
				} elseif ($img_size = @getimagesize($tpl_pre.'.png')) {
					$tpl_preview = '../'.$this->_config['base_dir'].'/'.$prefs['cxc_tpl_current'].'/preview.png';
				}
	
				print '<h2 class="cxc-tpl-capital">Template Preview Image</h2>';
				if (isset($tpl_preview)) {
					print '<p><img src="'.$tpl_preview.'" width="200px" height="auto" alt="'.$tpl_alt.'" /></p>';
				} else {
					print '<p class="cxc-tpl-padded">No Preview Image Available</p>';
				}
				print '
					<br />
					</div>
				';
			}
			if (file_exists($readme)){
				print '
					<div>'.
						file_get_contents($readme).'
					</div>
					<br />
				';
			}
			if (file_exists($design)){
				print '
					<h1>Additional Information</h1>
					<div>'.
						file_get_contents($design).'
					</div>
					<br />
				';
			}			
		}

		function cxc_tpl_downzip($folder, $to='archive.zip', $basedir) {
			$zip = new ZipArchive();
			if ($zip->open($to, ZIPARCHIVE::CREATE)) {
				$found = array(rtrim($folder,DIRECTORY_SEPARATOR.'\/'));
				while ($path = each($found)) {
					$path = current($path);
					if (is_dir($path)) {
						//$zip->addEmptyDir(substr($path, strlen($basedir)));
						foreach (scandir($path) as $subpath) {
							if ($subpath=='.'||$subpath=='..'||substr($subpath,-2)==DIRECTORY_SEPARATOR.'.'||substr($subpath,-3)==DIRECTORY_SEPARATOR.'..') continue;
							$found[] = $path.DIRECTORY_SEPARATOR.$subpath;
						}
					} else {
						$zip->addFile($path, substr($path, strlen($basedir)));
					}
				}
				if ($zip->close()) {
					header ("Content-Type: application/zip");
					header ("Content-Disposition: attachment; filename=$to");
					header ("Pragma: no-cache");
					header ("Expires: 0");
					if (!readfile($to)){
						print 'Error, there was a problem creating the zip file for the template directory.';
					}
					if (!unlink($to)) {
						print 'Error, there was a problem removing the zip file after the download.';
					}
		
					return true;
				} else {
					print 'Error, could not finalise the archive.';
				}
			} else {
				print 'Error, could not create a zipfile at '.$to;
			}
			return false;
		}

		function removeDirectory($dir) {
			if (is_dir($dir)) {
				$objects = scandir($dir);
				foreach ($objects as $object) {
					if ($object != '.' && $object != '..') {
						if (is_dir($dir. DIRECTORY_SEPARATOR .$object)) {
							$this->removeDirectory($dir.'/'.$object);
						} else { 
							@unlink($dir. DIRECTORY_SEPARATOR .$object);
						}
					}
				}
				reset($objects);
				@rmdir($dir);
			}
		}

		function writeIndexFiles($dir) {
			if (is_dir($dir)) {
				if (!file_exists($dir. DIRECTORY_SEPARATOR .'index.html')) {
					$f = @fopen($dir. DIRECTORY_SEPARATOR .'index.html', 'x+');
					if ($f) {
						fwrite($f,'<html><body bgcolor="#FFFFFF"></body></html>');
						fclose($f);
					}
				}
				$objects = scandir($dir);
				foreach ($objects as $object) {
					if ($object != '.' && $object != '..') {
						if (is_dir($dir. DIRECTORY_SEPARATOR .$object)) {
							$this->writeIndexFiles($dir.'/'.$object);
						}
					}
				}
				reset($objects);
			}
		}
	} 
# --- END PLUGIN CODE ---
if (0) {
?>
<!--
# --- BEGIN PLUGIN HELP ---
<h1>Import/Export/Remove/Download/Upload Templates as Files</h1>
<p>This plugin creates a new <strong>Templates</strong> tab under <strong>Extensions</strong>, enabling the trivial export of<strong> Forms</strong>, <strong>Pages</strong>, <strong>Plugins</strong>, <strong>Sections</strong>, and <strong>Style</strong> rules to a specified folder for convenient editing, and the subsequent import of new and updated files. Existing template directories, as well as, the $cxc_templates[&#8217;base_dir&#8217;] can be deleted. Please note, the $cxc_templates[&#8217;base_dir&#8217;] will be recreated when the plugin is next accessed. Other features include zip and download of template directories, and a template upload option that will upload and import new templates with a single click.</p>

<h2 class="cxc-tpl-slide-head"><a id="plugin-requirements">Plugin Requirements</a> &lt;/&gt;</h2>
<div class="cxc-tpl-slide-body">
<p>This plugin requires Textpattern <strong>4.3.0</strong> and above.</p>
<p>Regardless of where it&#8217;s been tested, this plugin messes around with your database.</p>
<p><em>Do not use it without backing up your database</em>.</p>
</div>

<h2 class="cxc-tpl-slide-head"><a id="setup-instructions">Setup Instructions</a> &lt;/&gt;</h2>
<div class="cxc-tpl-slide-body">
<p>By default, the plugin looks for directories named <strong>cache</strong> and <strong>tpl</strong> in the directory with images, rpc, sites, and textpattern directories. If the directories don&#8217;t exist, the plugin will attempt to create it the first time you export your templates. This creation will often fail, if that occurs, you&#8217;ll need to create the directories manually, and ensure that the web server has write access.</p>
<p>If your Textpattern root is located at <strong>/users/home/myuser/web/public/</strong>, something similar to the following commands could be used:</p>
<pre><code>cd /users/home/myuser/web/public/
mkdir directory
chmod 777 directory
</code></pre>
<p>Just replace the word &#8217;directory&#8217; in the example above with the directory you need to create.</p>
<p><strong>Note:</strong> <em>if using an alternate template directory you will need to adjust accordingly.</em></p>
</div>

<h2 class="cxc-tpl-slide-head"><a id="usage-instructions">Usage Instructions</a> &lt;/&gt;</h2>
<div class="cxc-tpl-slide-body">
<p><strong>Import Template</strong>&#8211; select a template to import from the dropdown on the <strong>Templates</strong> tab and press <em>Go</em>. Before importing, the plugin will do an export of your currently installed templates to a folder called. If this is not your first install this may overwrite the current template backup located in <strong>preimport-data</strong>.</p>
<p><strong>Safe Mode</strong> &#8211; allows you to import a template with out overwriting any existing database entries. When this setting is enabled the plugin will skip importing the forms, pages, sections and styles if the database already contains an entry with the same name and only import new entries. This setting will usually require additional editing and is turned off by default.</p>
<p><strong>Export Template</strong> &#8211; is achieved by typing in an export name and pressing <em>Go</em>. Keep in mind naming an export the same as an exisiting template directory will overwrite the contents and that the assets folder is not created. This is done for two reasons ...</p>
<ol>
    <li>The system is completely unaware of which template you are using and there isn&#8217;t a meta file to tell the plugin what assets to clone or where to find them.</li>
    <li>Even if the above was added to the plugin, cloned templates would need to edit the exported files to change resource directories used for css, images, and js.</li>
</ol>
<p>... that doesn&#8217;t mean it can&#8217;t be added, only that for now it doesn&#8217;t work that way.</p>
<p><strong>Delete Template</strong> &#8211; select a template to remove from the dropdown on the <strong>Templates</strong> tab and press <em>Go</em>. Follow the instructions on the <strong>Delete Directory Confirmation</strong> page to remove the selected template directory from your site, if no template is selected from the dropdown list the entire templates directory will be removed. If this feature is unable to remove templates it is usually due to the server configuration of your host and removal of templates will need to be done manually.</p>
<p><strong>Zip Project Folder</strong> &#8211; select a template directory to zip from the dropdown on the <strong>Templates</strong> tab and press <em>Go</em>. This will zip the entire template directory and force download of the template, once downloaded you can extract the contents and remove files that are unecessary. This is mostly a feature to help designers share their templates, downloaded zip files must be extracted and rezipped before they can be used with the upload feature. If this feature is unable to zip the template directory it is usually due to the server configuration of your host and downlpad of templates will not be possible<strong></strong>.</p>
<p><strong>Upload Template</strong> &#8211; use the browse button to locate a template zip file you have and press <em>Go</em>. Keep in mind uploading a templates zip file with a template of the same name as an exisiting folder will overwrite the contents of the existing folder. Uploaded templates are extracted to the templates directory and can then be imported using the Import feature of the plugin.</p>
<p><strong>Advanced</strong> &#8211; this area will allow you to do a webroot template installation and is not recommended unless instructed to by the template designer <strong></strong>or you know hwat you are doing. This feature can be used for support files or common files used by designers that must be in the webroot to function properly. When using this method for installation the uploaded zip file will be extracted directly into teh webroot and will overwrite existing files of the same name.</p>
</div>

<h2 class="cxc-tpl-slide-head"><a id="designing-templates">Designing Templates</a> &lt;/&gt;</h2>
<div class="cxc-tpl-slide-body">
<p class="cxc-tpl-slide-head">The following <a id="file-naming-conventions">file naming conventions</a> &lt;/&gt; are recommended to designers:</p>
<div class="cxc-tpl-slide-body preview">
    <p>Default pages, forms and styles should be prefaced with the design’s name.</p>
    <ul>
        <li>Where possible the default page becomes THEME_NAME_default.page and so on ...</li>
        <li><strong>Note:</strong> <em>all core code findings discovered by <a href="">Bert Garcia</a> are still relevant</em>.</li>
    </ul>
    <br />
    <p>Templating file extensions, simple:</p>
    <ul>
        <li>Forms → .form</li>
        <li>Pages → .page</li>
        <li>Plugins → .plugin</li>
        <li>Sections → .section</li>
        <li>Styles → .css  </li>
    </ul>
</div>
<p class="cxc-tpl-slide-head">This is <a id="suggested-folder-structure" title="Click to view example template directory structure">the folder and subfolders structure</a> &lt;/&gt; used for template creation:</p>
<div class="cxc-tpl-slide-body preview">
    <ul>
        <li>tpl<ul>
            <li>THEME_NAME<ul>
                <li>assets<ul>
                    <li>css<ul>
                        <li>additional.css</li>
                    </ul></li>
                    <li>js<ul>
                        <li>additional.js</li>
                    </ul></li>
                    <li>additional.png</li>
                </ul></li>
                <li>forms<ul>
                    <li>default.article.form</li>
                    <li>...</li>
                </ul></li>
                <li>pages<ul>
                    <li>default.page</li>
                    <li>...</li>
                </ul></li>
                <li>plugins<ul>
                    <li>cxc_templates.plugin</li>
                </ul></li>
                <li>sections<ul>
                    <li>default.section</li>
                </ul></li>
                <li>style<ul>
                    <li>default.css</li>
                </ul></li>
            </ul></li>
            <li>DESIGNER.txt</li>
            <li>README.txt</li>
            <li>preview.img</li>
      </ul></li>
    </ul>
</div>
<p>The default templates directory is the &quot;<strong>tpl</strong>&quot; directory, but in the past &quot;_templates&quot; was used and some templates may still require you to use it (or another) directory. I hope everyone will adopt the use of the &quot;<strong>tpl</strong>&quot; directory but I&#8217;m not forcing it on anyone. Please note, it is possible to have and use multiple template directories on a single site, but only templates existing in the directory set as $cxc_templates[&#8217;base_dir&#8217;] will be used to display available templates.</p>
<p>You will need to replace &quot;<strong>THEME_NAME</strong>&quot; with the name of the template you are designing. The name of the template folder should be lower-cased and alpha numeric (can contain hyphens &quot;-&quot; and underscores &quot;_&quot;). Technically it does not have to be lower-cased but it is definitely the standard since asset links are case-sensitive.</p>
<p>The &quot;<strong>assets</strong>&quot; folder is not required nor are the sub-directories, I added them with organization in mind, it is simply a design choice. The concept is that all support files (css, images, js, and other files) could be placed in this folder (or another folder below the <strong>THEME_NAME</strong> directory) instead of requiring an advanced install into the webroot.</p>
<p>The &quot;<strong>forms</strong>&quot; folder contains all forms included with the template, form files not required or part of the template should be removed before sharing your design publicly.</p>
<p>The &quot;<strong>pages</strong>&quot; folder contains all pages included with the template, page files not required or part of the template should be removed before sharing your design publicly.</p>
<p>The &quot;<strong>plugins</strong>&quot; folder contains all plugins included with the template, plugin files not required or part of the template should be removed before sharing your design publicly.</p>
<p>The &quot;<strong>sections</strong>&quot; folder contains all sections included with the template, section files not required or part of the template should be removed before sharing your design publicly.</p>
<p>The &quot;<strong>styles</strong>&quot; folder contains all css style sheets included with the template, css files not required or part of the template should be removed before sharing your design publicly.</p>
<p>The &quot;<strong>DESIGNER.txt</strong>&quot; (<em>optional</em>) file must be located in the templates root directory and can be used by designers to link to their homepage or advertise additional products and services they offer. This file is not required and will not be displayed unless the user clicks on the &quot;<strong>Additional Information</strong> &lt;/&gt;&quot; area located below allow other results. The &quot;<strong>DESIGNER.txt</strong>&quot; file name is case sensitive and may contain simple html markup.</p>
<p>The &quot;<strong>README.txt</strong>&quot; (<em>optional</em>) file must be located in the templates root directory and can be used by designers to display after installation instructions. This file is not required but if present, it will be displayed above all other information during install. The &quot;<strong>README.txt</strong>&quot; file name is case sensitive and may contain simple html markup.</p>
<p>The &quot;<strong>preview.img</strong>&quot; (<em>optional</em>) file must be located in the templates root directory and is an image or logo that can be added to display the template previews. This file is not required but if present, it will be displayed on the right hand side of the template manager after the first import. The file name and acceptable image formats are &quot;<strong>preview.gif</strong>&quot;, &quot;<strong>preview.jpg</strong>&quot; and &quot;<strong>preview.png</strong>&quot; and is case sensitive.</p>
<p><strong>Note:</strong> <em>designers are encouraged to include empty index.html files in all subdirectories of their template to help keep our Textpattern sites secure.</em></p>
</div>

<h2>Plugin Credits</h2>

<p>Plugin code based on a modified version of mem_templates by <a href="http://manfre.net/">Michael Manfre</a> that was released with one of <a href="http://protextthemes.com">Stuart Butcher&#8217;s</a> TXP 4.3.0 templates, which is based off of hcg_templates by <a href="http://txptag.com/">Bert Garcia</a>, which is based off of mcw_templates by <a href="http://mikewest.org/" rel="nofollow">Mike West</a> with additional features introduced to an alternate hcg_templates provided by <a href="http://clueless.com.ar/">Mariano Absatz</a>. </p>
<p>Without code contributions, help and tutoring from <a href="http://zegnat.com/">Martijn van der Ven</a>, as well as, the mentioned plugins and contributions from <strong>all</strong> the above this plugin would not have been made possible. </p>
<p><strong>Note:</strong> <em>when </em><strong>&lt;/&gt;</strong><em> is encountered throughout the template manager it denotes information that can be expanded/collapsed to show/hide additional information.</em></p>

<script type="text/javascript">
	$(document).ready(function()
	{
	  $(".cxc-tpl-slide-body").hide();
	  $(".cxc-tpl-slide-head").click(function()
	  {
		$(this).next(".cxc-tpl-slide-body").slideToggle(600);
	  });
	});
</script>
# --- END PLUGIN HELP ---
-->
<?php
}
?>