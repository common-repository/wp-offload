=== WP-Offload ===

Tags: offloading, performance, caching, thumbnails
Contributors: bl0wfish
Stable tag: trunk

Improve the overall performance of WordPress by delivering static content from external cache servers.

== Description ==

WP-Offload will boost the performance of your blog by seamlessly offloading static content like images, documents and movies. This will greatly reduce bandwidth consumption and the number of HTTP requests issued to your web server. Additional features such as remote image manipulation and thumbnail generation are provided.

You need a free [SteadyOffload account](http://steadyoffload.com/sign-up "Obtain your account now!"). After signing up, enter your SteadyOffload key and enable offloading via the WP-Offload options administration menu.

== Installation ==

Installation normally takes less than 3 minutes:

1. Upload to your plugins folder, usually `wp-content/plugins/` and unzip the file.

2. Activate the plugin via the plugin screen.

3. Create a [SteadyOffload account](http://steadyoffload.com/sign-up "Obtain your account now!"). You will receive your SteadyOffload key in an email message immediately.

4. Go to the "Options" administration menu, select "WP-Offload", enter your SteadyOffload key, check "Enable Offloading" and click "Update Options".

5. The static content in your posts (images, links to PDF documents, movies, etc.) will now be automatically mirrored and offloaded to the SteadyOffload cache servers. Note that you will have to freshly regenerate any pages that have been previously cached with WP-Cache.

== Frequently Asked Questions ==

= Is this plugin for me? =

You should use WP-Offload if your blog has a considerable amount of traffic and you care about end-user performance. Not only you will gain more performance and responsiveness, but your total bandwidth bill will get smaller.

= Do I have to upload my files to the SteadyOffload cache servers? =

No. You don't have to upload any files at all. The SteadyOffload cachebot does that - static content gets mirrored and delivered from the SteadyOffload cache servers, but at the same time everything remains stored on your server.

= Is my SteadyOffload key private and confidential? =

No. Your SteadyOffload key is used primarily for URL differentiation. To prevent bandwidth theft, you can set URL filters through the [SteadyOffload control panel](http://steadyoffload.com/panel "Log into the SteadyOffload control panel with your existing account now!").

= Is there any limit on the size of the files that can be offloaded? =

No. You can offload small text files as well as big downloads like CD images.

= How can I refresh an already cached object in case the original has changed? =

You should use the custom "xnonce" attribute. Setting a random value to it will tell the SteadyOffload cache servers to synchronize the cached object with the original one immediately.

= How can I use WP-Offload to create a thumbnail? =

Just use the custom "xmanip" attribute with the "img" and "a" tags. For more details on the available commands within that attribute, please check the [SteadyOffload control panel](http://steadyoffload.com/panel "Log into the SteadyOffload control panel with your existing account now!").

= Can I compress offloaded images? =

Yes. It's possible to set the quality factor of JPEG images through the "xjpegquality" custom attribute.

== Screenshots ==

1. The administration menu.
