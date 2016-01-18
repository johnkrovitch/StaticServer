sf=app/console
web_dir=web
copy=rsync



all: install

install:
	composer install -n
	bundle install --path=vendor/

deploy:
	bundle exec cap staging deploy
	bundle exec cap staging deploy:cleanup

cleanup:
	bundle exec cap staging deploy:cleanup
