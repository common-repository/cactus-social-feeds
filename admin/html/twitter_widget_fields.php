<p>
  <label for="<?php echo $this->get_field_id('name'); ?>"><?php esc_html_e('Twitter Username:', 'cactus');?></label> <input class="widefat twitter_user_name" id="<?php echo $this->get_field_id('name'); ?>" name="<?php echo $this->get_field_name('name'); ?>" type="text" value="<?php echo esc_attr($name); ?>"/>
  <span class="widefat user-validator"><?php esc_html_e('Please enter your Twitter Username', 'cactus');?></span>
</p>
<p>
  <label for="<?php echo $this->get_field_id('cache_time'); ?>"><?php esc_html_e('Cache Period (in mins):', 'cactus');?> </label><input class="widefat" id="<?php echo $this->get_field_id('cache_time'); ?>" name="<?php echo $this->get_field_name('cache_time'); ?>" type="text" value="<?php echo esc_attr($cache_time); ?>"/>
</p>

<div style="border-top: 1px solid #eee;">
  <h4><?php esc_html_e('Twitter API Settings', 'cactus');?></h4>
  <p>Go to <a href="https://dev.twitter.com/apps/new" target="_blank">Twitter App</a> to manager your API keys</p>

  <p>
    <label for="<?php echo $this->get_field_id('consumerKey'); ?>"><?php esc_html_e('Consumer key:', 'cactus');?> </label>
	<input class="widefat" id="<?php echo $this->get_field_id('consumerKey'); ?>" name="<?php echo $this->get_field_name('consumerKey'); ?>" type="text" value="<?php echo esc_attr($consumerKey); ?>"/>
  </p>

  <p>
    <label for="<?php echo $this->get_field_id('consumerSecret'); ?>"><?php esc_html_e('Consumer secret:', 'cactus');?> </label>
	<input class="widefat" id="<?php echo $this->get_field_id('consumerSecret'); ?>" name="<?php echo $this->get_field_name('consumerSecret'); ?>" type="text" value="<?php echo esc_attr($consumerSecret); ?>"/>
  </p>

  <p>
    <label for="<?php echo $this->get_field_id('accessToken'); ?>"><?php esc_html_e('Access Token:', 'cactus');?> </label>
	<input class="widefat" id="<?php echo $this->get_field_id('accessToken'); ?>" name="<?php echo $this->get_field_name('accessToken'); ?>" type="text" value="<?php echo esc_attr($accessToken); ?>"/>
  </p>

  <p>
    <label for="<?php echo $this->get_field_id('accessTokenSecret'); ?>"><?php esc_html_e('Access Token Secret:', 'cactus');?> </label>
	<input class="widefat" id="<?php echo $this->get_field_id('accessTokenSecret'); ?>" name="<?php echo $this->get_field_name('accessTokenSecret'); ?>" type="text" value="<?php echo esc_attr($accessTokenSecret); ?>"/>
  </p>
</div>