<?php
/*
Plugin Name: WP-Offload
Plugin URI: http://steadyoffload.com/
Description: WP-Offload will boost the performance of your blog by seamlessly offloading static content like images, documents and movies. This will greatly reduce bandwidth consumption and the number of HTTP requests issued to your web server. Additional features such as remote image manipulation and thumbnail generation are provided. You need a free <a href="http://steadyoffload.com/sign-up">SteadyOffload account</a>. After getting your account, enter your SteadyOffload key and enable offloading via <strong>Options > WP-Offload</strong>.
Author: Blagovest Buyukliev
Version: 1.0
Author URI: http://buyukliev.blogspot.com/
*/

if (($token = get_option('offload_account_token')) && get_option('offload_enabled')) {
  $offload_attrs = array('xmanip', 'xtype', 'xjpegquality', 'xnonce');
  $offload_extensions = array('.jpg', '.jpeg', '.png', '.gif', '.pdf', '.doc', '.rtf', '.mpg', '.mpeg', '.avi', '.qt', '.flv', '.wmv');
  define('OFFLOAD_ROOT', "http://steadyoffload.com:8080/{$token}.");
  define('OFFLOAD_EXTERNAL_URLS', get_option('offload_external_urls') ? true : false);
  add_filter('the_content', 'offload_rewrite', 99);
}

add_action('admin_menu', 'offload_modify_menu');
register_activation_hook(__FILE__, 'offload_set_options');
register_deactivation_hook(__FILE__, 'offload_unset_options');

function offload_set_options () {
  add_option('offload_enabled', '');
  add_option('offload_account_token', '');
  add_option('offload_external_urls', '');
}

function offload_unset_options () {
  delete_option('offload_enabled');
  delete_option('offload_account_token');
  delete_option('offload_external_urls');
}

function offload_options_form () {
  if (isset($_REQUEST['update'])) {
    update_option('offload_enabled', isset($_REQUEST['offload_enabled']) ? '1' : '');
    update_option('offload_account_token', $_REQUEST['offload_account_token']);
    update_option('offload_external_urls', isset($_REQUEST['offload_external_urls']) ? '1' : '');
    echo '<div id="message" class="updated fade"><p>Options Updated</p></div>';
  }
?><div class="wrap">
    <h2>WP-Offload Options</h2>
    <form method="post">
      <label>
      <input type="checkbox" name="offload_enabled" <?php if (get_option('offload_enabled')) echo "checked" ?> />
      Enable Offloading
      </label>
      <br /><br />
      <label>
      SteadyOffload Account Key:
      <input type="text" name="offload_account_token" maxlength="10" value="<?php echo get_option('offload_account_token')?>" /> (10 alphanumeric characters)
      </label>
      <br /><br />
      <label>
      <input type="checkbox" name="offload_external_urls" <?php if (get_option('offload_external_urls')) echo "checked" ?> />
      Offload External URLs (not recommended)
      </label>
      <br /><br />
      <p class="submit"><input type="submit" name="update" value="Update Options &raquo;" /></p>
    </form>
  </div>
  <div class="wrap">
    When enabled, WP-Offload will automatically substitute the URLs of the images in your posts, pointing them to the SteadyOffload cache servers. Links to images, documents and movies are also substituted. Static content remains stored on your server while being mirrored and delivered from the SteadyOffload cache servers. Note that the substitution process is dynamic - when offloading is not enabled, all the URLs will go back to their previous state.<br /><br />
    As an additional feature, there are a number of custom HTML attributes which you can use within the &lt;a&gt; and &lt;img&gt; tags of your posts:<br /><br />
    <tt>xmanip</tt>: Image manipulation stack (JPEG only);<br />
    <tt>xtype</tt>: Custom MIME type to be sent as a Content-Type HTTP header;<br />
    <tt>xjpegquality</tt>: Quality factor for JPEG images ranging from 1 to 100;<br />
    <tt>xnonce</tt>: An arbitrary value to issue a cache refresh.<br />
    <br />
    A typical &lt;img&gt; tag may look like:
    <br />
    <tt>&lt;img src="http://mysite.com/foo.jpg" xmanip="CropCenter 300, 200; RescaleWidth 150" xjpegquality="75" /&gt;</tt>
    <br /><br />
    A typical &lt;a&gt; tag may look like:
    <br />
    <tt>&lt;a href="http://mysite.com/foo.pdf" xtype="application/pdf" xnonce="last updated: 05.05.2008"&gt;Click&lt;/a&gt;</tt>
  </div>
  <div class="wrap">
    <h2>SteadyOffload Account</h2>
    Obtain a free SteadyOffload account at <strong><a href="http://steadyoffload.com/sign-up">http://steadyoffload.com/sign-up</a></strong>
    <br /><br />
    Manage your existing account through the SteadyOffload control panel <strong><a href="http://steadyoffload.com/panel">http://steadyoffload.com/panel</a></strong>
  </div><?php
}

function offload_modify_menu () {
  add_options_page('WP-Offload', 'WP-Offload', 'manage_options', __FILE__, 'offload_options_form');
}

function offload ($url) {
  return defined('OFFLOAD_ROOT') ? OFFLOAD_ROOT . base64_encode(offload_normalize_url($url)) : $url;
}

function offload_rewrite ($contents) {
  while (($pos_beg = strpos($contents, '<img ', $pos_beg)) !== false) {
    $pos_beg += 5;

    if (($pos_end = strpos($contents, '>', $pos_beg)) === false) {
      break;
    }

    $contents{$pos_end - 1} == '/' && $pos_end--;
    $contents = substr_replace(
      $contents,
      offload_transform_tag(substr($contents, $pos_beg, $pos_end - $pos_beg), 'src'),
      $pos_beg,
      $pos_end - $pos_beg
    );
  }

  while (($pos_beg = strpos($contents, '<a ', $pos_beg)) !== false) {
    $pos_beg += 3;

    if (($pos_end = strpos($contents, '>', $pos_beg)) === false) {
      break;
    }

    $contents = substr_replace(
      $contents,
      offload_transform_tag(substr($contents, $pos_beg, $pos_end - $pos_beg), 'href'),
      $pos_beg,
      $pos_end - $pos_beg
    );
  }

  return $contents;
}

function offload_transform_tag ($attrs, $src_attr) {
  global $offload_attrs, $offload_extensions;

  preg_match_all('/(\w+)[\s]*=[\s]*["\']([^"\']*)["\']/', $attrs, $matches);
  $num_matches = count($matches[0]);
  $names = &$matches[1];
  $values = &$matches[2];
  $ret = "";

  $state = array();

  foreach ($offload_attrs as $offload_attr) {
    $state[$offload_attr] = "";
  }

  $i = -1;
  $src = "";

  while (++$i < $num_matches) {
    $names[$i] = strtolower($names[$i]);

    if (!in_array($names[$i], $offload_attrs) && $names[$i] != $src_attr) {
      $ret .= "{$names[$i]}=\"{$values[$i]}\" ";
    } else if ($names[$i] != $src_attr) {
      $state[$names[$i]] = base64_encode($values[$i]);
    } else if (in_array(strtolower(strrchr($values[$i], '.')), $offload_extensions)) {
      $src = offload_normalize_url($values[$i]);

      if (!OFFLOAD_EXTERNAL_URLS &&
          strpos($src, "http://{$_SERVER['SERVER_NAME']}") !== 0 &&
          strpos($src, "http://www.{$_SERVER['SERVER_NAME']}") !== 0) {

        return $attrs;
      }

      $src = base64_encode($src);
    }
  }

  if (!$src) {
    return $attrs;
  }

  $ret .= $src_attr . '="' . OFFLOAD_ROOT . $src;

  foreach ($state as $val) {
    $ret .= ".$val";
  }

  $ret .= '"';
  return $ret;
}

function offload_normalize_url ($url) {
  if (substr($url, 0, 7) == 'http://') {
    return $url;
  } else if (substr($url, 0, 2) == '//') {
    return "http:$url";
  } else if (substr($url, 0, 1) == '/') {
    return "http://{$_SERVER['SERVER_NAME']}{$url}";
  } else {
    return "http://{$_SERVER['SERVER_NAME']}" . substr($_SERVER['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'], '/')) . "/{$url}";
  }
}

?>
