Github Auto Deploy

What it does

On every push to a specified in branch your server will get the latest version of a project.
Only modified files will be saved (added/modified/deleted) - not the whole project. So deploy is really quick.
On every deploy the changed files will be overwritten.
You can specify in a GAD class the array of files that should be excluded from a deploy. This means they will NOT be uploaded to your server even if pushed to a repository.
You can specify the array of folders, which will be excluded too. This means all files in that folders will be ignored and not synced while deploying.

How to use it

Working with the deployer is rather easy.
This is how I'm setting the deploy for own projects:
Create a public repo on Github and make a first push to some branch (for example, to master).
On your own site go to a folder you want your project files be placed in and put there a deploy.php script from this repository. This file should be reachable for Github pings.
Adjust settings (lines ~19-33) as you need. Make sure log.txt file is writeable (chmod and chown are correct).
Go to your repository admin area, Service Hooks page. In Available Service Hooks choose WebHooks URLs and insert there a URL to a deploy.php file. Save settings.
Do a commit and a push to that repository. Check upload folder - it should contain changed/added files, and removed files should be deleted too.