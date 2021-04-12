## edu.inventionlandinstitute.com

### Deploying changes
**Git repo:**  
`git clone <user>@23.253.218.7:/eduiland/git/eduiland.git`

There is a post-receive hook installed that copies the worktree of the following branches for each push:
```
dev -> /var/www/dev (edudev.inventionlandinstitute.com)
master -> /var/www/live (edu.inventionlandinstitute.com)
```
ssh user must be in group `staff` to allow post-receive hook copy changes.

### Backup repo
`git@bitbucket:flyingcork/edu2.inventionlandinstitute.com.git`

### Changes to js and scss files
The post-receive hook also checks for changes to `resources/js` and runs the production build if so, thus `npm install --no-optional` must be run whenever new packages are added to `package.json`.

**For development, npm build commands are:**
```
npm build
npm production
npm watch
npm browsersync (watch with browsersync)
```

Sass and Wordpress specific js changes are handled by gulp and not included in the deployment build script for now. The version of Laravel-Mix currently used doesn't work well with these files, specifically scss. 

**Gulp commands:**
```
gulp 
gulp [options --debugger --browsersync]
gulp build
gulp build --production

```

Run `gulp build --production` before committing changes to these files. 

### Edu requires Puppeteer for [Browsershot](https://github.com/spatie/browsershot)
Browsershot is used in ScreenshotAssignments job to create screen shots of custom assignments that are links to external websites.

Default Centos 7/RHEL install needs an additional package for Chromium use:
```
sudo npm install puppeteer -g
sudo yum install gtk3
```

***Setup working sandbox for Centos 7/RHEL:***   
Ensure that `npm install --no-optional` is run in the site's root directory since using a local install of puppeteer doesn't appear to work. 
```
cd /usr/lib/node_modules/puppeteer/.local-chromium/linux-XXXX/chrome-linux/
mv chrome_sandbox chrome-sandbox
chown root chrome-sandbox
chmod 4755 chrome-sandbox
```

***Setup working sandbox for macOS:***   
Should work after the `npm install` without any special handling.  

### These image optimizers are required by [Laravel Image Optimizer:](https://github.com/spatie/laravel-image-optimizer)

If none are installed, it will yield no errors, but optimization will not occur. 

**CentOS/RHEL**
```
sudo dnf install epel-release
sudo dnf install jpegoptim
sudo dnf install optipng
sudo dnf install pngquant
sudo dnf install gifsicle

// Currently not needed:
sudo dnf install libwebp-tools
sudo npm install -g svgo
```

**Ubuntu**
```
sudo apt-get install jpegoptim
sudo apt-get install optipng
sudo apt-get install pngquant
sudo apt-get install gifsicle

// Currently not needed:
sudo apt-get install webp
sudo npm install -g svgo 
```

**macOS**
```
brew install jpegoptim
brew install optipng
brew install pngquant
brew install gifsicle

// Currently not needed:
brew install webp
brew install svgo
```
