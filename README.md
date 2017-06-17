This blog is a sample application written in the Hiraeth Nano-Framework and using some of the components which are available in the current ecosystem.  It is a router-less, controller-less, flat-file blogging platform that uses twig and public file entry points to serve up markdown based blog articles.

## Test Drive

In order to test drive this application, you will need the following:

- Git
- PHP >= 5.6
- NPM (Node)
- Composer

Begin by cloning and entering the repository.  You can replace the `<path>` below with any name of a directory you like:

```
git clone git@github.com:hiraeth-php/blog.git <path>
cd <path>
```

Following this, you can install the required system dependencies:

```
composer install
```

Next, install the required asset dependencies and compile the assets:

```
npm install
node_modules/gulp/bin/gulp.js build
```

Finally, run the Hiraeth server wrapper to execute PHP's built in server.  If you need to change the settings for this due to conflicts, you can do so in the `config/app.jin` file under the `[server]` section.

```
php bin/server
```

Now you should be able to visit [http://localhost:8080](http://localhost:8080) and begin using your blog.
