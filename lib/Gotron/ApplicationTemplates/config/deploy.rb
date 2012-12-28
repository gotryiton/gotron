set :application, "@app_name"
set :repository, "git@github.com:example/@app_name.git"
set :deploy_to, "/var/www/@app_name"
set :user, "deploy"
set :scm, :git
set :keep_releases, 6
set :stages, %w(production staging)
set :default_stage, "staging"
set :git_enable_submodules, 1
set :deploy_via, :remote_cache
set :copy_exclude, [".git", ".DS_Store", ".gitignore", ".gitmodules"]

set :minifying, false
set :assets_dictionary, ""

require 'capistrano/ext/multistage'

before :deploy do
  unless exists?(:deploy_to)
    raise "Please invoke me like `cap stage deploy` where stage is production/staging/dev"
  end
end

namespace :web do
  desc "reload php-fpm"
  task :fpm_reload, :roles => :web do
    run "#{sudo} service php-fpm reload"
  end
end

namespace :maintenance do
  desc "Turn maintenance on"
  task :on do
    run "echo 'SHOW_MAINTENANCE' > #{deploy_to}/MAINTENANCE"
  end

  desc "Turn maintenance off"
  task :off do
    run "rm #{deploy_to}/MAINTENANCE"
  end
end

after  "deploy:update", "deploy:cleanup"
