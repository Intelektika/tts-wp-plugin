-include Makefile.options
name=tts-wp-plugin
wp_plugins_dir?=examples/wp_data/wp-content/plugins/

deploy:
	# rm -rf $(wp_plugins_dir)/$(name)
	mkdir -p $(wp_plugins_dir)/$(name)
	cp -rf ./*php $(wp_plugins_dir)/$(name)/
	cp -rf ./includes $(wp_plugins_dir)/$(name)/