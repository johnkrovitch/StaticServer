# config valid only for current version of Capistrano
lock '3.4.0'

set :application, 'StaticServer'
set :repo_url, 'https://github.com/johnkrovitch/StaticServer.git'

# Default branch is :master
set :branch, "dev"

# Default deploy_to directory is /var/www/my_app_name
set :deploy_to, '/home/johnkrovitch/www/StaticServer'

# Config
set :scm, :git
set :format, :pretty
set :log_level, 1
set :pty, true

# Symfony application configuration
set :app_path,        "app"
set :web_path,        "web"
set :log_path,        fetch(:app_path) + "/logs"
set :cache_path,      fetch(:app_path) + "/cache"
set :app_config_path, fetch(:app_path) + "/config"
set :symfony_console_path, fetch(:app_path) + "../vendor/bin/console"
set :controllers_to_clear, ["app_*.php"]
set :symfony_env,  "prod"

# Linked files and directories
set :linked_files,          ["app/config/parameters.yml"]
set :linked_dirs,           [fetch(:log_path), fetch(:web_path) + "/uploads", "vendor"]
set :keep_releases, 5

# Composer install
SSHKit.config.command_map[:composer] = "php #{shared_path.join("composer.phar")}"
set :composer_install_flags, '--prefer-dist --no-interaction --optimize-autoloader'

namespace :deploy do
  after :starting, 'composer:install_executable'
end
