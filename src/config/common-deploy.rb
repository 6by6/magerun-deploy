require 'capistrano/ext/multistage'

set :stage_dir,   "config/deploy"

# build a list of available stages
stages = []
Dir::glob('config/deploy/*.rb') do |f|
  stage_name = File.basename(f, ".*")
  stages.push(stage_name.to_sym)
end

set :stages, %w(staging)
set :scm, :git

set  :keep_releases,  3
after "deploy:update", "deploy:cleanup"

default_run_options[:pty] = true
set :use_sudo, false


