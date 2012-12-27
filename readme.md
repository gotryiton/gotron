      _____       _
     / ____|     | |
    | |  __  ___ | |_ _ __ ___  _ __
    | | |_ |/ _ \| __| '__/ _ \| '_ \
    | |__| | (_) | |_| | | (_) | | | |
     \_____|\___/ \__|_|  \___/|_| |_|

Gotron is a web application framework for PHP developers.

Gotron powers the [Go Try It On](http://www.gotryiton.com) website and the api for the [Go Try It On iPhone application](https://itunes.apple.com/us/app/go-try-it-on/id398392143?mt=8). It has borrowed a number of concepts from Ruby on Rails and added others where necessary.

## Getting started

The simplest way to create a new project is to clone this repository and then use the included generator, like so:

    git clone git@github.com:gotryiton/gotron.git
    cd gotron
    bin/gotron new blog ~/blog

That will create the directory `blog` in your home directory, create the necessary files that gotron uses, and add gotron and it's dependencies as submodules.

Then, to start the php server:

    cd ~/blog
    php -S localhost:9001 -t ./public
    open http://localhost:9001

You should see `Welcome to your new Gotron app!`

## Running tests

To run the Gotron test suite:
  1. Install mysql and make sure it is running.
  2. Run `mysql -u root -e "CREATE DATABASE gotron_development; CREATE USER 'test'@'localhost' IDENTIFIED BY 'test'; GRANT ALL PRIVILEGES ON gotron_development.* TO 'test'@'localhost' WITH GRANT OPTION;"` (command may need to be modified depending on the setup of the mysql instance)
  3. From the root directory run `bin/test`.
