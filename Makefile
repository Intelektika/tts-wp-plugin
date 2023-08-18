-include Makefile.options
name=tts-wp-plugin

deploy:
	rm -rf $(wp_plugins_dir)/$(name)
	cp -rf ./ $(wp_plugins_dir)/$(name)