-include Makefile.options
name=tts-wp-plugin
wp_data_dir?=wp_data
port?=80


wp_data:
	mkdir -p $@

.env: Makefile | wp_data
	echo PORT=$(port) > $@
	echo UID=`id -u ${USER}` >> $@
	echo GID=`id -g ${USER}` >> $@

up: .env
	docker compose up -d

clean:
	docker compose down -v
	rm -rf $(wp_data_dir)