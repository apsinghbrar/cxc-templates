<h1>Import/Export/Remove/Download/Upload Templates as Files</h1>
<p>This plugin creates a new <strong>Templates</strong> tab under <strong>Extensions</strong>, enabling the trivial export of<strong> Forms</strong>, <strong>Pages</strong>, <strong>Plugins</strong>, <strong>Sections</strong>, and <strong>Style</strong> rules to a specified folder for convenient editing, and the subsequent import of new and updated files. Existing template directories, as well as, the <code>$cxc_templates['base_dir']</code> can be deleted. Please note, the <code>$cxc_templates['base_dir']</code> will be recreated when the plugin is next accessed. Other features include zip and download of template directories, and a template upload option that will upload and import new templates with a single click.</p>
![http://cxc-templates.googlecode.com/svn/wiki/assets/preview.jpg](http://cxc-templates.googlecode.com/svn/wiki/assets/preview.jpg)

http://forum.textpattern.com/viewtopic.php?id=35319
<h2><a>Plugin Requirements</a> </></h2>
<div>
<p>This plugin requires Textpattern <strong>4.3.0</strong> and above.</p>
<p>Regardless of where it's been tested, this plugin messes around with your database.</p>
<p><em>Do not use it without backing up your database</em>.</p>
</div>

<h2><a>Setup Instructions</a> </></h2>
<div>
<p>By default, the plugin looks for directories named <strong>cache</strong> and <strong>tpl</strong> in the directory with images, rpc, sites, and textpattern directories. If the directories don't exist, the plugin will attempt to create it the first time you export your templates. This creation will often fail, if that occurs, you'll need to create the directories manually, and ensure that the web server has write access.</p>
<p>If your Textpattern root is located at <strong>/users/home/myuser/web/public/</strong>, something similar to the following commands could be used:</p><pre>
cd /users/home/myuser/web/public/<br>
mkdir directory<br>
chmod 777 directory</pre>
<p>Just replace the word 'directory' in the example above with the directory you need to create.</p>
<p><strong>Note:</strong> <em>if using an alternate template directory you will need to adjust accordingly.</em></p>
</div>