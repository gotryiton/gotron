set :rails_env, "production"
set :branch, "master"

server 'server1.example.com', :app, :web, :db, :primary => true

before "deploy:symlink", "deploy:migrate"
after  "deploy:symlink", "web:fpm_reload"
