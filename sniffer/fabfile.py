from __future__ import with_statement
from time import time
import os
from StringIO import StringIO
from tempfile import NamedTemporaryFile

from fabric.api import local, env, run, cd, get
from fabric.decorators import task
from fabric.contrib.files import exists, upload_template

env.hosts = ['grabber-jobs.mercurio.com']
env.user   = "fabric"

git_branch = "master"
releases_dir = "/var/www/sniffer/releases"
git_repo = "/insidevault2015/sniffer.git"
git_user = ""
repo_dir = "/var/www/sniffer/repo"
persist_dir = "/var/www/sniffer/persist"
next_release = "%(time).0f" % {'time': time()}
current_release = "/var/www/sniffer/current"
last_release_file = "/var/www/sniffer/LAST_RELEASE"
current_release_file = "/var/www/sniffer/CURRENT_RELEASE"

# DEPLOY COMMAND
# fab deploy:git_user=bpaglialunga

@task
def deploy(migrate='no', git_user=''):
  init(git_user)
  update_git()
  create_release()
  build_site()
  next_release = get_version()
  if migrate=='yes':
    migrate_from = "%s/%s" % (releases_dir, next_release)
    migrate_forward(migrate_from)
  swap_symlinks()

def get_git_repo(git_user):
  return "git@bitbucket." + git_user + ":" + git_repo

def init(git_user):
  if not exists(releases_dir):
      run("mkdir -p %s" % releases_dir)

  if not exists(repo_dir):
    git_repo_ = get_git_repo(git_user)
    run("git clone -b %s %s %s" % (git_branch, git_repo_, repo_dir))

    # tag
    # run("git tag  %s" % (next_release))
    # run("git push origin --tags")

  if not exists("%s/storage" % persist_dir):
      run("mkdir -p %s/storage/app/public" % persist_dir)
      run("mkdir -p %s/storage/framework/cache" % persist_dir)
      run("mkdir -p %s/storage/framework/sessions" % persist_dir)
      run("mkdir -p %s/storage/framework/views" % persist_dir)
      run("mkdir -p %s/storage/logs" % persist_dir)
      run("chmod 775 -R %s/storage" % persist_dir)

  if not os.path.isfile(last_release_file):
      run("touch %s" % last_release_file)

  if not os.path.isfile(current_release_file):
      run("touch %s" % current_release_file)

@task
def rollback(migrate_back='no'):
  last_release = get_last_release()
  current_release = get_current_release()

  if migrate_back=='yes':
      migrate_from = "%s/%s" % (releases_dir, current_release)
      migrate_backward(migrate_from)

  rollback_release(last_release)

  write_last_release(current_release)
  write_current_release(last_release)

@task
def migrate():
  migrate_forward()

@task
def migrate_back():
  migrate_backward()

def migrate_forward(release_dir=None, env='production'):
  if not release_dir:
      release_dir=current_release

  with cd(release_dir):
      run('php artisan migrate --env=%s' % env)

def migrate_backward(release_dir=None, env='production'):
  if not release_dir:
      release_dir=current_release

  with cd(release_dir):
      run('php artisan migrate:rollback --env=%s' % env)

def get_last_release():
  fd = StringIO()
  get(last_release_file, fd)
  return fd.getvalue()

def get_current_release():
  fd = StringIO()
  get(current_release_file, fd)
  return fd.getvalue()

def write_last_release(last_release):
  last_release_tmp = NamedTemporaryFile(delete=False)
  last_release_tmp.write("%(release)s")
  last_release_tmp.close()

  upload_template(last_release_tmp.name, last_release_file, {'release':last_release}, backup=False)
  os.remove(last_release_tmp.name)

def write_current_release(current_release):
  current_release_tmp = NamedTemporaryFile(delete=False)
  current_release_tmp.write("%(release)s")
  current_release_tmp.close()

  upload_template(current_release_tmp.name, current_release_file, {'release':current_release}, backup=False)
  os.remove(current_release_tmp.name)

def rollback_release(to_release):
  release_into = "%s/%s" % (releases_dir, to_release)
  run("ln -nfs %s %s" % (release_into, current_release))
  run("sudo service nginx reload")

def update_git():
  with cd(repo_dir):
      run("git pull")
      run("git checkout %s" % git_branch)
      run("git pull origin %s" % git_branch)

def create_release():
  next_release = get_version()
  release_into = "%s/%s" % (releases_dir, next_release)
  run("mkdir -p %s" % release_into)
  with cd(repo_dir):
      run("git archive --worktree-attributes %s | tar -x -C %s" % (git_branch, release_into))

def build_site():
  next_release = get_version()
  with cd("%s/%s" % (releases_dir, next_release)):
      run("mkdir -p bootstrap/cache")
      run("chmod 775 -R bootstrap/cache")
      run("composer install")

def get_version():
  if not os.path.isfile(repo_dir+"/version.txt"):
      return next_release

  archivo = open(repo_dir+"/version.txt", "r")
  version = archivo.read()
  archivo.close()
  return version

def swap_symlinks():
  next_release = get_version()
  release_into = "%s/%s" % (releases_dir, next_release)

  run("ln -nfs %s/.env %s/.env" % (persist_dir, release_into))
  run("rm -rf %s/storage" % release_into)
  run("ln -nfs %s/storage %s/storage" % (persist_dir, release_into))

  run("rm -rf %s/public/storage" % release_into)
  run("ln -nfs %s/public/storage %s/public/storage" % (persist_dir, release_into))

  run("ln -nfs %s %s" % (release_into, current_release))

  write_last_release(get_current_release())

  write_current_release(next_release)

  # Reiniciamos PHP
  run("sudo service nginx reload")
  run("sudo /etc/init.d/php7.0-fpm restart")
  run("sudo service supervisor restart")
