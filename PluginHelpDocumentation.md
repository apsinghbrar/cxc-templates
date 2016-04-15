<h1>Import/Export/Remove/Upload Templates as Files</h1>
<p>This plugin creates a new <strong>Templates</strong> tab under <strong>Extensions</strong>, enabling the trivial export of<strong> Forms</strong>, <strong>Pages</strong>, <strong>Plugins</strong>, <strong>Sections</strong>, and <strong>Style</strong> rules to a specified folder for convenient editing, and the subsequent import of new and updated files. Other features include removal of existing template directories, and a template upload option that will upload and import new templates with a single click.</p>

<h2><a>Plugin Requirements</a> &lt;/&gt;</h2>
<div>
<p>This plugin requires Textpattern <strong>4.3.0</strong> and above.</p>
<p>Regardless of where it&#8217;s been tested, this plugin messes around with your database.</p>
<p><em>Do not use it without backing up your database</em>.</p>
</div>

<h2><a>Setup Instructions</a> &lt;/&gt;</h2>
<div>
<p>By default, the plugin looks for directories named <strong>cache</strong> and <strong>tpl</strong> in the directory with images, rpc, sites, and textpattern directories. If the directories don&#8217;t exist, the plugin will attempt to create it the first time you export your templates. This creation will often fail, if that occurs, you&#8217;ll need to create the directories manually, and ensure that the web server has write access.</p>
<p>If your Textpattern root is located at <strong>/users/home/myuser/web/public/</strong>, something similar to the following commands could be used:</p>
<pre><code>cd /users/home/myuser/web/public/<br>
mkdir directory<br>
chmod 777 directory<br>
<br>
<br>
Unknown end tag for </code><br>
<br>
</pre>
<p>Just replace the word &#8217;directory&#8217; in the example above with the directory you need to create.</p>
<p><strong>Note:</strong> <em>if using an alternate template directory you will need to adjust accordingly.</em></p>
</div>

<h2><a>Usage Instructions</a> &lt;/&gt;</h2>
<div>
<p><strong>Import</strong> &#8211; select a template to import from the dropdown on the <strong>Templates</strong> tab and press <em>Go</em>. Before importing, the plugin will do an export of your currently installed templates to a folder called. If this is not your first install this may overwrite the current template backup located in <strong>preimport-data</strong>.</p>
<p><strong>Safe Mode</strong> &#8211; allows you to import a template with out overwriting any existing database entries. When this setting is enabled the plugin will skip importing the forms, pages, sections and styles if the database already contains an entry with the same name and only import new entries. This setting will usually require additional editing and is turned off by default.</p>
<p><strong>Export</strong> &#8211; is achieved by typing in an export name and pressing <em>Go</em>. Keep in mind naming an export the same as an exisiting template directory will overwrite the contents and that the assets folder is not created. This is done for two reasons ...</p>
<ol>
<blockquote><li>The system is completely unaware of which template you are using and there isn&#8217;t a meta file to tell the plugin what assets to clone or where to find them.</li>
<li>Even if the above was added to the plugin, cloned templates would need to edit the exported files to change resource directories used for css, images, and js.</li>
</ol>
<p>... that doesn&#8217;t mean it can&#8217;t be added, only that for now it doesn&#8217;t work that way.</p>
<p><strong>Delete</strong> &#8211; select a template to remove from the dropdown on the <strong>Templates</strong> tab and press <em>Go</em>. This will remove the template directory from your site, if this feature is unable to remove templates it is usually due to the server configuration of your host and removal of templates will need to be done manually<strong></strong>.</p>
<p><strong>Upload</strong> &#8211; use the browse button to locate a template zip file you have and press <em>Go</em>. Keep in mind uploading a templates zip file with a template of the same name as an exisiting folder will overwrite the contents of the existing folder. Uploaded templates are extracted to the templates directory and can then be imported using the Import feature of the plugin.</p>
<p><strong>Advanced</strong> &#8211; this area will allow you to do a webroot template installation and is not recommended unless instructed to by the template designer <strong></strong>or you know hwat you are doing. This feature can be used for support files or common files used by designers that must be in the webroot to function properly. When using this method for installation the uploaded zip file will be extracted directly into teh webroot and will overwrite existing files of the same name.</p>
</div></blockquote>

<h2><a>Designing Templates</a> &lt;/&gt;</h2>
<div>
<p>The following <a>file naming conventions</a> &lt;/&gt; are recommended to designers:</p>
<div>
<blockquote><p>Default pages, forms and styles should be prefaced with the design’s name.</p>
<ul>
<blockquote><li>Where possible the default page becomes THEME_NAME_default.page and so on ...</li>
<li><strong>Note:</strong> <em>all core code findings discovered by <a href=''>Bert Garcia</a> are still relevant</em>.</li>
</blockquote></ul>
<br />
<p>Templating file extensions, simple:</p>
<ul>
<blockquote><li>Forms → .form</li>
<li>Pages → .page</li>
<li>Plugins → .plugin</li>
<li>Sections → .section</li>
<li>Styles → .css  </li>
</blockquote></ul>
</div>
<p>This is <a title='Click to view example template directory structure'>the folder and subfolders structure</a> &lt;/&gt; used for template creation:</p>
<div>
<ul>
<blockquote><li>tpl<ul>
<blockquote><li>THEME_NAME<ul>
<blockquote><li>assets<ul>
<blockquote><li>css<ul>
<blockquote><li>additional.css</li>
</blockquote></ul></li>
<li>js<ul>
<blockquote><li>additional.js</li>
</blockquote></ul></li>
<li>additional.png</li>
</blockquote></ul></li>
<li>forms<ul>
<blockquote><li>default.article.form</li>
<li>...</li>
</blockquote></ul></li>
<li>pages<ul>
<blockquote><li>default.page</li>
<li>...</li>
</blockquote></ul></li>
<li>plugins<ul>
<blockquote><li>cxc_templates.plugin</li>
</blockquote></ul></li>
<li>sections<ul>
<blockquote><li>default.section</li>
</blockquote></ul></li>
<li>style<ul>
<blockquote><li>default.css</li>
</blockquote></ul></li>
</blockquote></ul></li>
<li>DESIGNER.txt</li>
<li>README.txt</li>
<li>preview.img</li>
</blockquote></blockquote><blockquote></ul></li>
</blockquote></ul>
</div>
<p>The default templates directory is the &quot;<strong>tpl</strong>&quot; directory, but in the past &quot;<i>templates&quot; was used and some templates may still require you to use it (or another) directory. I hope everyone will adopt the use of the &quot;</i><strong>tpl</strong>&quot; directory but I'm not forcing it on anyone. Please note, it is possible to have and use multiple template directories on a single site, but only templates existing in the directory set as $cxc_templates['base_dir'] will be used to display available templates.</p>
<p>You will need to replace &quot;<strong>THEME_NAME</strong>&quot; with the name of the template you are designing. The name of the template folder should be lower-cased and alpha numeric (can contain hyphens &quot;-&quot; and underscores &quot;<i>&quot;). Technically it does not have to be lower-cased but it is definitely the standard since asset links are case-sensitive.</p></i><p>The &quot;<strong>assets</strong>&quot; folder is not required nor are the sub-directories, I added them with organization in mind, it is simply a design choice. The concept is that all support files (css, images, js, and other files) could be placed in this folder (or another folder below the <strong>THEME_NAME</strong> directory) instead of requiring an advanced install into the webroot.</p>
<p>The &quot;<strong>forms</strong>&quot; folder contains all forms required by the template.</p>
<p>The &quot;<strong>pages</strong>&quot; folder contains all pages required by the template.</p>
<p>The &quot;<strong>plugins</strong>&quot; folder contains all plugins required by the template.</p>
<p>The &quot;<strong>sections</strong>&quot; folder contains all sections required by the template.</p>
<p>The &quot;<strong>styles</strong>&quot; folder contains all css style sheets required by the template.</p>
<p>The &quot;<strong>DESIGNER.txt</strong>&quot; (<em>optional</em>) file can be used by designers to link to their homepage or advertise additional products and services they offer. This file is not required and will not be displayed unless the user clicks on the &quot;<strong>Additional Information</strong> &lt;/&gt;&quot; area located below allow other results. The &quot;<strong>DESIGNER.txt</strong>&quot; file name is case sensitive and may contain simple html markup.</p>
<p>The &quot;<strong>README.txt</strong>&quot; (<em>optional</em>) file can be used by designers to display after installation instructions. This file is not required but if present, it will be displayed above all other information during install. The &quot;<strong>README.txt</strong>&quot; file name is case sensitive and may contain simple html markup.</p>
<p>The &quot;<strong>preview.img</strong>&quot; (<em>optional</em>) file is a image or logo that can be added to display the current template. This file is not required but if present, it will be displayed on the right hand side of the template manager after the first import. The file name and acceptable image formats are &quot;<strong>preview.gif</strong>&quot;, &quot;<strong>preview.jpg</strong>&quot; and &quot;<strong>preview.png</strong>&quot; and is case sensitive.</p>
<p><strong>Note:</strong> <em>designers are encouraged to include empty index.html files in all subdirectories of their template to help keep our Textpattern sites secure.</em></p>
</div></blockquote>

<h2>Plugin Credits</h2>

<p>Plugin code based on a modified version of mem_templates by <a href='http://manfre.net/'>Michael Manfre</a> that was released with one of <a href='http://thebombsite.com'>Stuart Butcher&#8217;s</a> TXP 4.3.0 templates, which is based off of hcg_templates by <a href='http://txptag.com/'>Bert Garcia</a>, which is based off of mcw_templates by <a href='http://mikewest.org/'>Mike West</a> with additional features introduced to an alternate hcg_templates provided by <a href='http://clueless.com.ar/'>Mariano Absatz</a>. </p>
<p>Without the mentioned plugins and contributions from <strong>all</strong> the above this plugin would not have been made possible. </p>
<p><strong>Note:</strong> <em>when </em><strong>&lt;/&gt;</strong><em> is encountered throughout the template manager it denotes information that can be expanded/collapsed to show/hide additional information.</em></p>