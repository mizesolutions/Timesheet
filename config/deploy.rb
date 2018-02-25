########################
Setup project
########################
set :application, "hello-world-medium"
set :repo_url, "https://github.com/groupname/repository.git"
set :scm, :git
#########################
Setup Capistrano
#########################
set :log_level, :info
set :use_sudo, false
set :ssh_options, {
  forward_agent: true
}
set :keep_releases, 3
#######################################
Linked files and directories (symlinks)
#######################################
set :linked_files, ["app/config/parameters.yml"]
set :linked_dirs, [fetch(:log_path), fetch(:web_path) + "/uploads"] set :file_permissions_paths, [fetch(:log_path), fetch(:cache_path)] set :composer_install_flags, '--no-interaction --optimize-autoloader'
namespace :deploy do
  after :updated, 'composer:install_executable'
end