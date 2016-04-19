require 'capistrano/ext/multistage'

set :stage_dir,   "config/deploy"

# build a list of available stages
stages = []
Dir::glob('app/config/deploy/*.rb') do |f|
  stage_name = File.basename(f, ".*")
  stages.push(stage_name.to_sym)
end

set :stages, %w(staging)


set :application, ""
set :repository,  ""
set :scm,         :git

set  :keep_releases,  3
after "deploy:update", "deploy:cleanup"

default_run_options[:pty] = true
set :use_sudo, false

set :app_symlinks, ["/media", "/var", "/sitemaps", "/staging", "/bower_components", "/node_modules", "/wp/wp-content/uploads"]
set :app_shared_dirs, ["/app/etc", "/sitemaps", "/media", "/var", "/staging", "/bower_components", "/node_modules", "/wp/wp-content/uploads"]
set :app_shared_files, ["/app/etc/local.xml", "/robots.txt", "/wp/wp-config.php"]

after "deploy:update_code" do
  run("cd #{release_path}; composer update")

  run("mkdir -p #{release_path}/skin/frontend/bluebellgray/default/build/js")
  run("mkdir -p #{release_path}/skin/frontend/bluebellgray/default/build/css")
  run_locally("npm install --silent")
  run_locally("bower install --config.interactive=false")
  run_locally("gulp release")
  upload("skin/frontend/bluebellgray/default/build/js", "#{release_path}/skin/frontend/bluebellgray/default/build/js")
  upload("skin/frontend/bluebellgray/default/build/css", "#{release_path}/skin/frontend/bluebellgray/default/build/css")
end
