This is a content management system (CMS) written in node.js, with an admin interface written in PHP. However, contrary to most CMSes, pages aren't generated on the fly. Instead, there is a script which builds a bunch of static HTML files. This has a bunch of advantages over creating pages
 on the fly:
* **Performance** - The only thing your server has to do, is to serve plain .html files. Only when you tell it to re-run the script after making modifications does it do any real work. It generally takes a few tenths of a second to completely rebuild the website.
* **Stability** - Your website doesn't go down, even in the case of an extreme disaster. Say you accidentally delete your MySQL database, or the database host goes down, or file permissions mess up. Usually, this would take down the website. With this CMS however, all html files will just stay there, available for everyone to see. Your users won't notice a thing, while you can take all the time you need to properly fix whatever issue appeared. You can even re-run the site building script as much as you like while everything is down; it won't delete anything.
* **Beautiful URLs across webservers** - Usually, you would use something like .htaccess files to make URLs prettier, or just leave them ugly. However, with this CMS, URLs are of the style `yourdomain.com/your-post`, with no webserver specific code code whatsoever. This is because the public facing directory is just a bunch of folders, and an `index.html` file inside of each folder.

#### Installation

Make sure you have installed the required dependencies. Replace `apt-get install` with the appropriate command for your system.

* **node.js** - `sudo apt-get install nodejs`
* **PHP** - `sudo apt-get install php5`
* **PHP mysql** - `sudo apt-get install php5-mysql`
* **PHP json** - `sudo apt-get install php5-json`
* **MySQL** - `sudo apt-get install mysql`

Now that dependencies are fixed, here's what you need to do:

* Go to the desired directory: `cd /some/path/`
* Clone this project: `git clone https://github.com/mortie/jsSiteBuilder.git`
* Make sure that `jsSiteBuilder/public/` is accessible from the outside, but that `jsSiteBuilder/` itself **is not**. `jsSiteBuilder/public/` should be your web server's **root directory**. This can be achieved using Apache VirtualHosts and subdomains.
* Make sure that permissions are properly set: `sudo chown -R www-data` (replace www-data with your webserver's user)
* Now go to `yourdomain.com/admin` and press `Settings` in the sidebar. Fill in the appropriate fields, and press `Submit`.
* Now press the `update` button which appears on the top of your page. Your website should now be working, and you can start writing!

By default, jsSiteBuilder has a category named "pages". All entries in that category will end up in the menu bar. You probably want to create a new category, "posts", and create a page which lists all entries in the category "posts".

If anything is unclear, feel free to message me at [mail@mort.coffee](mailto:mail@mort.coffee).
