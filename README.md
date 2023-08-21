# tts-wp-plugin

Intelektika TTS WordPress plugin allows generating Lithuanian audio for your posts.

*Tested with WP (version 6.3)*

## Installation

The plugin depends on [Action Scheduler](https://wordpress.org/plugins/action-scheduler/) plugin. Please install and activate it before starting (https://actionscheduler.org/usage/).

### Install from GitHub (ZIP File)

1. Download the zip file of the plugin:
    1. Visit the GitHub repository: [tts-wp-plugin](https://github.com/Intelektika/tts-wp-plugin).
    1. Click on the **Code** button.
    1. Select **Download ZIP** to download the plugin ZIP file.
1. Go to your WordPress admin panel.
1. Navigate to **Plugins > Add New**.
1. Click the **Upload Plugin** button.
1. Choose the downloaded ZIP file and click **Install Now**.
1. After installation, click **Activate** to activate the plugin.

## Configuration

To use the plugin you need an API key. Contact info@intelektika.lt to acquire it. But you can start testing without the key (there is a monthly free-tier limit of 5000 symbols).

1. Navigate to the plugin settings.
1. Enter the **api key**.
1. Configure **voice** and **speed** settings.  
1. Use **Test TTS** button to investigate configuration possibilities for the audio output.
1. Save changes.

## Usage

The plugin will add an audio component in a post at the position of a newly inserted shortcode `[itts_audio]`. To add it into a post:
1. Open a post (new or existing) in an edit mode.
1. Insert `[itts_audio]` shortcode. Preferably on the first section after a title.
1. Save/publish a post.

The plugin will synthesize an audio of a post and it will cache it in the **[WP upload dir]/itts_generated_audio/** directory.
The audio will be regenerated on every save/update of a post. 

If there is an error then it will be displayed in a post when opened in the preview mode by an editor. 
