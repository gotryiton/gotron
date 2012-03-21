set :rails_env, "staging"
set :branch, "staging"
server "staging.gotryiton.com", :app, :web, :db, :minifier, :primary => true
before "deploy:symlink", "web:minify", "web:create_asset_revision_file"
