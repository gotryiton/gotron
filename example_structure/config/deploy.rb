set :application, "test_app"
set :repository, "git@github.com:gotryiton/test_app.git"
set :scm, :git
set :deploy_to, "/var/www/saas"
set :user, "deployer"
set :keep_releases, 6
                             
set :minifying, false

set :stages, %w(production staging dev load_testing)
set :default_stage, "dev"

require 'capistrano/ext/multistage'

set :deploy_via, :remote_cache
set :copy_exclude, [".git", ".DS_Store", ".gitignore", ".gitmodules"]

before :deploy do
  unless exists?(:deploy_to)
    raise "Please invoke me like `cap stage deploy` where stage is production/staging/dev"
  end
end

namespace :web do
  desc "Compile compass css"
  task :compile_assets, :roles => :app do
    run "compass clean #{latest_release} --config #{latest_release}/config/compass_config.rb"
    run "compass compile #{latest_release} --config #{latest_release}/config/compass_config.rb"
  end
  
  desc "Create the ASSET_REVISION file"
  task :create_asset_revision_file, :roles => :app do
    if fetch(:minifying)
      run "echo '#{latest_revision}' > #{shared_path}/ASSET_REVISION"
    end

    # Always need the revision file in the current_path
    run "cp #{shared_path}/ASSET_REVISION #{latest_release}/ASSET_REVISION"
  end
  
  task :reload_fpm, :roles => :web do
    run "ruby #{latest_release}/tools/minifydeploy.rb #{latest_revision} #{stage.upcase}"
  end
  
  task :apc_clear, :roles => :web do
    run "curl -s http://localhost/cleanup/clear_cache"
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

namespace :fpm do
  desc "reload php-fpm"
  task :reload, :roles => :web do
    run "#{sudo} sv 2 php5-fpm"
  end
  
  [:stop, :start, :restart].each do |action|
    desc "#{action.to_s} php-fpm"
    task action, :roles => :web do
      run "#{sudo} sv #{action.to_s} php5-fpm"
    end
  end
end

before  "deploy:symlink", "deploy:migrate"
after "deploy:symlink", "web:apc_clear", "fpm:reload"
after "deploy:update", "deploy:cleanup"