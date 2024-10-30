<?php

	/**
	 * Plugin Name: Cactus Social Feeds
	 * Description: Get images from Twitter and Instagram in a beautiful gallery
	 * Version: 1.0.2
	 * Author: CactusThemes
	 * Author URI: https://www.cactusthemes.com/
	 */
	class Cactus_Social_Feeds extends WP_Widget {

		const LANGUAGE_DOMAIN = 'cactus';

		function __construct() {

			$widget_ops = array(
				'classname'   => 'ct_social_feed',
				'description' => esc_html__( 'Get image from Twitter and Instagram', self::LANGUAGE_DOMAIN )
			);

			parent::__construct( 'ct_social_feed', esc_html__( 'Cactus Twitter and Instagram Feed', self::LANGUAGE_DOMAIN ), $widget_ops );

			add_action( 'wp_enqueue_scripts', array( $this, 'cactus_social_feed_frontend_scripts' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_cactus_social_feed_frontend_scripts' ) );
			add_shortcode( 'ct_social_feed', array( $this, 'parse_shortcode' ) );

			load_plugin_textdomain( self::LANGUAGE_DOMAIN, false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
		}

		public function parse_shortcode( $atts, $content ) {

			if ( isset( $atts['twitter_name'] ) && isset( $atts['instagram_name'] ) && isset( $atts['twitter_consumer_secret'] ) && isset( $atts['twitter_access_token_secret'] ) && isset( $atts['twitter_access_token'] ) && isset( $atts['twitter_consumer_key'] ) ) {
				$instance = wp_parse_args( array(), ctsf_default_args() );

				$instance['name']              = $atts['twitter_name'];
				$instance['consumerSecret']    = $atts['twitter_consumer_secret'];
				$instance['accessTokenSecret'] = $atts['twitter_access_token_secret'];
				$instance['accessToken']       = $atts['twitter_access_token'];
				$instance['consumerKey']       = $atts['twitter_consumer_key'];
				$instance['isg_username']      = $atts['instagram_name'];
				$instance['cache_time']        = 4;

				$layout = isset( $atts['layout'] ) ? $atts['layout'] : 1;

				$tweets = ctsf_get_tweet( $instance );

				return ctsf_output( $instance, $tweets, $layout );
			}

			return '';
		}

		/*
		 * Enqueue Styles and Scripts
		 */
		function cactus_social_feed_frontend_scripts() {
			// //main css
			wp_enqueue_style( 'cactus-social-feed', plugins_url( "css/cactus-social-feed.css", __FILE__ ), array(), '20160621' );
			wp_enqueue_style( 'fontawesome', plugins_url( "css/css/fontawesome-all.min.css", __FILE__ ), array(), '5.0.7' );

			// //main js
			wp_enqueue_script( 'cactus-social-feed', plugins_url( "js/cactus-social-feed.js", __FILE__ ), array(), '20160621', true );
		}

		function admin_cactus_social_feed_frontend_scripts() {
			wp_enqueue_style( 'cactus-social-feed-admin', plugins_url( "css/cactus-social-feed-admin.css", __FILE__ ), array(), '20160621' );
		}

		function widget( $args, $instance ) {
			extract( $args, EXTR_SKIP );

			$instance = wp_parse_args( (array) $instance, ctsf_default_args() );

			// print the before widget
			echo $before_widget;

			if ( $instance['title'] ) {
				echo $before_title . $instance['title'] . $after_title;
			}

			$tweets = ctsf_get_tweet( $instance );

			$layout = $instance['layout'];

			echo ctsf_output( $instance, $tweets, $layout );

			// Print the after widget
			echo $after_widget;
		}

		/**
		 * Update the widget settings.
		 *
		 * Backend widget settings
		 */
		function update( $new_instance, $old_instance ) {
			$instance                 = $old_instance;
			$instance['title']        = strip_tags( $new_instance['title'] );
			$instance['layout']            = $new_instance['layout'];
			
			$instance['isg_username'] = strip_tags( $new_instance['isg_username'] );
			
			$instance['name']       = strip_tags( $new_instance['name'] ); // twitter username
			$instance['cache_time'] = $new_instance['cache_time']; // cache time
			
			$instance['consumerKey']       = trim( $new_instance['consumerKey'] );
			$instance['consumerSecret']    = trim( $new_instance['consumerSecret'] );
			$instance['accessToken']       = trim( $new_instance['accessToken'] );
			$instance['accessTokenSecret'] = trim( $new_instance['accessTokenSecret'] );			

			return $instance;
		}

		/**
		 * Displays the widget settings controls on the widget panel.
		 *
		 * Backend widget options form
		 */

		function form( $instance ) {

			$instance = wp_parse_args( (array) $instance, ctsf_default_args() ); // merge the user-selected arguments with the defaults.

			$layout = isset( $instance['layout'] ) ? $instance['layout'] : 1;

			$tabs = array(
				esc_html__( 'General', self::LANGUAGE_DOMAIN ),
				esc_html__( 'Twitter', self::LANGUAGE_DOMAIN ),
				esc_html__( 'Instagram', self::LANGUAGE_DOMAIN )
			);

			?>

            <script type="text/javascript">
				// Tabs function
				jQuery(document).ready(function ($) {
					// Tabs function
					$('ul.nav-tabs li').each(function (i) {
						$(this).bind("click", function () {
							var liIndex = $(this).index();
							var content = $(this).parent("ul").next().children("li").eq(liIndex);
							$(this).addClass('active').siblings("li").removeClass('active');
							$(content).show().addClass('active').siblings().hide().removeClass('active');

							$(this).parent("ul").find("input").val(0);
							$('input', this).val(1);
						});
					});
				});
            </script>

            <div id="fbw-<?php echo $this->id; ?>" class="totalControls tabbable tabs-left">
                <ul class="nav nav-tabs">
					<?php foreach ( $tabs as $key => $tab ) : ?>
                        <li class="fes-<?php echo $key; ?> <?php echo $instance['tab'][ $key ] ? 'active' : ''; ?>"><?php echo $tab; ?>
                            <input type="hidden" name="<?php echo $this->get_field_name( 'tab' ); ?>[]" value="<?php echo $instance['tab'][ $key ]; ?>"/>
                        </li>
					<?php endforeach; ?>
                </ul>

                <ul class="tab-content">
                    <li class="tab-pane <?php if ( $instance['tab'][0] ) : ?>active<?php endif; ?>">
                        <ul>
                            <li>
                                <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php esc_html_e( 'Title', self::LANGUAGE_DOMAIN ); ?></label>
                                <span class="controlDesc"><?php esc_html_e( 'Give the widget title, or leave it empty for no title.', self::LANGUAGE_DOMAIN ); ?></span>
                                <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>"/>
                            </li>
                            <li>
                                <label for="<?php echo $this->get_field_id( 'layout' ); ?>"><?php esc_html_e( 'Layout', self::LANGUAGE_DOMAIN ); ?></label>
                                <span class="controlDesc"><?php esc_html_e( 'Choose layout', self::LANGUAGE_DOMAIN ); ?></span>
                                <select id="<?php echo $this->get_field_id( 'layout' ); ?>" name="<?php echo $this->get_field_name( 'layout' ); ?>">
                                    <option value="1" <?php echo $layout == 1 ? 'selected="selected"' : ''; ?>><?php esc_html_e( '2 rows', self::LANGUAGE_DOMAIN ); ?></option>
                                    <option value="2" <?php echo $layout == 2 ? 'selected="selected"' : ''; ?>><?php esc_html_e( '3 rows', self::LANGUAGE_DOMAIN ); ?></option>
                                </select>
                            </li>
                        </ul>
                    </li>

                    <li class="tab-pane <?php if ( $instance['tab'][1] ) : ?>active<?php endif; ?>">
						<?php

							$defaults = array(
													'name'              => '',
													'cache_time'        => 4,
													'consumerKey'       => '',
													'consumerSecret'    => '',
													'accessToken'       => '',
													'accessTokenSecret' => '',
												);
												
							$instance          = wp_parse_args( (array) $instance, $defaults );
							$widget_title      = $instance['title'];
							$name              = $instance['name'];
							
							$accessTokenSecret = trim( $instance['accessTokenSecret'] );
							$consumerSecret    = trim( $instance['consumerSecret'] );
							$accessToken       = trim( $instance['accessToken'] );
							
							$cache_time     = $instance['cache_time'];
							$consumerKey      = trim( $instance['consumerKey'] );
							
							if ( ! in_array( 'curl', get_loaded_extensions() ) ) {
								echo '<p style="background-color:pink;padding:10px;border:1px solid red;"><strong>cURL is not installed!</strong></p>';
							}

						?><?php include( 'admin/html/twitter_widget_fields.php' ); ?>
                    </li>
                    <li class="tab-pane <?php if ( $instance['tab'][2] ) : ?>active<?php endif; ?>">
                        <ul>
                            <li>
                                <label for="<?php echo $this->get_field_id( 'isg_username' ); ?>"><?php esc_html_e( 'Username', self::LANGUAGE_DOMAIN ); ?></label>
                                <input id="<?php echo $this->get_field_id( 'isg_username' ); ?>" name="<?php echo $this->get_field_name( 'isg_username' ); ?>" type="text" value="<?php echo esc_attr( $instance['isg_username'] ); ?>"/>
                            </li>
                        </ul>

                    </li>
                </ul>
            </div>
			<?php
		}
	}
	
	// register  widget
	add_action( 'widgets_init', function(){ return register_widget("Cactus_Social_Feeds");} );

	function ctsf_default_args() {
		return array(
			'title'        => esc_html__( 'Cactus Social Feeds', Cactus_Social_Feeds::LANGUAGE_DOMAIN ),
			'isg_username' => ''
		);
	}

	if ( ! function_exists( 'ctsf_output' ) ) {

		/**
		 * $args - array of settings
		 * $tweets - array of Tweets
		 * $layout - 1 (3 rows); 2 (2 rows)
		 **/
		function ctsf_output( $args, $tweets = array(), $layout = 1 ) {
			
			$output = '';

			// Get the user direction, rtl or ltr
			if ( function_exists( 'is_rtl' ) ) {
				$dir = is_rtl() ? 'rtl' : 'ltr';
			}

			$protocol = is_ssl() ? 'https' : 'http';

			if ( $args['isg_username'] != '' ) {
				$output .= "<div class='cactus-social-feed-wrapper ctsf-wrap-$dir'>";

				$insta_images = ctsf_scrape_instagram( $args['isg_username'] );

				if ( is_wp_error( $insta_images ) ) {

					echo wp_kses_post( $insta_images->get_error_message() );

				} else {
					$insta_index   = 0;
					$twitter_index = 0;

					for ( $i = 0; $i < ( $layout == 1 ? 9 : 6 ); $i ++ ) {
						if ( in_array( $i, array( 0, 3, 6 ) ) ) {
							$output .= '<div class="' . apply_filters( 'ctsf_row_class', 'row' ) . '">';
						}


						if ( in_array( $i, array( 1, 3, 8 ) ) ) {
							// fetch twitter
							$output .= '<div class="' . apply_filters( 'ctsf_col_twitter_class', 'col-twitter col col-sm-12 col-md-6' ) . '">';
							$output .= apply_filters( 'ctsf_before_twitter_col', '' );

							if ( isset( $tweets[ $twitter_index ] ) ) {
								$tweet  = $tweets[ $twitter_index ];
								$output .= '<div class="twitter"> <div class="inner">';

								$timeDisplay = twitter_time_diff( $tweet['time'], current_time( 'timestamp' ) );
								
								$displayAgo = esc_html__( ' ago', Cactus_Social_Feeds::LANGUAGE_DOMAIN );

								$tweet_time = sprintf( esc_html__( '%1$s ago' ), $timeDisplay);

								$output .= '<div class="tweet-time">' . $tweet_time . '</div>';

								$output .= '<div class="tweet-text">' . $tweet['text'] . '</div>';

								$output .= '</div></div>';
							}

							$output .= apply_filters( 'ctsf_after_twitter_col', '' );
							$output .= ' </div>';

							$twitter_index ++;
						} else {
							// fetch instagram
							$output .= '<div class="' . apply_filters( 'ctsf_col_insta_class', 'col-insta col col-sm-6 col-md-3' ) . '">';
							$output .= apply_filters( 'ctsf_before_insta_col', '' );
							if ( isset( $insta_images[ $insta_index ] ) ) {
								$insta = $insta_images[ $insta_index ];

								$output .= '<div class="instagram"><a href="' . esc_url( $insta['link'] ) . '"><img src="' . esc_url( $insta[ 'thumbnail' ] ) . '" alt="' . esc_attr( $insta['description'] ) . '" title="' . esc_attr( $insta['description'] ) . '"/></a></div>';
							}
							$output .= apply_filters( 'ctsf_after_insta_col', '' );
							$output .= '</div>';

							$insta_index ++;
						}

						if ( in_array( $i, array( 2, 5, 8 ) ) ) {
							$output .= '</div>'; // .row
						}
					}
				}

				$output .= '</div>';
			}

			return $output;
		}
	}

	// based on https://gist.github.com/cosmocatalano/4544576
	function ctsf_scrape_instagram( $username ) {

		$username = strtolower( $username );
		$username = str_replace( '@', '', $username );

		// get cache
		$instagram = get_transient( 'instagram-a5-' . sanitize_title_with_dashes( $username ) );

		if ( ! $instagram ) {

			$remote = wp_remote_get( 'http://instagram.com/' . trim( $username ) );

			if ( is_wp_error( $remote ) ) {
				return new WP_Error( 'site_down', esc_html__( 'Unable to communicate with Instagram.', Cactus_Social_Feeds::LANGUAGE_DOMAIN ) );
			}

			if ( 200 != wp_remote_retrieve_response_code( $remote ) ) {
				return new WP_Error( 'invalid_response', esc_html__( 'Instagram did not return a 200.', Cactus_Social_Feeds::LANGUAGE_DOMAIN ) );
			}

			$shards     = explode( 'window._sharedData = ', $remote['body'] );
			$insta_json = explode( ';</script>', $shards[1] );

			$insta_array = json_decode( $insta_json[0], true );

			if ( ! $insta_array ) {
				return new WP_Error( 'bad_json', esc_html__( 'Instagram has returned invalid data.', Cactus_Social_Feeds::LANGUAGE_DOMAIN ) );
			}

			if ( isset( $insta_array['entry_data']['ProfilePage'][0]['graphql']['user']['edge_owner_to_timeline_media']['edges'] ) ) {
				$images = $insta_array['entry_data']['ProfilePage'][0]['graphql']['user']['edge_owner_to_timeline_media']['edges'];
			} else {
				return new WP_Error( 'bad_json_2', esc_html__( 'Instagram has returned invalid data.', Cactus_Social_Feeds::LANGUAGE_DOMAIN ) );
			}

			if ( ! is_array( $images ) ) {
				return new WP_Error( 'bad_array', esc_html__( 'Instagram has returned invalid data.', Cactus_Social_Feeds::LANGUAGE_DOMAIN ) );
			}

			$instagram = array();

			foreach ( $images as $image ) {

				$image['thumbnail_src'] = preg_replace( '/^https?\:/i', '', $image['node']['thumbnail_src'] );
				$image['display_src']   = preg_replace( '/^https?\:/i', '', $image['node']['display_url'] );

				if ( $image['node']['is_video'] == true ) {
					$type = 'video';
				} else {
					$type = 'image';
				}

				$caption = __( 'Instagram Image', Cactus_Social_Feeds::LANGUAGE_DOMAIN );
				if ( ! empty( $image['edge_media_to_caption']['edges'][0]['node']['text'] ) ) {
					$caption = $image['edge_media_to_caption']['edges'][0]['node']['text'];
				}

				$datetime = DateTime::createFromFormat( 'U', $image['node']['taken_at_timestamp'] );

				$instagram[] = array(
					'description' => $caption,
					'link'        => trailingslashit( 'https://instagram.com/p/' . $image['node']['shortcode'] ) . '?taken-by=' . $username,
					'time'        => $datetime->format( 'dd/mm/yyyy' ),
					'comments'    => $image['node']['edge_media_to_comment']['count'],
					'likes'       => $image['node']['edge_media_preview_like']['count'],
					'thumbnail'   => $image['thumbnail_src'],
					'original'    => $image['display_src'],
					'type'        => $type
				);
			}

			// do not set an empty transient - should help catch private or empty accounts
			if ( ! empty( $instagram ) ) {
				$instagram = base64_encode( serialize( $instagram ) );
				set_transient( 'instagram-a5-' . sanitize_title_with_dashes( $username ), $instagram, apply_filters( 'ctsf_instagram_cache_time', HOUR_IN_SECONDS * 2 ) );
			}
		}

		if ( ! empty( $instagram ) ) {

			return unserialize( base64_decode( $instagram ) );

		} else {

			return new WP_Error( 'no_images', esc_html__( 'Instagram did not return any images.', Cactus_Social_Feeds::LANGUAGE_DOMAIN ) );

		}
	}

	function ctsf_images_only( $media_item ) {

		if ( $media_item['type'] == 'image' ) {
			return true;
		}

		return false;
	}

	function ctsf_get_tweet( $instance ) {
		$twitter_name              = $instance['name'];
		$cactus_sf_consumerSecret    = trim( $instance['consumerSecret'] );
		$cactus_sf_accessTokenSecret = trim( $instance['accessTokenSecret'] );
		$widget_replies_excl         = true;
		$cactus_sf_accessToken       = trim( $instance['accessToken'] );
		$cache_time        = $instance['cache_time'];
		$cactus_sf_consumerKey       = trim( $instance['consumerKey'] );

		$tweets_count      = 3;
		
		$consumerSecret    = trim( $cactus_sf_consumerSecret );
		$accessToken       = trim( $cactus_sf_accessToken );
		$accessTokenSecret = trim( $cactus_sf_accessTokenSecret );
		$replies_excl      = $widget_replies_excl;
		$consumerKey       = trim( $cactus_sf_consumerKey );
		$transName         = 'list-tweets-' . $twitter_name;
		$backupName        = $transName . '-backup';

		if ( false === ( $tweets = get_transient( $transName ) ) ) :
			require_once dirname( __FILE__ ) . '/twitteroauth/twitteroauth.php';

			$api_call = new TwitterOAuth( $consumerKey, $consumerSecret, $accessToken, $accessTokenSecret );

			$totalToFetch = ( $replies_excl ) ? max( 50, $tweets_count * 3 ) : $tweets_count;

			$fetchedTweets = $api_call->get( 'statuses/user_timeline', array(
				'screen_name'  => $twitter_name,
				'count'        => $totalToFetch,
				'replies_excl' => $replies_excl
			) );

			if ( $api_call->http_code != 200 ) :
				$tweets = get_option( $backupName );

			else :
				$limitToDisplay = min( $tweets_count, count( $fetchedTweets ) );

				for ( $i = 0; $i < $limitToDisplay; $i ++ ) :
					$tweet       = $fetchedTweets[ $i ];
					$name        = $tweet->user->name;
					$screen_name = $tweet->user->screen_name;
					$permalink   = 'https://twitter.com/' . $name . '/status/' . $tweet->id_str;
					$tweet_id    = $tweet->id_str;
					if ( is_ssl() ) {
						$image = esc_url( ( isset( $tweet->retweeted_status ) ) ? $tweet->retweeted_status->user->profile_image_url_https : $tweet->user->profile_image_url_https );
					} else {
						$image = esc_url( ( isset( $tweet->retweeted_status ) ) ? $tweet->retweeted_status->user->profile_image_url : $tweet->user->profile_image_url );
					}
					//$image = $tweet->user->profile_image_url;
					$text     = ctsf_sanitize_links( $tweet );
					$time     = $tweet->created_at;
					$time     = date_parse( $time );
					$uTime    = mktime( $time['hour'], $time['minute'], $time['second'], $time['month'], $time['day'], $time['year'] );
					$tweets[] = array(
						'text'            => $text,
						'scr_name'        => $screen_name,
						'favourite_count' => $tweet->favorite_count,
						'retweet_count'   => $tweet->retweet_count,
						'name'            => $name,
						'permalink'       => $permalink,
						'image'           => $image,
						'time'            => $uTime,
						'tweet_id'        => $tweet_id
					);
				endfor;
				set_transient( $transName, $tweets, 60 * $cache_time );
				update_option( $backupName, $tweets );
			endif;
		endif;

		return $tweets;
	}

	if ( ! function_exists( 'twitter_time_diff' ) ) {

		function twitter_time_diff( $from, $to = '' ) {
			$diff    = human_time_diff( $from, $to );
			$replace = array(
				' hour'    => 'h',
				' hours'   => 'h',
				' day'     => 'd',
				' days'    => 'd',
				' minute'  => 'm',
				' minutes' => 'm',
				' second'  => 's',
				' seconds' => 's',
			);

			return strtr( $diff, $replace );
		}

	}


	function ctsf_sanitize_links( $tweet ) {
		if ( isset( $tweet->retweeted_status ) ) {
			$rt_section = current( explode( ":", $tweet->text ) );
			$text       = $rt_section . ": ";
			$text       .= $tweet->retweeted_status->text;
		} else {
			$text = $tweet->text;
		}
		$text = preg_replace( '/((http)+(s)?:\/\/[^<>\s]+)/i', '<a href="$0" target="_blank" rel="nofollow">$0</a>', $text );
		$text = preg_replace( '/[@]+([A-Za-z0-9-_]+)/', '<a href="https://twitter.com/$1" target="_blank" rel="nofollow">@$1</a>', $text );
		$text = preg_replace( '/[#]+([A-Za-z0-9-_]+)/', '<a href="https://twitter.com/search?q=%23$1" target="_blank" rel="nofollow">$0</a>', $text );

		return $text;
	}

?>