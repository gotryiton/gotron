![Gotron](http://assets.gotryiton.com/img/gotron/ff91430392/logo-m.png)

Gotron is a web application framework for PHP developers.

Gotron powers the [Go Try It On](http://www.gotryiton.com) website and the API for the [Go Try It On iPhone application](https://itunes.apple.com/us/app/go-try-it-on/id398392143?mt=8). It has borrowed a number of concepts from Ruby on Rails and added others where necessary.

## Requirements
    - PHP 5.4+
    - Ruby 1.9+ (For Compass and Migrations)
    - MySQL

## Getting started

The simplest way to create a new project is to clone this repository and then use the included generator, like so:

    git clone git@github.com:gotryiton/gotron.git
    cd gotron
    bin/gotron new blog ~/

That will create the directory `blog` in your home directory, create the necessary files that gotron uses, and add gotron and its dependencies as submodules.

Then, to start the PHP server:

    cd ~/blog
    bundle install
    bundle exec compass compile --config ./config/compass_config.rb
    php -S localhost:9001 -t ./public

Open a browser and go to [http://localhost:9001](http://localhost:9001). You should see `Welcome to your new Gotron app!`

If you want Compass to continually compile CSS you can run `bundle exec guard`.

## Running tests

To run the Gotron test suite:

  1. Install MySQL and make sure it is running. (On a Mac, install homebrew and run `brew install mysql`)
  2. Run:
    ```
    mysql -u root -e "CREATE DATABASE gotron_development; \
      CREATE USER 'test'@'localhost' IDENTIFIED BY 'test'; \
      GRANT ALL PRIVILEGES ON gotron_development.* TO 'test'@'localhost' WITH GRANT OPTION;"
    ```

   (This command may need to be modified depending on the setup of your MySQL instance. If you have any important data on there you should **definitely** set a root password.)
  3. From the root directory run `bin/test`.
