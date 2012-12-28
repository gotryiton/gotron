set :rails_env, "staging"
set :branch, "staging"

server 'staging.example.com', :app, :web, :db, :primary => true

before "deploy:symlink", "deploy:migrate"
after  "deploy:symlink", "web:fpm_reload"
