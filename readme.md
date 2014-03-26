This is a content management system (CMS) written in node.js, with an admin interface written in PHP. However, contrary to most CMSes, pages aren't generated on the fly. Instead, there is a script which builds a bunch of static HTML files. This has a bunch of advantages over creating pages
 on the fly:
* **Performance** - The only thing your server has to do, is to serve plain .html files. Only when you tell it to re-run the script after making modifications does it do any real work. It generally takes a few tenths of a second to completely rebuild the website.
* **Stability** - Your website doesn't go down, even in the case of an extreme disaster. Say you accidentally delete your MySQL database, or the database host goes down, or file permissions mess up. Usually, this would take down the website. With this CMS however, all html files will just stay there, available for everyone to see. Your users won't notice a thing, while you can take all the time you need to properly fix whatever issue appeared. You can even re-run the site building script as much as you like while everything is down; it won't delete anything.
* **Beautiful URLs across webservers** - Usually, you would use something like .htaccess files to make URLs prettier, or just leave them ugly. However, with this CMS, URLs are of the style `yourdomain.com/your-post`, with no webserver specific code code whatsoever. This is because the public facing directory is just a bunch of folders, and an `index.html` file inside of each folder.

**Dependencies:**
* **PHP** - `sudo apt-get install php5`
* **node.js** - `sudo apt-get install nodejs`

**Possible dependencies:**
* **PHP mysql** - `sudo apt-get install php5-mysql`
* **PHP json** - `sudo apt-get install php5-json` - Some Linux distros don't include PHP's JSON functions, due to a license issue.
